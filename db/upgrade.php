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

/**
 * Upgrade steps are defined here.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Runs every upgrade step this plugin has ever needed.
 *
 * @param int $oldversion The version being upgraded from.
 * @return bool Always true.
 */
function xmldb_local_autograder_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026091100) {
        upgrade_local_autograder_from_v2($dbman);
        upgrade_local_autograder_create_missing_tables($dbman);

        upgrade_plugin_savepoint(true, 2026091100, 'local', 'autograder');
    }

    // The `local/autograder:viewreport` capability was dropped in 2026091200:
    // seeing the report is report_autograder's business, that plugin defines
    // its own capability per level, and nothing here ever checked this one. No
    // step is needed — core calls `update_capabilities()` on every plugin
    // upgrade, and that removes whatever `db/access.php` no longer declares.

    if ($oldversion < 2026091201) {
        upgrade_local_autograder_catch_up_everything();

        upgrade_plugin_savepoint(true, 2026091201, 'local', 'autograder');
    }

    if ($oldversion < 2026091300) {
        // Until now a decision that had been called off stayed called off, so
        // an activity switched off and back on kept every student cancelled
        // and graded nobody. The same sweep picks them up again.
        upgrade_local_autograder_catch_up_everything();

        upgrade_plugin_savepoint(true, 2026091300, 'local', 'autograder');
    }

    if ($oldversion < 2026091602) {
        // A savepoint whose only job is to exist. The `gradeonbehalf`
        // capability was dropped from `db/access.php` without the version
        // moving, so `update_capabilities()` never ran and the site kept a
        // capability no plugin declares any more — which Moodle then reports
        // as a missing language string every time it lists the role's
        // permissions. Bumping the version is what actually removes it.
        upgrade_plugin_savepoint(true, 2026091602, 'local', 'autograder');
    }

    if ($oldversion < 2026091603) {
        upgrade_local_autograder_adopt_teacher_settings();

        upgrade_plugin_savepoint(true, 2026091603, 'local', 'autograder');
    }

    return true;
}

/**
 * Gives autograder its own answer to "which roles teach a course".
 *
 * It used to read another plugin's settings for this. Sites already running
 * that way have the answer written down somewhere, and starting from a fresh
 * default would silently change whose name their grades carry — so whatever
 * they had is copied across once, and only a site that had nothing gets the
 * defaults the settings page offers.
 */
function upgrade_local_autograder_adopt_teacher_settings(): void {
    $adopted = [
        'teacher_roles' => 'editingteacher,teacher',
        'coordinator_roles' => 'editingteacher',
        'subject_course_category' => '1',
        'program_course_category' => '1',
    ];

    foreach ($adopted as $name => $fallback) {
        if (get_config('local_autograder', $name) !== false) {
            // Already answered here, on an upgrade run more than once.
            continue;
        }

        $previous = get_config('local_resume', $name);

        set_config($name, $previous === false ? $fallback : $previous, 'local_autograder');
    }
}

/**
 * Turns the v2 `local_autograder` table into `local_autograder_config`.
 *
 * A site that never had v2 installed has no `local_autograder` table at all —
 * this is a straight no-op for it, and
 * {@see upgrade_local_autograder_create_missing_tables()} builds the config
 * table (among the others) from install.xml instead.
 *
 * @param database_manager $dbman
 */
