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

/**
 * A course backed up and restored keeps what a teacher configured.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \backup_local_autograder_plugin
 * @covers      \restore_local_autograder_plugin
 */
final class backup_restore_test extends \advanced_testcase {
    /**
     * Duplicating an activity carries its autograder settings across, against
     * the new course module rather than the old one.
     */
    public function test_duplicating_an_activity_keeps_its_configuration(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'point',
            65.0,
            null,
            2 * DAYSECS,
            (int) $teacher->id
        );

        $newcm = duplicate_module($course, $cm);

        $this->assertNotEquals($cm->id, $newcm->id);

        $copy = config_repository::get_for_cm((int) $newcm->id);
        $this->assertNotFalse($copy, 'The duplicate must come with its own configuration.');
        $this->assertEquals(1, (int) $copy->enabled);
        $this->assertSame('point', $copy->grademethod);
        $this->assertEquals(65.0, (float) $copy->gradevalue);
        $this->assertEquals(2 * DAYSECS, (int) $copy->delayseconds);
        $this->assertEquals($course->id, (int) $copy->courseid);

        $this->assertEquals(
            2,
            $DB->count_records('local_autograder_config'),
            'The original keeps its own row; the copy does not move it.'
        );
    }

    /**
     * Decisions and the grading log stay behind: they describe students doing
     * this activity, not the activity itself, and the copy has none yet.
     */
    public function test_duplicating_does_not_carry_decisions_across(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $student = $generator->create_and_enrol($course, 'student');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'point',
            65.0,
            null,
            0,
            (int) $teacher->id
        );

        $now = time();
        $DB->insert_record('local_autograder_decision', (object) [
            'cmid' => $cm->id,
            'courseid' => $course->id,
            'userid' => $student->id,
            'status' => 'graded',
            'baselineduedate' => $now,
            'duedatereason' => 'submission',
            'scheduledgradetime' => $now,
            'graderid' => $teacher->id,
            'gradedvalue' => 65.0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $newcm = duplicate_module($course, $cm);

        $this->assertFalse($DB->record_exists('local_autograder_decision', ['cmid' => $newcm->id]));
        $this->assertTrue($DB->record_exists('local_autograder_decision', ['cmid' => $cm->id]));
    }

    /**
     * An activity with autograder switched on gets a catch-up queued once it
     * has been restored, so the copy decides for itself rather than inheriting
     * the original's answers.
     */
    public function test_restoring_an_enabled_activity_queues_a_catch_up(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'point',
            65.0,
            null,
            0,
            (int) $teacher->id
        );

        $DB->delete_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%local_autograder%']
        );

        $newcm = duplicate_module($course, $cm);

        $queued = $DB->get_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%catch_up_module%']
        );
        $this->assertNotEmpty($queued);

        $cmids = array_map(function ($task) {
            return (int) json_decode($task->customdata)->cmid;
        }, $queued);
        $this->assertContains((int) $newcm->id, $cmids);
    }

    /**
     * An activity with autograder switched off is restored switched off, and
     * nothing is queued for it.
     */
    public function test_restoring_a_disabled_activity_queues_nothing(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            false,
            'point',
            65.0,
            null,
            0,
            (int) $teacher->id
        );

        $DB->delete_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%local_autograder%']
        );

        $newcm = duplicate_module($course, $cm);

        $copy = config_repository::get_for_cm((int) $newcm->id);
        $this->assertNotFalse($copy);
        $this->assertEquals(0, (int) $copy->enabled);
        $this->assertEmpty($DB->get_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%catch_up_module%']
        ));
    }
}
