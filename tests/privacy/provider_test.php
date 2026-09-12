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

namespace local_autograder\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_autograder\local\config_repository;
use local_autograder\local\decision_repository;
use local_autograder\local\grade_log_repository;

/**
 * What the plugin hands over, and what it removes, when asked.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\privacy\provider
 */
final class provider_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The assignment being autograded. */
    private \stdClass $cm;

    /** @var \context_module The activity's context. */
    private \context_module $context;

    /** @var \stdClass The teacher who configured it and was graded as. */
    private \stdClass $teacher;

    /** @var \stdClass The student who was graded. */
    private \stdClass $student;

    /** @var \stdClass A student with nothing to do with any of it. */
    private \stdClass $bystander;

    /** @var \stdClass The settled decision. */
    private \stdClass $decision;

    /**
     * An activity that has already graded one student, leaving a decision, a
     * log entry and a configuration behind.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');
        $this->bystander = $generator->create_and_enrol($this->course, 'student');

        $assign = $generator->create_module('assign', ['course' => $this->course->id, 'grade' => 100]);
        $this->cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);
        $this->context = \context_module::instance((int) $this->cm->id);

        config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            0,
            (int) $this->teacher->id
        );

        $this->decision = $this->make_decision((int) $this->student->id, (int) $this->teacher->id);
        grade_log_repository::record(
            $this->decision,
            grade_log_repository::OUTCOME_GRADED,
            'graded by point',
            (int) $this->teacher->id,
            70.0
        );
    }

    /**
     * The activity turns up for the student, for the teacher it was graded
     * as, and for nobody else.
     */
    public function test_get_contexts_for_userid(): void {
        foreach ([$this->student, $this->teacher] as $user) {
            $contexts = provider::get_contexts_for_userid((int) $user->id)->get_contextids();
            $this->assertEquals([$this->context->id], $contexts);
        }

        $this->assertEmpty(provider::get_contexts_for_userid((int) $this->bystander->id)->get_contextids());
    }

    /**
     * Everyone the plugin holds something about in the activity is listed —
     * the graded student and the teacher alike.
     */
    public function test_get_users_in_context(): void {
        $userlist = new userlist($this->context, 'local_autograder');
        provider::get_users_in_context($userlist);

        $found = $userlist->get_userids();
        sort($found);
        $expected = [(int) $this->student->id, (int) $this->teacher->id];
        sort($expected);

        $this->assertEquals($expected, $found);
    }

    /**
     * The student's export says what was decided about them and when.
     */
    public function test_export_user_data(): void {
        $contextlist = new approved_contextlist(
            $this->student,
            'local_autograder',
            [$this->context->id]
        );
        provider::export_user_data($contextlist);

        $writer = writer::with_context($this->context);
        $this->assertTrue($writer->has_any_data());

        $exported = $writer->get_data([
            get_string('pluginname', 'local_autograder'),
            get_string('privacy:path:decision', 'local_autograder'),
        ]);
        $this->assertSame(decision_repository::STATUS_GRADED, $exported->status);
        $this->assertEquals(70.0, (float) $exported->gradedvalue);
    }

    /**
     * Emptying the activity takes everything with it, queued tasks included.
     */
    public function test_delete_data_for_all_users_in_context(): void {
        global $DB;

        $taskid = (int) $this->decision->adhoctaskid;

        provider::delete_data_for_all_users_in_context($this->context);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['cmid' => $this->cm->id]));
        $this->assertFalse($DB->record_exists('local_autograder_grade_log', ['cmid' => $this->cm->id]));
        $this->assertFalse($DB->record_exists('local_autograder_config', ['cmid' => $this->cm->id]));
        $this->assertFalse($DB->record_exists('task_adhoc', ['id' => $taskid]));
    }

    /**
     * Removing the student takes their rows and leaves the activity working
     * for everybody else.
     */
    public function test_delete_data_for_user_leaves_the_activity_configured(): void {
        global $DB;

        $contextlist = new approved_contextlist($this->student, 'local_autograder', [$this->context->id]);
        provider::delete_data_for_user($contextlist);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['userid' => $this->student->id]));
        $this->assertFalse($DB->record_exists('local_autograder_grade_log', ['userid' => $this->student->id]));
        $this->assertTrue(
            $DB->record_exists('local_autograder_config', ['cmid' => $this->cm->id]),
            'The configuration belongs to the activity, not to the student.'
        );
    }

    /**
     * Removing the teacher keeps the student's record but takes the teacher's
     * name off it — the decision still happened, it just no longer names them.
     */
    public function test_delete_data_for_users_unattributes_the_grader(): void {
        global $DB;

        $userlist = new approved_userlist($this->context, 'local_autograder', [(int) $this->teacher->id]);
        provider::delete_data_for_users($userlist);

        $decision = $DB->get_record('local_autograder_decision', ['id' => $this->decision->id]);
        $this->assertNotFalse($decision, 'The student\'s own record is not the teacher\'s to remove.');
        $this->assertNull($decision->graderid);
        $this->assertEquals(0, (int) $DB->get_field('local_autograder_config', 'usermodified', ['cmid' => $this->cm->id]));
    }

    /**
     * The one preference this plugin keeps is exported.
     */
    public function test_export_user_preferences(): void {
        set_user_preference('local_autograder_optout', 1, $this->teacher);

        provider::export_user_preferences((int) $this->teacher->id);

        $preferences = writer::with_context(\context_system::instance())->get_user_preferences('local_autograder');
        // Plain property_exists rather than an assertObjectHas* helper: the
        // two spellings of that helper belong to different PHPUnit majors and
        // this plugin supports Moodle branches that ship both.
        $this->assertTrue(property_exists($preferences, 'local_autograder_optout'));
        $this->assertSame(get_string('yes'), $preferences->local_autograder_optout->value);
    }

    /**
     * Makes a settled, graded decision with a task still attached to it.
     *
     * @param int $userid
     * @param int $graderid
     * @return \stdClass
     */
    private function make_decision(int $userid, int $graderid): \stdClass {
        global $DB;

        $now = time();
        $decision = (object) [
            'cmid' => (int) $this->cm->id,
            'courseid' => (int) $this->course->id,
            'userid' => $userid,
            'status' => decision_repository::STATUS_GRADED,
            'baselineduedate' => $now - DAYSECS,
            'duedatereason' => 'submission',
            'scheduledgradetime' => $now - HOURSECS,
            'graderid' => $graderid,
            'gradedvalue' => 70.0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $decision->id = $DB->insert_record('local_autograder_decision', $decision);

        decision_repository::schedule($decision);

        return $decision;
    }
}
