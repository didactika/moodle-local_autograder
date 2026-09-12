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

use local_autograder\local\config_repository;

/**
 * Upgrading a site that really had v2 installed.
 *
 * The v2 table is built here from its own definition rather than read from
 * `old/`, so the test says what it is upgrading from instead of depending on a
 * directory that is only kept for reference.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      ::upgrade_local_autograder_from_v2
 */
final class upgrade_from_v2_test extends \advanced_testcase {
    /**
     * Replaces the v3 tables with the v2 one, holding real v2 rows.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();

        require_once($CFG->dirroot . '/local/autograder/db/upgrade.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        $this->become_a_v2_site();
    }

    /**
     * Puts the database back the way a site running v2 had it.
     *
     * Called again by any test that has to build its course and activities
     * first: creating an activity runs this plugin's own settings hook, which
     * reads the v3 table, so the tables cannot already be gone by then.
     */
    private function become_a_v2_site(): void {
        global $DB;

        $dbman = $DB->get_manager();

        foreach (['local_autograder_config', 'local_autograder_decision', 'local_autograder_grade_log'] as $name) {
            $table = new \xmldb_table($name);

            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        $old = new \xmldb_table('local_autograder');

        if (!$dbman->table_exists($old)) {
            $dbman->create_table($this->v2_table());
        }
    }

    /**
     * Every configuration a site had under v2 comes across, with its values.
     */
    public function test_the_old_configurations_come_across(): void {
        global $DB;

        $dbman = $DB->get_manager();

        $enabled = $this->v2_row(101, 11, 1, 70, 2 * DAYSECS);
        $disabled = $this->v2_row(102, 11, 0, 55, 0);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertFalse(
            $dbman->table_exists(new \xmldb_table('local_autograder')),
            'The v2 table is renamed, not copied, so it must be gone.'
        );

        $rows = $DB->get_records('local_autograder_config', null, 'cmid');
        $this->assertCount(2, $rows);

        $first = $DB->get_record('local_autograder_config', ['cmid' => 101]);
        $this->assertEquals(1, (int) $first->enabled);
        $this->assertEquals(70, (float) $first->gradevalue);
        $this->assertEquals(2 * DAYSECS, (int) $first->delayseconds);
        $this->assertSame('point', $first->grademethod, 'Point is the only method v2 ever had.');
        $this->assertNull($first->advancedgrading);

        $second = $DB->get_record('local_autograder_config', ['cmid' => 102]);
        $this->assertEquals(0, (int) $second->enabled);

        unset($enabled, $disabled);
    }

    /**
     * The other two tables, which v2 never had, are created.
     */
    public function test_the_tables_v2_never_had_are_created(): void {
        global $DB;

        $dbman = $DB->get_manager();

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertTrue($dbman->table_exists(new \xmldb_table('local_autograder_decision')));
        $this->assertTrue($dbman->table_exists(new \xmldb_table('local_autograder_grade_log')));
    }

    /**
     * The migrated table can still be written to.
     *
     * This is the one that matters most and the easiest to miss: a rename that
     * leaves a column out does not fail, it fails later — the first time a
     * teacher saves an activity on the upgraded site.
     */
    public function test_the_migrated_table_still_accepts_a_configuration(): void {
        global $DB;

        $dbman = $DB->get_manager();

        $this->v2_row(103, 12, 1, 60, HOURSECS);
        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        // Saving an activity on the upgraded site, which is what a teacher
        // does the moment they open one.
        $saved = config_repository::upsert_for_cm(103, 12, true, 'point', 62.5, null, HOURSECS, 2);

        $this->assertNotEmpty($saved->id);

        $stored = $DB->get_record('local_autograder_config', ['cmid' => 103]);
        $this->assertEquals(62.5, (float) $stored->gradevalue, 'A grade with decimals has to survive.');
        $this->assertNotEmpty($stored->timecreated);

        // And a brand new one, which is the other half of the same question.
        $new = config_repository::upsert_for_cm(104, 12, true, 'scale', 3.0, null, 0, 2);
        $this->assertNotEmpty($new->id);
    }

    /**
     * Two v2 rows for the same activity do not stop the upgrade.
     *
     * v2 had no unique key on cmid, so a site can hold two; v3 does, and
     * creating it on a table that holds two would fail the whole upgrade. The
     * newest row is the one the teacher last saw, so it is the one kept.
     */
    public function test_two_rows_for_one_activity_do_not_break_the_upgrade(): void {
        global $DB;

        $dbman = $DB->get_manager();

        $this->v2_row(106, 14, 1, 40, 0);
        $newest = $this->v2_row(106, 14, 0, 90, DAYSECS);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $rows = $DB->get_records('local_autograder_config', ['cmid' => 106]);

        $this->assertCount(1, $rows);
        $this->assertEquals($newest, (int) reset($rows)->id);
        $this->assertEquals(90, (float) reset($rows)->gradevalue);
    }

    /**
     * A v2 row with no course module is a configuration for nothing, and v3
     * will not hold one.
     */
    public function test_a_row_with_no_activity_is_dropped(): void {
        global $DB;

        $dbman = $DB->get_manager();

        $DB->insert_record('local_autograder', (object) [
            'cmid' => null,
            'courseid' => 15,
            'enable' => 1,
            'gradetoassign' => 50,
            'processingdelayseconds' => 0,
            'timemodified' => time(),
        ]);
        $this->v2_row(107, 15, 1, 50, 0);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertSame(1, $DB->count_records('local_autograder_config'));
        $this->assertSame(1, $DB->count_records('local_autograder_config', ['cmid' => 107]));
    }

    /**
     * A configuration migrated from v2 says it was made when v2 last saw it,
     * not when the site happened to be upgraded.
     */
    public function test_the_migrated_rows_keep_their_own_date(): void {
        global $DB;

        $dbman = $DB->get_manager();
        $when = time() - YEARSECS;

        $DB->insert_record('local_autograder', (object) [
            'cmid' => 108,
            'courseid' => 16,
            'enable' => 1,
            'gradetoassign' => 50,
            'processingdelayseconds' => 0,
            'timemodified' => $when,
        ]);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertEquals(
            $when,
            (int) $DB->get_field('local_autograder_config', 'timecreated', ['cmid' => 108])
        );
    }

    /**
     * Running the step twice does not lose anything — an upgrade that is
     * retried has to be safe.
     */
    public function test_running_the_migration_twice_is_safe(): void {
        global $DB;

        $dbman = $DB->get_manager();

        $this->v2_row(105, 13, 1, 80, 0);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);
        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertEquals(80, (float) $DB->get_field('local_autograder_config', 'gradevalue', ['cmid' => 105]));
    }