function upgrade_local_autograder_from_v2(database_manager $dbman): void {
    global $DB;

    $oldtable = new xmldb_table('local_autograder');

    if (!$dbman->table_exists($oldtable)) {
        return;
    }

    $newtable = new xmldb_table('local_autograder_config');

    if ($dbman->table_exists($newtable)) {
        // A previous, partial run of this same step already renamed it.
        return;
    }

    $dbman->rename_table($oldtable, 'local_autograder_config');
    $table = new xmldb_table('local_autograder_config');

    $renames = [
        'enable' => ['enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'],
        'gradetoassign' => ['gradevalue', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null],
        'processingdelayseconds' => ['delayseconds', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'],
    ];

    foreach ($renames as $oldname => [$newname, $type, $precision, $unsigned, $notnull, $sequence, $default]) {
        $oldfield = new xmldb_field($oldname, $type, $precision, $unsigned, $notnull, $sequence, $default);

        if ($dbman->field_exists($table, $oldfield) && !$dbman->field_exists($table, new xmldb_field($newname))) {
            $dbman->rename_field($table, $oldfield, $newname);
        }
    }

    $grademethod = new xmldb_field('grademethod', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'point');

    if (!$dbman->field_exists($table, $grademethod)) {
        // Every v2 row was point grading — it is the only method v2 ever offered.
        $dbman->add_field($table, $grademethod);
    }

    $advancedgrading = new xmldb_field('advancedgrading', XMLDB_TYPE_TEXT, null, null, null, null, null);

    if (!$dbman->field_exists($table, $advancedgrading)) {
        $dbman->add_field($table, $advancedgrading);
    }

    if (!$dbman->field_exists($table, new xmldb_field('usermodified'))) {
        $dbman->add_field($table, new xmldb_field('usermodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'));
        $DB->execute("UPDATE {local_autograder_config} SET usermodified = 2"); // 2 = the primary admin account.
    }

    upgrade_local_autograder_add_timecreated($dbman, $table);
    upgrade_local_autograder_make_rows_fit($dbman, $table);

    // A rename leaves the column's type alone, so every field the v2 table
    // already had is still shaped the way v2 shaped it. Bring each one up to
    // what install.xml declares, or the first save on the upgraded site fails
    // — `gradevalue` above all, which v2 kept as a plain integer and which
    // this plugin now writes decimals into.
    $definitions = [
        new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null),
        new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null),
        new xmldb_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
        new xmldb_field('gradevalue', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null),
        new xmldb_field('delayseconds', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0'),
    ];

    // The v2 schema already indexed cmid and courseid somehow (a plain
    // index, or a key that is index-backed either way), and a column type
    // change is refused outright while any index still depends on it. Both
    // are recreated a few lines below regardless (see $courseidindex,
    // $cmidunique), so the old one just needs to be out of the way, not
    // replaced in place.
    foreach (['cmid', 'courseid'] as $indexedfield) {
        $oldindex = new xmldb_index($indexedfield, XMLDB_INDEX_NOTUNIQUE, [$indexedfield]);

        if ($dbman->find_index_name($table, $oldindex) !== false) {
            $dbman->drop_index($table, $oldindex);
        }
    }

    foreach ($definitions as $field) {
        if (!$dbman->field_exists($table, $field)) {
            continue;
        }

        $dbman->change_field_type($table, $field);
        $dbman->change_field_notnull($table, $field);
        $dbman->change_field_default($table, $field);
    }

    $courseidindex = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

    if (!$dbman->index_exists($table, $courseidindex)) {
        $dbman->add_index($table, $courseidindex);
    }

    // The old plugin never enforced one row per cmid at the database level —
    // it only ever wrote one because its own code always looked cmid up first.
    // "Only ever wrote one" is not the same as "cannot hold two", though, and
    // adding the key on a table that holds two would fail the whole upgrade,
    // so the duplicates are settled first (see make_rows_fit()).
    $cmidunique = new xmldb_key('uq_locautog_config_cmid', XMLDB_KEY_UNIQUE, ['cmid']);

    if (!$dbman->find_key_name($table, $cmidunique)) {
        $dbman->add_key($table, $cmidunique);
    }
}

/**
 * Gives the migrated table the `timecreated` column v2 never had.
 *
 * v2 only ever recorded when a configuration was last changed. The closest
 * honest answer to when it was created is that same instant, so that is what
 * the migrated rows get rather than the moment of the upgrade, which would
 * claim every configuration on the site was made the day it was upgraded.
 *
 * @param database_manager $dbman
 * @param xmldb_table $table
 */
