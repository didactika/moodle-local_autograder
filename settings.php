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
 * @copyright   2025 ADSDR <eduardo.cubias@ct.uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {
        $settings = new admin_settingpage('local_autograder', get_string('pluginname', 'local_autograder'));

        $settings->add(
            new admin_setting_configcheckbox(
                'local_autograder/enable',
                get_string('settings:enable', 'local_autograder'),
                get_string('settings:enableDescription', 'local_autograder'),
                1
            )
        );

        $settings->add(
            new admin_setting_configtext(
                'local_autograder/defaultGrade',
                get_string('setting:default_gradeTitle', 'local_autograder'),
                get_string('setting:default_gradeHelper', 'local_autograder'),
                "10",
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configtext(
                'local_autograder/daysToComplete',
                get_string('setting:days_to_completeTitle', 'local_autograder'),
                get_string('setting:days_to_completeHelper', 'local_autograder'),
                "2",
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configtext(
                'local_autograder/hoursToComplete',
                get_string('setting:hours_to_completeTitle', 'local_autograder'),
                get_string('setting:hours_to_completeHelper', 'local_autograder'),
                "0",
                PARAM_INT
            )
        );

        $settings->add(
            new admin_setting_configtext(
                'local_autograder/minutesToComplete',
                get_string('setting:minutes_to_completeTitle', 'local_autograder'),
                get_string('setting:minutes_to_completeHelper', 'local_autograder'),
                "0",
                PARAM_INT
            )
        );

        $ADMIN->add('localplugins', $settings);
    }
}
