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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @author      Eduardo Cubias <eduardo.cubias@ct.uneatlantico.es>
 * @author      Hector Arrechea <hector.arrechea@uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_autograder_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_autograder');
    $table_event = new xmldb_table('local_autograder_event_data');
    $xmlfile = __DIR__ . '/install.xml';

    if ($oldversion < 2024121301) {
        $taskname = '\local_autograder\task\review_pending_autograde';
        $task = $DB->get_record('task_scheduled', ['classname' => $taskname]);
        if ($task) {
            $task->disabled = 1;
            $DB->update_record('task_scheduled', $task);
        }
    }

    if ($oldversion < 2025102704) {
        if ($dbman->table_exists($table_event)) {
            $dbman->drop_table($table_event);
        }
        if ($dbman->table_exists($table)) {
            if ($dbman->field_exists($table, new xmldb_field('isautograded')) &&
                !$dbman->field_exists($table, new xmldb_field('enable'))) {
                $dbman->rename_field($table, new xmldb_field('isautograded', XMLDB_TYPE_INTEGER, '1'), 'enable');
            }

            if ($dbman->field_exists($table, new xmldb_field('autogradergrade')) &&
                !$dbman->field_exists($table, new xmldb_field('gradetoassign'))) {
                $dbman->rename_field($table, new xmldb_field('autogradergrade', XMLDB_TYPE_INTEGER, '10'), 'gradetoassign');
            }

            if ($dbman->field_exists($table, new xmldb_field('datetograde')) &&
                !$dbman->field_exists($table, new xmldb_field('processingdelayseconds'))) {
                $dbman->rename_field($table, new xmldb_field('datetograde', XMLDB_TYPE_INTEGER, '10'), 'processingdelayseconds');
                $DB->execute("UPDATE {local_autograder} SET processingdelayseconds = processingdelayseconds * 86400");
            }
        }
        $xmldbfile = new xmldb_file($xmlfile);
        $xmldbfile->loadXMLStructure();
        $xmlstructure = $xmldbfile->getStructure();
        $xmldbtable = $xmlstructure->getTable('local_autograder');

        if (!$dbman->table_exists($table)) {
            $dbman->install_one_table_from_xmldb_file($xmlfile, 'local_autograder');
        } else {
            foreach ($xmldbtable->getFields() as $field) {
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }
            }

            $existingfields = $DB->get_columns($table->getName());
            foreach (array_keys($existingfields) as $existingfieldname) {
                $found = false;
                foreach ($xmldbtable->getFields() as $field) {
                    if ($field->getName() === $existingfieldname) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $dbman->drop_field($table, new xmldb_field($existingfieldname));
                }
            }

            foreach ($xmldbtable->getKeys() as $key) {

                if ($key->getType() === XMLDB_KEY_PRIMARY) {
                    continue;
                }

                $indexType = ($key->getType() === XMLDB_KEY_UNIQUE || $key->getType() === XMLDB_KEY_FOREIGN_UNIQUE)
                    ? XMLDB_INDEX_UNIQUE
                    : XMLDB_INDEX_NOTUNIQUE;

                $index = new xmldb_index(
                    'tmp_index',
                    $indexType,
                    $key->getFields()
                );

                if (!$dbman->find_index_name($table, $index)) {
                    $dbman->add_key($table, $key);
                }
            }




        }

        upgrade_plugin_savepoint(true, 2025102704, 'local', 'autograder');
    }

    return true;
}