    /**
     * The students the old service still had waiting are picked up.
     *
     * Their schedule lived outside Moodle, in the service's own database, and
     * none of it is imported: what the upgrade does instead is ask autograder
     * to work each student out again from what Moodle itself knows — the
     * submission, the close date, the exceptions. A student who had already
     * handed in before the upgrade therefore ends up with a decision without
     * ever touching the activity again, which is the whole point.
     */
    public function test_the_students_the_old_service_had_waiting_are_picked_up(): void {
        global $DB;

        $dbman = $DB->get_manager();

        // The course and its activity have to exist before the site is put
        // back to v2: creating an activity runs this plugin's settings hook,
        // which reads a table v2 does not have.
        $this->restore_v3_tables();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $student = $generator->create_and_enrol($course, 'student');

        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->submit($assign, $student);
        $this->become_a_v2_site();

        // What v2 had stored for this activity.
        $DB->insert_record('local_autograder', (object) [
            'cmid' => $cm->id,
            'courseid' => $course->id,
            'enable' => 1,
            'gradetoassign' => 70,
            'processingdelayseconds' => DAYSECS,
            'timemodified' => time() - DAYSECS,
        ]);

        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        $this->assertFalse(
            $DB->record_exists('local_autograder_decision', ['cmid' => $cm->id]),
            'The migration itself brings no decisions across; the catch-up makes them.'
        );

        $this->expectOutputRegex('/queued a catch-up for 1 activity/');
        upgrade_local_autograder_catch_up_everything();

        // Run what the upgrade queued, which is what cron would do next.
        $this->run_queued_catch_ups();

        $decision = $DB->get_record('local_autograder_decision', [
            'cmid' => $cm->id,
            'userid' => $student->id,
        ]);

        $this->assertNotFalse($decision, 'The student who had already submitted has to be picked up.');
        $this->assertSame('pending', $decision->status);
        $this->assertSame('submission', $decision->duedatereason);
        $this->assertEquals(
            (int) $decision->baselineduedate + DAYSECS,
            (int) $decision->scheduledgradetime,
            'And on the wait v2 had configured, carried across by the migration.'
        );

        unset($teacher);
    }

