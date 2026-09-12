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
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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

    // `local/autograder:viewreport` was dropped in 2026091200: seeing the
    // report is report_autograder's business, that plugin defines its own
    // capability per level, and nothing here ever checked this one. No step is
    // needed — core calls `update_capabilities()` on every plugin upgrade, and
    // that removes whatever `db/access.php` no longer declares.

    return true;
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

    $courseidindex = new xmldb_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

    if (!$dbman->index_exists($table, $courseidindex)) {
        $dbman->add_index($table, $courseidindex);
    }

    // The old plugin never enforced one row per cmid at the database level —
    // it only ever wrote one because its own code always looked cmid up first.
    $cmidunique = new xmldb_key('uq_locautog_config_cmid', XMLDB_KEY_UNIQUE, ['cmid']);

    if (!$dbman->find_key_name($table, $cmidunique)) {
        $dbman->add_key($table, $cmidunique);
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
