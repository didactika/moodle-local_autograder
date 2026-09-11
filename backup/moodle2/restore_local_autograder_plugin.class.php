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
 * Puts an activity's autograder settings back, and asks autograder to look at
 * the restored activity afresh.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_autograder\local\config_repository;
use local_autograder\local\eligibility;
use local_autograder\task\catch_up_module;

/**
 * Restores the configuration, then leaves the decisions to be worked out from
 * the restored course rather than copied from the old one.
 *
 * Two things are deliberately not carried over as they stood. The grading
 * method is re-read from the activity that actually exists here, because a
 * restore can land in a site whose scales differ; and a rubric or marking
 * guide filling is dropped, because restoring a grading form gives every
 * criterion a new id, so the stored selection would point at criteria that no
 * longer exist. The teacher is asked to pick the levels again rather than
 * being given a configuration that looks complete and is not.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_autograder_plugin extends restore_local_plugin {
    /**
     * The backed-up configuration, held until the restored activity is
     * complete enough to read its real grading method back off it.
     *
     * @var \stdClass|null
     */
    protected ?\stdClass $pending = null;

    /**
     * The paths this plugin claims inside an activity.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure() {
        return [
            new restore_path_element('autograder_config', $this->get_pathfor('/autograder_config')),
        ];
    }

    /**
     * Holds one activity's configuration until the activity is finished.
     *
     * @param array|object $data
     */
    public function process_autograder_config($data) {
        $this->pending = (object) $data;
    }

    /**
     * Writes the configuration against the restored activity, then has
     * autograder decide what it owes the students who came across with it.
     *
     * By this point the activity, its grade item and its grading form all
     * exist, which is what makes reading the real grading method back off it
     * possible at all.
     */
    public function after_restore_module() {
        if ($this->pending === null) {
            return;
        }

        $data = $this->pending;
        $this->pending = null;

        $cmid = (int) $this->task->get_moduleid();

        if ($cmid === 0) {
            return;
        }

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm || !eligibility::is_module_type_enabled($cm->modname)) {
            // The activity type is not one this site allows autograder on.
            return;
        }

        $grademethod = eligibility::grademethod_for($cm);

        if ($grademethod === null) {
            // It came back not graded at all; a configuration that can never
            // fire is worse than none.
            return;
        }

        $advanced = in_array($grademethod, ['rubric', 'guide'], true);

        config_repository::upsert_for_cm(
            $cmid,
            (int) $cm->course,
            !empty($data->enabled),
            $grademethod,
            $advanced ? null : (isset($data->gradevalue) ? (float) $data->gradevalue : null),
            null,
            (int) $data->delayseconds,
            (int) $this->get_mappingid('user', $data->usermodified, $this->task->get_userid())
        );

        if (empty($data->enabled)) {
            return;
        }

        $task = new catch_up_module();
        $task->set_custom_data((object) ['cmid' => $cmid]);

        // Adhoc rather than inline: a restore should not wait on a class-sized
        // sweep, and the enrolments this reads may still be arriving.
        \core\task\manager::queue_adhoc_task($task, true);
    }
}
