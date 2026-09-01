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
 * Restore of plugin local is here.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @author      Eduardo Cubias <eduardo.cubias@ct.uneatlantico.es>
 * @author      Hector Arrechea <hector.arrechea@uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class restore_local_autograder_plugin extends restore_local_plugin
{

    /** @var array Stores autograder records to insert after execute */
    protected $pendingrecords = [];

    /**
     * Define the paths in the backup XML that we want to process at module level.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure()
    {
        if (!get_config('local_autograder', 'enable')) return;

        $paths = [];
        $paths[] = new restore_path_element(
            'local_autograder',
            $this->get_pathfor('/local_autograder')
        );

        return $paths;
    }

    /**
     * Collect each local_autograder record from the backup for deferred processing.
     * @param array|stdClass $data
     */
    public function process_local_autograder($data)
    {
        $this->pendingrecords[] = (object)$data;
    }

    /**
     * Called after the parent module has been fully restored and all mappings
     * are guaranteed to exist. Insert the collected autograder records now.
     */
    public function after_restore_module()
    {
        global $DB;

        foreach ($this->pendingrecords as $data) {
            $newcmid = $this->get_mappingid('course_module', $data->cmid);
            if (!$newcmid) {
                $newcmid = $this->task->get_moduleid();
            }
            if ($newcmid) {
                $data->cmid = $newcmid;
                $data->courseid = $this->task->get_courseid();
                unset($data->id);
                $DB->insert_record('local_autograder', $data);
            }
        }

        $this->pendingrecords = [];
    }
}