    /**
     * Puts the v3 tables back, so a course and its activities can be built
     * before the site is turned back into a v2 one.
     */
    private function restore_v3_tables(): void {
        global $CFG, $DB;

        $dbman = $DB->get_manager();
        $old = new \xmldb_table('local_autograder');

        if ($dbman->table_exists($old)) {
            $dbman->drop_table($old);
        }

        $xmlfile = $CFG->dirroot . '/local/autograder/db/install.xml';

        foreach (['local_autograder_config', 'local_autograder_decision', 'local_autograder_grade_log'] as $name) {
            if (!$dbman->table_exists(new \xmldb_table($name))) {
                $dbman->install_one_table_from_xmldb_file($xmlfile, $name);
            }
        }
    }

    /**
     * Runs every catch-up sitting in the queue.
     */
    private function run_queued_catch_ups(): void {
        global $DB;

        $tasks = $DB->get_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':classname'),
            ['classname' => '%catch_up_module%']
        );

        foreach ($tasks as $record) {
            $task = new \local_autograder\task\catch_up_module();
            $task->set_custom_data(json_decode($record->customdata));
            $task->execute();
        }
    }

    /**
     * Puts an online-text submission in, as v2 would have seen it.
     *
     * @param \stdClass $assign
     * @param \stdClass $user
     */
    private function submit(\stdClass $assign, \stdClass $user): void {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $DB->insert_record('assign_submission', (object) [
            'assignment' => $assign->id,
            'userid' => $user->id,
            'timecreated' => time() - (2 * HOURSECS),
            'timemodified' => time() - (2 * HOURSECS),
            'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            'groupid' => 0,
            'attemptnumber' => 0,
            'latest' => 1,
        ]);
    }

    /**
     * The v2 table, exactly as v2 declared it.
     *
     * @return \xmldb_table
     */
    private function v2_table(): \xmldb_table {
        $table = new \xmldb_table('local_autograder');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null);
        $table->add_field('enable', XMLDB_TYPE_INTEGER, '1', null, null, null);
        $table->add_field('gradetoassign', XMLDB_TYPE_INTEGER, '10', null, null, null);
        $table->add_field('processingdelayseconds', XMLDB_TYPE_INTEGER, '10', null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        return $table;
    }

    /**
     * One row as v2 would have written it.
     *
     * @param int $cmid
     * @param int $courseid
     * @param int $enable
     * @param int $gradetoassign
     * @param int $delayseconds
     * @return int The row id.
     */
    private function v2_row(int $cmid, int $courseid, int $enable, int $gradetoassign, int $delayseconds): int {
        global $DB;

        return (int) $DB->insert_record('local_autograder', (object) [
            'cmid' => $cmid,
            'courseid' => $courseid,
            'enable' => $enable,
            'gradetoassign' => $gradetoassign,
            'processingdelayseconds' => $delayseconds,
            'timemodified' => time(),
        ]);
    }
}
