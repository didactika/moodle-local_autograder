<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_autograder\local;

use local_autograder\local\config\config_repository;
use local_autograder\local\decision\decision_repository;
use local_autograder\local\grading\grade_log_repository;

/**
 * A decision and the task queued for it have to move together.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\decision\decision_repository
 */
final class decision_repository_test extends \advanced_testcase {
    /** @var \stdClass The course everything in a test lives in. */
    private \stdClass $course;

    /** @var \stdClass The assignment being autograded. */
    private \stdClass $cm;

    /** @var \stdClass Its autograder configuration. */
    private \stdClass $config;

    /** @var \stdClass The student. */
    private \stdClass $student;

    /**
     * A course with one assignment, one student who has submitted, and
     * autograder switched on with a one-hour delay.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->student = $generator->create_and_enrol($this->course, 'student');

        $assign = $generator->create_module('assign', [
            'course' => $this->course->id,
            'grade' => 100,
            'assignsubmission_onlinetext_enabled' => 1,
        ]);
        $this->cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);

        $this->submit($assign, $this->student);

        $this->config = config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            HOURSECS,
            2
        );
    }

    /**
     * A student who has engaged gets a decision, and a task waiting for it at
     * exactly the moment it comes due.
     */
    public function test_ensure_creates_a_decision_and_queues_its_task(): void {
        global $DB;

        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);

        $this->assertNotNull($decision);
        $this->assertSame(decision_repository::STATUS_PENDING, $decision->status);
        $this->assertSame('submission', $decision->duedatereason);
        $this->assertEquals(
            $decision->baselineduedate + HOURSECS,
            $decision->scheduledgradetime,
            'The delay is counted from the baseline due date.'
        );

