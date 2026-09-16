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
use local_autograder\task\cancel_module;
use local_autograder\task\catch_up_module;

/**
 * What changing an activity's autograder settings does to the students who are
 * already waiting on it.
 *
 * A teacher who shortens the wait expects everyone waiting to move, and a
 * teacher who switches autograder off and on again expects it to pick up where
 * it left off. Neither is true unless a settled decision is reopened when what
 * settled it no longer holds.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\decision\decision_repository::ensure
 */
final class reconsider_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The autograded assignment. */
    private \stdClass $cm;

    /** @var \stdClass The assign instance. */
    private \stdClass $assign;

    /** @var \stdClass The teacher. */
    private \stdClass $teacher;

    /** @var \stdClass The student, who has submitted. */
    private \stdClass $student;

    /**
     * An assignment with autograder on, a two-day wait, and one student who
     * has handed in.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');

        $this->assign = $generator->create_module('assign', [
            'course' => $this->course->id,
            'grade' => 100,
        ]);
        $this->cm = get_coursemodule_from_instance(
            'assign',
            $this->assign->id,
            $this->course->id,
            false,
            MUST_EXIST
        );

        $this->submit();
        $this->configure(true, 2 * DAYSECS);
        $this->catch_up();
    }

    /**
     * Shortening the wait moves everybody who is already waiting, rather than
     * leaving them on the old date until each one comes due.
     */
    public function test_shortening_the_wait_moves_the_students_already_waiting(): void {
        $before = $this->decision();

        $this->assertSame(decision_repository::STATUS_PENDING, $before->status);

        $this->configure(true, HOURSECS);
        $this->catch_up();

        $after = $this->decision();

        $this->assertEquals(
            (int) $after->baselineduedate + HOURSECS,
            (int) $after->scheduledgradetime,
            'The new wait has to be counted from the same baseline as the old one.'
        );
        $this->assertLessThan(
            (int) $before->scheduledgradetime,
            (int) $after->scheduledgradetime
        );
    }

    /**
     * The task waiting in the queue moves with the decision — a row saying one
     * date while a task waits for another is how a student gets graded on a
     * deadline that no longer applies.
     */
    public function test_the_queued_task_moves_with_it(): void {
        global $DB;

        $this->configure(true, HOURSECS);
        $this->catch_up();

        $decision = $this->decision();

        $this->assertEquals(
            (int) $decision->scheduledgradetime,
            (int) $DB->get_field('task_adhoc', 'nextruntime', ['id' => $decision->adhoctaskid])
        );
    }

    /**
     * Switching autograder off and on again picks the students back up.
     *
     * Switching it off cancels everything waiting, which is right. Switching
     * it back on has to undo that, or the activity is configured, enabled, and
     * quietly grading nobody — the state the report shows as "not autograded"
     * for ever.
     */
    public function test_switching_off_and_on_again_picks_the_students_back_up(): void {
        $this->configure(false, 2 * DAYSECS);
        $this->cancel();

        $this->assertSame(decision_repository::STATUS_CANCELLED, $this->decision()->status);

        $this->configure(true, 2 * DAYSECS);
        $this->catch_up();

        $this->assertSame(
            decision_repository::STATUS_PENDING,
            $this->decision()->status,
            'Switching autograder back on has to revive what switching it off called off.'
        );
    }

    /**
     * Saving the activity moves the students already waiting there and then,
     * without waiting for cron.
     *
     * This is what a teacher sees: they shorten the wait, open the report, and
     * expect the new dates. Leaving it to the queued sweep means the report
     * shows the old ones until the next cron run, which reads as the setting
     * having done nothing.
     */
    public function test_saving_the_form_moves_the_waiting_students_at_once(): void {
        $before = (int) $this->decision()->scheduledgradetime;

        \local_autograder\form\autograder_section::save((object) [
            'modulename' => 'assign',
            'coursemodule' => (int) $this->cm->id,
            'course' => (int) $this->course->id,
            'autograder_enabled' => 1,
            'autograder_grade_point' => 70,
            'autograder_days' => 0,
            'autograder_hours' => 1,
            'autograder_minutes' => 0,
        ]);

        // Deliberately no task is run here.
        $after = $this->decision();

        $this->assertEquals(
            (int) $after->baselineduedate + HOURSECS,
            (int) $after->scheduledgradetime,
            'The new wait applies to the students already waiting.'
        );
        $this->assertLessThan($before, (int) $after->scheduledgradetime);
    }

    /**
     * A grade that a person put there is never reopened — that is the whole
     * promise of standing down for a teacher.
     */
    public function test_a_grade_by_hand_is_never_reopened(): void {
        $this->settle(decision_repository::STATUS_MANUAL);

        $this->catch_up();

        $this->assertSame(decision_repository::STATUS_MANUAL, $this->decision()->status);
    }

    /**
     * Neither is one autograder already posted.
     */
    public function test_an_autograded_decision_is_never_reopened(): void {
        $this->settle(decision_repository::STATUS_GRADED);

        $this->catch_up();

        $this->assertSame(decision_repository::STATUS_GRADED, $this->decision()->status);
    }

    /**
     * A failure is reopened by an explicit catch-up: whatever stopped it —
     * no eligible teacher, a rubric that had been edited — is exactly the kind
     * of thing somebody fixes and then expects to be retried.
     */
    public function test_a_failure_is_retried_by_a_catch_up(): void {
        $this->settle(decision_repository::STATUS_FAILED);

        $this->catch_up();

        $this->assertSame(decision_repository::STATUS_PENDING, $this->decision()->status);
    }

    /**
     * The student's decision.
     *
     * @return \stdClass
     */
    private function decision(): \stdClass {
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $this->assertNotFalse($decision, 'The student should have a decision by now.');

        return $decision;
    }

    /**
     * Forces the decision into a settled state.
     *
     * @param string $status
     */
    private function settle(string $status): void {
        decision_repository::settle($this->decision(), $status);
    }

    /**
     * Writes the activity's autograder settings.
     *
     * @param bool $enabled
     * @param int $delayseconds
     */
    private function configure(bool $enabled, int $delayseconds): void {
        config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            $enabled,
            'point',
            70.0,
            null,
            $delayseconds,
            (int) $this->teacher->id
        );
    }

    /**
     * Runs the catch-up the settings form queues on every save.
     */
    private function catch_up(): void {
        $task = new catch_up_module();
        $task->set_custom_data((object) ['cmid' => (int) $this->cm->id]);
        $task->execute();
    }

    /**
     * Runs the cancellation the settings form queues when it is switched off.
     */
    private function cancel(): void {
        $task = new cancel_module();
        $task->set_custom_data((object) ['cmid' => (int) $this->cm->id, 'reason' => 'autograderoff']);
        $task->execute();
    }

    /**
     * Puts an online-text submission in for the student.
     */
    private function submit(): void {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $DB->insert_record('assign_submission', (object) [
            'assignment' => $this->assign->id,
            'userid' => $this->student->id,
            'timecreated' => time() - HOURSECS,
            'timemodified' => time() - HOURSECS,
            'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);
    }
}
