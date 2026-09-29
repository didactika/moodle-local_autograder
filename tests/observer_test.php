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

namespace local_autograder;

use local_autograder\local\config\config_repository;
use local_autograder\local\decision\decision_repository;

/**
 * What the plugin does when Moodle says something happened.
 *
 * These go through the real events rather than calling the observer methods,
 * so that db/events.php is under test too: a callback wired to an event name
 * that does not exist would pass a direct call and fail a site.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\observer
 */
final class observer_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The assignment being autograded. */
    private \stdClass $cm;

    /** @var \stdClass The assign instance record. */
    private \stdClass $assign;

    /** @var \stdClass The teacher. */
    private \stdClass $teacher;

    /** @var \stdClass The student. */
    private \stdClass $student;

    /**
     * A course, an assignment with autograder switched on, a teacher and a
     * student who has not done anything yet.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['enablecompletion' => 1]);
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

        config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            DAYSECS,
            (int) $this->teacher->id
        );
    }

    /**
     * A hand-in creates the decision, counting from the submission because
     * this activity tracks no completion and sets no close date.
     */
    public function test_a_submission_creates_the_decision(): void {
        $this->submit();

        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $this->assertNotFalse($decision);
        $this->assertSame(decision_repository::STATUS_PENDING, $decision->status);
        $this->assertSame('submission', $decision->duedatereason);
    }

    /**
     * A teacher grading by hand stops autograder, without autograder's own
     * writes counting as that.
     */
    public function test_a_grade_by_hand_cancels_the_decision(): void {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $this->submit();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $this->setUser($this->teacher);
        $context = \context_module::instance((int) $this->cm->id);
        $assign = new \assign($context, $this->cm, get_course($this->course->id));
        $assign->save_grade((int) $this->student->id, (object) [
            'grade' => 33.0,
            'attemptnumber' => -1,
            'addattempt' => false,
            'applytoall' => false,
            'sendstudentnotifications' => false,
            'assignfeedbackcomments_editor' => ['text' => '', 'format' => FORMAT_HTML],
        ]);
        $this->setUser(null);

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_MANUAL, $settled->status);
    }

    /**
     * Unenrolling a student calls off what was waiting for them.
     */
    public function test_unenrolling_cancels_the_decision(): void {
        global $DB;

        $this->submit();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);

        $instance = $DB->get_record('enrol', ['courseid' => $this->course->id, 'enrol' => 'manual'], '*', MUST_EXIST);
        $plugin = enrol_get_plugin('manual');
        $plugin->unenrol_user($instance, (int) $this->student->id);

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $settled->status);
        $this->assertSame('unenrolled', $settled->failurereason);
    }

    /**
     * A student suspended and let back in is looked at again: they keep their
     * role through the suspension, so nothing else would pick them up.
     */
    public function test_reactivating_a_student_picks_their_decision_back_up(): void {
        $this->submit();
        [$instance, $plugin] = $this->manual_enrolment();

        $plugin->update_user_enrol($instance, (int) $this->student->id, ENROL_USER_SUSPENDED);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $this->decision()->status);

        $plugin->update_user_enrol($instance, (int) $this->student->id, ENROL_USER_ACTIVE);
        $this->assertSame(decision_repository::STATUS_PENDING, $this->decision()->status);
    }

    /**
     * A student who left and is enrolled again is looked at again, with the
     * work they had already handed in.
     */
    public function test_enrolling_a_student_again_picks_their_decision_back_up(): void {
        global $DB;

        $this->submit();
        [$instance, $plugin] = $this->manual_enrolment();

        $plugin->unenrol_user($instance, (int) $this->student->id);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $this->decision()->status);

        $studentrole = (int) $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $plugin->enrol_user($instance, (int) $this->student->id, $studentrole);
        $this->assertSame(decision_repository::STATUS_PENDING, $this->decision()->status);
    }

    /**
     * Deleting the activity leaves nothing of it behind — neither its
     * decisions nor its configuration.
     */
    public function test_deleting_the_activity_removes_its_decisions_and_config(): void {
        global $DB;

        $this->submit();
        $cmid = (int) $this->cm->id;

        course_delete_module($cmid);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['cmid' => $cmid]));
        $this->assertFalse($DB->record_exists('local_autograder_config', ['cmid' => $cmid]));
    }

    /**
     * Resetting the course clears the decisions and the history it built on
     * the work the reset just removed, and asks for the activity to be looked
     * at again from scratch.
     */
    public function test_resetting_the_course_clears_and_re_derives(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/course/lib.php');

        $this->submit();
        $decision = decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
        \local_autograder\local\grading\grade_log_repository::record(
            $decision,
            \local_autograder\local\grading\grade_log_repository::OUTCOME_CANCELLED,
            'test'
        );

        $DB->delete_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%catch_up_module%']
        );

        reset_course_userdata((object) [
            'id' => $this->course->id,
            'reset_start_date' => 0,
            'reset_assign_submissions' => 1,
        ]);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['courseid' => $this->course->id]));
        $this->assertFalse($DB->record_exists('local_autograder_grade_log', ['courseid' => $this->course->id]));
        $this->assertTrue(
            $DB->record_exists('local_autograder_config', ['cmid' => $this->cm->id]),
            'A reset clears the students\' work, not the teacher\'s settings.'
        );
        $this->assertNotEmpty(
            $DB->get_records_select('task_adhoc', $DB->sql_like('classname', ':c'), ['c' => '%catch_up_module%']),
            'Whatever survived the reset has to be looked at again.'
        );
    }

    /**
     * A student who never engaged is never scheduled, whatever else happens
     * in the course.
     */
    public function test_a_student_who_did_nothing_is_never_scheduled(): void {
        global $DB;

        $group = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        $this->getDataGenerator()->create_group_member([
            'groupid' => $group->id,
            'userid' => $this->student->id,
        ]);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['userid' => $this->student->id]));
    }

    /**
     * The course's manual enrolment instance and its plugin.
     *
     * @return array{0: \stdClass, 1: \enrol_plugin}
     */
    private function manual_enrolment(): array {
        global $DB;

        return [
            $DB->get_record('enrol', ['courseid' => $this->course->id, 'enrol' => 'manual'], '*', MUST_EXIST),
            enrol_get_plugin('manual'),
        ];
    }

    /**
     * The student's decision on the assignment.
     *
     * @return \stdClass
     */
    private function decision(): \stdClass {
        return decision_repository::for_cm_user((int) $this->cm->id, (int) $this->student->id);
    }

    /**
     * Puts a hand-in in, through assign's own API so that its event fires.
     */
    private function submit(): void {
        global $CFG, $DB;

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

        \mod_assign\event\assessable_submitted::create([
            'context' => \context_module::instance((int) $this->cm->id),
            'objectid' => $this->assign->id,
            'userid' => (int) $this->student->id,
            'other' => ['submission_editable' => false],
        ])->trigger();
    }
}