        $task = $DB->get_record('task_adhoc', ['id' => $decision->adhoctaskid], '*', MUST_EXIST);
        $this->assertSame('\local_autograder\task\grade_student', $task->classname);
        $this->assertEquals($decision->scheduledgradetime, $task->nextruntime);
    }

    /**
     * A student who has neither completed nor submitted has nothing to grade,
     * so there is nothing to decide either.
     */
    public function test_ensure_declines_a_student_who_has_not_engaged(): void {
        global $DB;

        $other = $this->getDataGenerator()->create_and_enrol($this->course, 'student');

        $this->assertNull(decision_repository::ensure($this->cm, $this->config, (int) $other->id));
        $this->assertFalse($DB->record_exists('local_autograder_decision', ['userid' => $other->id]));
    }

    /**
     * Asking again moves the existing decision rather than making a second
     * one, and the old task goes with the old time.
     */
    public function test_ensure_moves_the_existing_decision_and_its_task(): void {
        global $DB;

        $first = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        $firsttaskid = (int) $first->adhoctaskid;

        $this->config->delayseconds = DAYSECS;
        $second = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);

        $this->assertEquals($first->id, $second->id, 'One student, one activity, one decision.');
        $this->assertEquals(
            $second->baselineduedate + DAYSECS,
            $second->scheduledgradetime
        );
        $this->assertNotEquals($firsttaskid, (int) $second->adhoctaskid);
        $this->assertFalse(
            $DB->record_exists('task_adhoc', ['id' => $firsttaskid]),
            'The task queued for the old moment must not survive the new one.'
        );
        $this->assertEquals(
            $second->scheduledgradetime,
            $DB->get_field('task_adhoc', 'nextruntime', ['id' => $second->adhoctaskid])
        );
    }

    /**
     * Calling a decision off takes its task out of the queue, writes the
     * reason to the log, and says so as an event.
     */
    public function test_cancel_settles_the_decision_and_drops_its_task(): void {
        global $DB;

        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        $taskid = (int) $decision->adhoctaskid;

        $sink = $this->redirectEvents();
        decision_repository::cancel($decision, 'unenrolled');
        $events = $sink->get_events();
        $sink->close();

        $stored = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $stored->status);
        $this->assertSame('unenrolled', $stored->failurereason);
        $this->assertNull($stored->adhoctaskid);
        $this->assertFalse($DB->record_exists('task_adhoc', ['id' => $taskid]));

        $log = grade_log_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->assertCount(1, $log);
        $this->assertSame(grade_log_repository::OUTCOME_CANCELLED, reset($log)->outcome);

        $cancelled = array_values(array_filter($events, function ($event) {
            return $event instanceof \local_autograder\event\decision_cancelled;
        }));
        $this->assertCount(1, $cancelled);
        $this->assertSame('unenrolled', $cancelled[0]->other['reason']);
    }

    /**
     * A settled decision stays settled: a later event must not reopen one a
     * teacher has already taken over.
     */
    public function test_a_settled_decision_is_not_reopened(): void {
        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        decision_repository::cancel($decision, 'gradedbyhand', decision_repository::STATUS_MANUAL);

        $again = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);

        $this->assertSame(decision_repository::STATUS_MANUAL, $again->status);
        $this->assertNull($again->adhoctaskid);
    }

    /**
     * A task already claimed by a runner is left alone — pulling it out from
     * under itself is worse than letting it run and find nothing to do.
     */
    public function test_unschedule_leaves_a_task_that_has_already_started(): void {
        global $DB;

        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        $DB->set_field('task_adhoc', 'timestarted', time(), ['id' => $decision->adhoctaskid]);
        $taskid = (int) $decision->adhoctaskid;

        decision_repository::unschedule($decision);

        $this->assertTrue($DB->record_exists('task_adhoc', ['id' => $taskid]));
        $this->assertNull($DB->get_field('local_autograder_decision', 'adhoctaskid', ['id' => $decision->id]));
    }

    /**
     * The safety net finds a due decision whose task has gone missing, and
     * ignores one that is not due yet.
     */
    public function test_orphaned_pending_finds_only_due_decisions_without_a_task(): void {
        global $DB;

        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        $DB->delete_records('task_adhoc', ['id' => $decision->adhoctaskid]);
        $DB->set_field('local_autograder_decision', 'scheduledgradetime', time() + DAYSECS, ['id' => $decision->id]);

        $this->assertEmpty(
            decision_repository::orphaned_pending(time()),
            'Not due yet, so not orphaned work — just work still to come.'
        );

        $DB->set_field('local_autograder_decision', 'scheduledgradetime', time() - 60, ['id' => $decision->id]);
        $orphans = decision_repository::orphaned_pending(time());

        $this->assertCount(1, $orphans);
        $this->assertEquals($decision->id, reset($orphans)->id);
    }

    /**
     * Retention clears out what is finished with and leaves what is not.
     */
    public function test_purge_settled_before_spares_pending_decisions(): void {
        global $DB;

        $decision = decision_repository::ensure($this->cm, $this->config, (int) $this->student->id);
        $other = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $DB->insert_record('local_autograder_decision', (object) [
            'cmid' => $this->cm->id,
            'courseid' => $this->course->id,
            'userid' => $other->id,
            'status' => decision_repository::STATUS_GRADED,
            'baselineduedate' => 1,
            'duedatereason' => 'submission',
            'scheduledgradetime' => 1,
            'timecreated' => 1,
            'timemodified' => 1,
        ]);

        $this->assertSame(1, decision_repository::purge_settled_before(time()));
        $this->assertTrue($DB->record_exists('local_autograder_decision', ['id' => $decision->id]));
    }

    /**
     * Puts an online-text submission in as the student.
     *
     * @param \stdClass $assign The assign instance record.
     * @param \stdClass $user
     */
    private function submit(\stdClass $assign, \stdClass $user): void {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $submission = (object) [
            'assignment' => $assign->id,
            'userid' => $user->id,
            'timecreated' => time() - HOURSECS,
            'timemodified' => time() - HOURSECS,
            'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ];
        $DB->insert_record('assign_submission', $submission);
    }
}