function upgrade_local_autograder_add_timecreated(database_manager $dbman, xmldb_table $table): void {
    global $DB;

    $field = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

    if ($dbman->field_exists($table, new xmldb_field('timecreated'))) {
        return;
    }

    $dbman->add_field($table, $field);
    $DB->execute('UPDATE {local_autograder_config} SET timecreated = timemodified');

    // The install.xml file declares it without a default; the default was only
    // there so the column could be added to a table that already had rows.
    $dbman->change_field_default($table, new xmldb_field(
        'timecreated',
        XMLDB_TYPE_INTEGER,
        '10',
        null,
        XMLDB_NOTNULL,
        null,
        null
    ));
}

/**
 * Settles the rows v2 allowed but v3 does not.
 *
 * Two things v2 never stopped: a row with no course module, which is a
 * configuration for nothing, and two rows for the same one, which v3 forbids
 * with a unique key that would refuse to be created. The newest row wins,
 * because it is the one whose settings the teacher last saw.
 *
 * @param database_manager $dbman
 * @param xmldb_table $table
 */
function upgrade_local_autograder_make_rows_fit(database_manager $dbman, xmldb_table $table): void {
    global $DB;

    unset($dbman, $table);

    $DB->execute('DELETE FROM {local_autograder_config} WHERE cmid IS NULL OR courseid IS NULL');
    $DB->execute('UPDATE {local_autograder_config} SET enabled = 0 WHERE enabled IS NULL');
    $DB->execute('UPDATE {local_autograder_config} SET delayseconds = 0 WHERE delayseconds IS NULL');

    $duplicates = $DB->get_records_sql(
        'SELECT cmid, MAX(id) AS keepid
           FROM {local_autograder_config}
       GROUP BY cmid
         HAVING COUNT(1) > 1'
    );

    foreach ($duplicates as $duplicate) {
        $DB->delete_records_select(
            'local_autograder_config',
            'cmid = :cmid AND id <> :keepid',
            ['cmid' => $duplicate->cmid, 'keepid' => $duplicate->keepid]
        );
    }
}

/**
 * Creates whichever of the three tables `install.xml` now declares are still
 * missing — `local_autograder_config` on a site with no v2 history at all,
 * and always `local_autograder_decision`/`local_autograder_grade_log`, which
 * v2 never had.
 *
 * @param database_manager $dbman
 */
function upgrade_local_autograder_create_missing_tables(database_manager $dbman): void {
    $xmlfile = __DIR__ . '/install.xml';

    foreach (['local_autograder_config', 'local_autograder_decision', 'local_autograder_grade_log'] as $name) {
        $table = new xmldb_table($name);

        if ($dbman->table_exists($table)) {
            continue;
        }

        $dbman->install_one_table_from_xmldb_file($xmlfile, $name);
    }
}

/**
 * Asks autograder to look at every activity it is switched on for.
 *
 * This is the handover. Until v3 the grading was done by an external service
 * that kept its own schedule; the migration above brings each activity's
 * settings across but not a single decision, because decisions are worked out
 * from Moodle's own data rather than copied. Without this step a site would
 * upgrade, keep all its configuration, and quietly grade nobody: the students
 * who had already submitted have no event left to fire, so nothing would ever
 * create their decision, and their deadlines would pass in silence.
 *
 * Safe to run more than once — {@see \local_autograder\local\decision\decision_repository::ensure()}
 * settles on the same answer every time, and the tasks are deduplicated.
 */
function upgrade_local_autograder_catch_up_everything(): void {
    global $DB;

    $configs = $DB->get_records('local_autograder_config', ['enabled' => 1], 'cmid', 'id, cmid');

    foreach ($configs as $config) {
        $task = new \local_autograder\task\catch_up_module();
        $task->set_custom_data((object) ['cmid' => (int) $config->cmid]);

        \core\task\manager::queue_adhoc_task($task, true);
    }

    if (!empty($configs)) {
        mtrace('local_autograder: queued a catch-up for ' . count($configs) . ' activity(ies).');
    }
}
