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
 * Carries an activity's autograder settings along with the activity.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Backs up what a teacher configured, and only that.
 *
 * The decisions and the grading log are deliberately left out. Both are
 * worked out from this course's own completions, submissions and enrolments,
 * and both point at rows — a queued task, a grading teacher — that mean
 * nothing in the course this backup is restored into. Restoring them would
 * produce a copy of the record without the facts behind it; instead the
 * restore asks autograder to look at the restored course and decide again,
 * which gets the same answers where the same data came across and the right
 * ones where it did not.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_autograder_plugin extends backup_local_plugin {
    /**
     * One activity's autograder configuration.
     *
     * @return backup_plugin_element
     */
    protected function define_module_plugin_structure() {
        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($wrapper);

        $config = new backup_nested_element('autograder_config', ['id'], [
            'enabled',
            'grademethod',
            'gradevalue',
            'advancedgrading',
            'delayseconds',
            'usermodified',
            'timecreated',
            'timemodified',
        ]);

        $wrapper->add_child($config);

        $config->set_source_table('local_autograder_config', ['cmid' => backup::VAR_MODID]);
        $config->annotate_ids('user', 'usermodified');

        return $plugin;
    }
}
