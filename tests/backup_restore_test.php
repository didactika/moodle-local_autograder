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
use local_autograder\local\grading\advanced_grading;

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

        $newcm = $this->duplicate($course, $cm);

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

        $newcm = $this->duplicate($course, $cm);

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

        $newcm = $this->duplicate($course, $cm);

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

        $newcm = $this->duplicate($course, $cm);

        $copy = config_repository::get_for_cm((int) $newcm->id);
        $this->assertNotFalse($copy);
        $this->assertEquals(0, (int) $copy->enabled);
        $this->assertEmpty($DB->get_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%catch_up_module%']
        ));
    }

    /**
     * What autograder was told to mark on a rubric follows the activity.
     *
     * Core renumbers every criterion and level as it restores the form, so the
     * selection has to be renumbered with it. Carrying it across untouched
     * would point at the original course's rubric; dropping it would leave the
     * teacher to fill it in again on every restore.
     */
    public function test_restoring_a_rubric_marks_the_same_levels_on_the_new_criteria(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->define_rubric($cm);

        // Pick "Present" on both criteria — the second level of each, so that
        // a remap which simply kept the first would be caught.
        $criteria = advanced_grading::criteria($cm);
        $filling = [];

        foreach ($criteria as $criterionid => $criterion) {
            $filling[$criterionid] = [
                'levelid' => $this->level_called($criterion, 'Present'),
                'remark' => '',
            ];
        }

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'rubric',
            null,
            advanced_grading::encode($filling),
            0,
            (int) $teacher->id
        );

        $duplicate = $this->duplicate($course, $cm);
        $newcm = get_coursemodule_from_id('', (int) $duplicate->id, 0, false, MUST_EXIST);

        $copy = config_repository::get_for_cm((int) $newcm->id);
        $this->assertNotFalse($copy, 'The duplicate must come with its own configuration.');
        $this->assertSame('rubric', $copy->grademethod, 'The rubric came across with the activity.');
        $this->assertEquals(1, (int) $copy->enabled, 'A filling that came across leaves autograder on.');

        $restored = advanced_grading::decode($copy->advancedgrading);
        $this->assertNotEmpty($restored, 'The filling is carried over, not dropped.');

        // The ids have to have moved: a copy still naming the old ones would
        // be marking against the original course's rubric.
        $this->assertEmpty(
            array_intersect(array_keys($restored), array_keys($criteria)),
            'Every criterion id is the restored one, not the one backed up.'
        );

        $this->assertTrue(
            advanced_grading::filling_is_current($newcm, $copy->advancedgrading),
            'The filling fits the rubric that actually came back.'
        );

        // Valid is not the same as right: a remap pointing at the wrong level
        // of the right criterion would pass the check above and grade wrongly.
        foreach (advanced_grading::criteria($newcm) as $criterionid => $criterion) {
            $this->assertArrayHasKey($criterionid, $restored, $criterion['description'] . ' is answered.');
            $this->assertSame(
                'Present',
                $criterion['levels'][(int) $restored[$criterionid]['levelid']]['definition'],
                $criterion['description'] . ': still marked at the level it was marked at.'
            );
        }
    }

    /**
     * A rubric activity with nothing to mark comes back switched off.
     *
     * Enabled, it would create a decision per student and fail every one of
     * them at whatever moment each came due, with nothing on screen beforehand
     * to say why. Off, the teacher meets the form's own notice first.
     */
    public function test_a_rubric_with_nothing_to_mark_is_restored_switched_off(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->define_rubric($cm);

        // Switched on, but never told what to mark.
        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            true,
            'rubric',
            null,
            null,
            0,
            (int) $teacher->id
        );

        $DB->delete_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':c'),
            ['c' => '%local_autograder%']
        );

        $duplicate = $this->duplicate($course, $cm);

        $copy = config_repository::get_for_cm((int) $duplicate->id);
        $this->assertNotFalse($copy, 'The configuration is still restored, so the teacher finds it.');
        $this->assertEquals(0, (int) $copy->enabled, 'But switched off, because there is nothing to mark.');
        $this->assertNull($copy->advancedgrading);

        $this->assertEmpty(
            $DB->get_records_select('task_adhoc', $DB->sql_like('classname', ':c'), ['c' => '%catch_up_module%']),
            'And nothing is queued for an activity that would only fail.'
        );
    }

    /**
     * The id of the level with this definition, on one criterion.
     *
     * @param array $criterion As {@see advanced_grading::criteria()} shapes it.
     * @param string $definition The level's own wording, e.g. "Present".
     * @return int
     */
    private function level_called(array $criterion, string $definition): int {
        foreach ($criterion['levels'] as $levelid => $level) {
            if ($level['definition'] === $definition) {
                return (int) $levelid;
            }
        }

        $this->fail("The rubric has no level called {$definition}.");
    }

    /**
     * Duplicates an activity the way the course page does.
     *
     * Moodle 5.2 deprecates duplicate_module() in favour of cmactions::duplicate()
     * (MDL-86858), which older releases do not have.
     *
     * @param \stdClass $course
     * @param \cm_info|\stdClass $cm
     * @return \cm_info|null
     */
    private function duplicate(\stdClass $course, $cm): ?\cm_info {
        $actions = \core_courseformat\formatactions::cm((int) $course->id);
        if (method_exists($actions, 'duplicate')) {
            return $actions->duplicate((int) $cm->id);
        }
        return duplicate_module($course, $cm);
    }

    /**
     * Puts a two-criterion rubric on an assignment and makes it the active
     * grading method, the two steps core itself takes.
     *
     * @param \cm_info|\stdClass $cm
     */
    private function define_rubric($cm): void {
        $context = \context_module::instance((int) $cm->id);

        $this->getDataGenerator()
            ->get_plugin_generator('core_grading')
            ->create_instance($context, 'mod_assign', 'submissions', 'rubric');

        $this->getDataGenerator()
            ->get_plugin_generator('gradingform_rubric')
            ->create_instance($context, 'mod_assign', 'submissions', 'Test rubric', 'For autograder', [
                'Argument' => ['Absent' => 0, 'Present' => 5],
                'Evidence' => ['Absent' => 0, 'Present' => 5],
            ]);
    }
}
