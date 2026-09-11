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

namespace local_autograder\task;

use local_autograder\local\config_repository;
use local_autograder\local\decision_repository;
use local_autograder\local\grade_log_repository;

/**
 * The whole cycle, from switching autograder on to the grade in the gradebook.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\task\grade_student
 * @covers      \local_autograder\task\catch_up_module
 * @covers      \local_autograder\task\cancel_module
 */
final class grade_student_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The assignment being autograded. */
    private \stdClass $cm;

    /** @var \stdClass The assign instance record. */
    private \stdClass $assign;

    /** @var \stdClass The teacher who should end up as the grader. */
    private \stdClass $teacher;

    /** @var \stdClass The student who submitted. */
    private \stdClass $student;

    /**
     * A course with a teacher, a student who has submitted, and an assignment
     * out of 100.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');

        $this->assign = $generator->create_module('assign', [
            'course' => $this->course->id,
            'grade' => 100,
            'assignsubmission_onlinetext_enabled' => 1,
        ]);
        $this->cm = get_coursemodule_from_instance(
            'assign',
            $this->assign->id,
            $this->course->id,
            false,
            MUST_EXIST
        );

        $this->submit($this->student);
    }

    /**
     * Switching autograder on catches the student up, and when the moment
     * arrives the grade lands in the gradebook as the teacher's.
     */
    public function test_catch_up_then_grade_puts_the_grade_in_the_gradebook(): void {
        $this->configure(true, 70.0);
        $this->run_catch_up();

        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->assertNotFalse($decision, 'Switching autograder on must reach students who already submitted.');
        $this->assertSame(decision_repository::STATUS_PENDING, $decision->status);

        $sink = $this->redirectEvents();
        $this->run_grading($decision);
        $events = $sink->get_events();
        $sink->close();

        $grade = $this->current_grade();
        $this->assertNotNull($grade);
        $this->assertEquals(70.0, (float) $grade->finalgrade);
        $this->assertEquals($this->teacher->id, (int) $grade->usermodified, 'It must read as the teacher\'s grade.');

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_GRADED, $settled->status);
        $this->assertEquals($this->teacher->id, (int) $settled->graderid);
        $this->assertEquals(70.0, (float) $settled->gradedvalue);
        $this->assertNull($settled->adhoctaskid);

        $log = grade_log_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->assertCount(1, $log);
        $this->assertSame(grade_log_repository::OUTCOME_GRADED, reset($log)->outcome);

        $graded = array_values(array_filter($events, function ($event) {
            return $event instanceof \local_autograder\event\student_graded;
        }));
        $this->assertCount(1, $graded);
        $this->assertEquals($this->student->id, (int) $graded[0]->relateduserid);
        $this->assertEquals($this->teacher->id, (int) $graded[0]->other['graderid']);
    }

    /**
     * Autograder's own grade must not be mistaken for a teacher's, which
     * would leave the decision it is completing marked as taken over by hand.
     */
    public function test_autograders_own_grade_does_not_cancel_its_own_decision(): void {
        $this->configure(true, 55.0);
        $this->run_catch_up();

        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->run_grading($decision);

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_GRADED, $settled->status);
    }

    /**
     * A teacher who grades first wins: autograder stands down and says so.
     */
    public function test_a_grade_by_hand_cancels_the_pending_decision(): void {
        $this->configure(true, 70.0);
        $this->run_catch_up();

        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->grade_by_hand(42.0);

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_MANUAL, $settled->status);
        $this->assertSame('gradedbyhand', $settled->failurereason);

        // And should the task somehow still run, it must not overwrite them.
        $this->run_grading($settled);
        $this->assertEquals(42.0, (float) $this->current_grade()->finalgrade);
    }

    /**
     * Switching autograder off calls off everything it had queued.
     */
    public function test_switching_off_cancels_everything_pending(): void {
        $this->configure(true, 70.0);
        $this->run_catch_up();

        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        $this->assertSame(decision_repository::STATUS_PENDING, $decision->status);

        $this->configure(false, 70.0);
        $task = new cancel_module();
        $task->set_custom_data((object) ['cmid' => (int) $this->cm->id, 'reason' => 'autograderoff']);
        $task->execute();

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $settled->status);
        $this->assertSame('autograderoff', $settled->failurereason);
        $this->assertNull($this->current_grade(), 'Nothing should have been written.');
    }

    /**
     * An extension that arrives after the task was queued moves the grading,
     * it does not let it go ahead early.
     */
    public function test_an_extension_pushes_the_grading_back(): void {
        global $DB;

        $this->configure(true, 70.0);
        $this->run_catch_up();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $cutoff = time() + WEEKSECS;
        $DB->insert_record('assign_overrides', (object) [
            'assignid' => $this->assign->id,
            'groupid' => null,
            'userid' => $this->student->id,
            'sortorder' => null,
            'duedate' => $cutoff,
            'cutoffdate' => $cutoff,
            'allowsubmissionsfromdate' => null,
            'timelimit' => null,
        ]);

        $this->run_grading($decision, false);

        $moved = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_PENDING, $moved->status);
        $this->assertSame('useroverride', $moved->duedatereason);
        $this->assertEquals($cutoff, (int) $moved->baselineduedate);
        $this->assertNull($this->current_grade(), 'Grading early would be the whole bug.');
    }

    /**
     * With nobody able to grade on the student's behalf, the decision fails
     * loudly rather than silently doing nothing.
     */
    public function test_no_eligible_grader_is_recorded_as_a_failure(): void {
        $this->configure(true, 70.0);
        $this->run_catch_up();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $role = $this->teacher_role_id();
        role_change_permission(
            $role,
            \context_course::instance($this->course->id),
            'local/autograder:gradeonbehalf',
            CAP_PROHIBIT
        );

        $sink = $this->redirectEvents();
        $this->run_grading($decision);
        $events = $sink->get_events();
        $sink->close();

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_FAILED, $settled->status);
        $this->assertSame('no_grader', $settled->failurereason);
        $this->assertNull($this->current_grade());

        $failures = array_filter($events, function ($event) {
            return $event instanceof \local_autograder\event\grading_failed;
        });
        $this->assertCount(1, $failures);
    }

    /**
     * The safety net requeues a due decision whose task has gone missing.
     */
    public function test_reconcile_requeues_a_decision_that_lost_its_task(): void {
        global $DB;

        $this->configure(true, 70.0);
        $this->run_catch_up();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $DB->delete_records('task_adhoc', ['id' => $decision->adhoctaskid]);
        $DB->set_field('local_autograder_decision', 'scheduledgradetime', time() - 60, ['id' => $decision->id]);

        $this->expectOutputRegex('/requeued 1 decision/');
        (new reconcile_pending())->execute();

        $taskid = $DB->get_field('local_autograder_decision', 'adhoctaskid', ['id' => $decision->id]);
        $this->assertNotEmpty($taskid);
        $this->assertTrue($DB->record_exists('task_adhoc', ['id' => $taskid]));
    }

    /**
     * Writes the plugin's configuration for the assignment.
     *
     * @param bool $enabled
     * @param float $grade
     */
    private function configure(bool $enabled, float $grade): void {
        config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            $enabled,
            'point',
            $grade,
            null,
            0,
            (int) $this->teacher->id
        );
    }

    /**
     * Runs the catch-up task for the assignment.
     */
    private function run_catch_up(): void {
        $task = new catch_up_module();
        $task->set_custom_data((object) ['cmid' => (int) $this->cm->id]);
        $task->execute();
    }

    /**
     * Runs the grading task for one decision.
     *
     * @param \stdClass $decision
     * @param bool $makedue Whether to bring its moment forward first.
     */
    private function run_grading(\stdClass $decision, bool $makedue = true): void {
        global $DB;

        if ($makedue) {
            $DB->set_field(
                'local_autograder_decision',
                'scheduledgradetime',
                time() - MINSECS,
                ['id' => $decision->id]
            );
        }

        $task = new grade_student();
        $task->set_custom_data((object) ['decisionid' => (int) $decision->id]);
        $task->execute();
    }

    /**
     * The student's grade on the assignment, or null if they have none.
     *
     * @return \stdClass|null
     */
    private function current_grade(): ?\stdClass {
        $grades = grade_get_grades(
            (int) $this->course->id,
            'mod',
            'assign',
            (int) $this->assign->id,
            (int) $this->student->id
        );
        $grade = $grades->items[0]->grades[$this->student->id] ?? null;

        return ($grade && $grade->grade !== null && $grade->grade !== '')
            ? (object) ['finalgrade' => $grade->grade, 'usermodified' => $grade->usermodified]
            : null;
    }

    /**
     * Grades the student the way a teacher on the grading screen would.
     *
     * @param float $value
     */
    private function grade_by_hand(float $value): void {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $this->setUser($this->teacher);

        $context = \context_module::instance((int) $this->cm->id);
        $assign = new \assign($context, $this->cm, get_course($this->course->id));
        $assign->save_grade((int) $this->student->id, (object) [
            'grade' => $value,
            'attemptnumber' => -1,
            'addattempt' => false,
            'applytoall' => false,
            'sendstudentnotifications' => false,
            'assignfeedbackcomments_editor' => ['text' => '', 'format' => FORMAT_HTML],
        ]);

        $this->setUser(null);
    }

    /**
     * The role the teacher holds, so a test can change what it may do.
     *
     * @return int The editing teacher role's id.
     */
    private function teacher_role_id(): int {
        global $DB;

        return (int) $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
    }

    /**
     * Puts an online-text submission in for a student.
     *
     * @param \stdClass $user
     */
    private function submit(\stdClass $user): void {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $DB->insert_record('assign_submission', (object) [
            'assignment' => $this->assign->id,
            'userid' => $user->id,
            'timecreated' => time() - HOURSECS,
            'timemodified' => time() - HOURSECS,
            'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);
    }
}
