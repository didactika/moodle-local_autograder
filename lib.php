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
require_once(__DIR__ . '/classes/form/autograder_form.php');


/**
 * Callback to add elements to the course module settings form after saving.
 *
 * @param stdClass $data
 * @return stdClass
 * @throws dml_exception
 */
function local_autograder_coursemodule_edit_post_actions($data) {
    if (!get_config('local_autograder', 'enable')) {
        return $data;
    }
    return \local_autograder\activity_save::coursemodule_edit_post_actions($data);
}

/**
 * Get autograder configuration from the mdl_local_autograder table
 * for a specific module context.
 *
 * @param context_module $context
 * @return array
 */
function local_autograder_get_cm_config(context_module $context): array {
    global $DB;
    if (!get_config('local_autograder', 'enable')) return [];
    $record = $DB->get_record('local_autograder', ['cmid' => $context->instanceid]);
    if (!$record) {
        return [
            'id' => 0,
            'cmid' => 0,
            'enabled' => false,
            'grade_to_assign' => null,
            'processing_delay_seconds' => 0,
        ];
    }
    if (!(bool)$record->enable) return [];

    return [
        'id' => $record->id,
        'cmid' => $record->cmid,
        'enabled' => (bool)$record->enable,
        'grade_to_assign' => $record->gradetoassign,
        'processing_delay_seconds' => $record->processingdelayseconds,
    ];
}


function local_autograder_grade_to_save($data){
    if (!get_config('local_autograder', 'enable')) return;
    if($data->modulename == 'forum'){
        return $data->grade_forum;
    }else{
        return $data->grade;
    }
}


