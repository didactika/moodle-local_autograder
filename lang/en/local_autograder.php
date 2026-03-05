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
 * Plugin strings are defined here.
 *
 * @package     local_autograder
 * @category    string
 * @copyright   2025 ADSDR <eduardo.cubias@ct.uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

    $string['pluginname'] = 'Local Autograder';

    $string['form:heading'] = 'Automatic Grading';
    $string['form:enabled'] = 'Enable automatic grading for this activity';
    $string['form:grade'] = 'Automatic grade to assign';
    $string['form:error_numeric'] = 'Enter only integer numeric value';
    $string['form:error_max_grade'] = 'The automatic grading score cannot be higher than the maximum score';
    $string['form:days_to_complete'] = 'Days to wait before automatic grading';
    $string['form:hours_to_complete'] = 'Hours to wait before automatic grading';
    $string['form:minutes_to_complete'] = 'Minutes to wait before automatic grading';
    $string['form:time_to_complete'] = 'Time to wait before grading';
    $string['form:enabled_help'] = 'Enable/disable automatic grading for this activity. If enabled, the automatic grade will be assigned after the configured time.';
    $string['form:grade_help'] = 'Integer numeric grade that will be automatically assigned when the configured time ends.';
    $string['form:time_to_complete_help'] = 'Sets the period (days or hours or minutes) after which automatic grading will be applied.';
    $string['form:error_days_range'] = 'Days must be between 0 and 100.';
    $string['form:error_hours_range'] = 'Hours must be between 0 and 24.';
    $string['form:error_minutes_range'] = 'Minutes must be between 0 and 60.';
    $string['form:error_all_time_zero'] = 'At least one of days, hours, or minutes must be greater than 0.';
    $string['form:error_completion_tracking'] = 'Automatic grading is enabled, set completion tracking';
    $string['form:error_type_grade'] = 'Automatic grading is enabled, only score type is accepted';

    $string['settings:enable'] = 'Enable autograder plugin';
    $string['settings:enableDescription'] = 'Default value: Yes';

    $string['setting:days_to_completeTitle'] = 'Days to wait before automatic grading';
    $string['setting:days_to_completeHelper'] = 'Number of days the system must wait before automatic grading.';

    $string['setting:hours_to_completeTitle'] = 'Hours to wait before automatic grading';
    $string['setting:hours_to_completeHelper'] = 'Number of hours allowed (in addition to days) that the system must wait before automatic grading.';

    $string['setting:minutes_to_completeTitle'] = 'Minutes to wait before automatic grading';
    $string['setting:minutes_to_completeHelper'] = 'Number of minutes allowed (in addition to days and hours) that the system must wait before automatic grading.';

    $string['setting:default_gradeTitle'] = 'Automatic grade to assign';
    $string['setting:default_gradeHelper'] = 'Default grade to assign if no specific grade is provided. Enter only integer numeric value';

    $string['event:autograder_created'] = 'Autograder Created';
    $string['event:autograder_updated'] = 'Autograder Updated';

    $string['form:error_completion_tracking'] = 'To enable the autograder, you must activate completion tracking in the "Activity completion" section.';
    $string['form:error_type_grade'] = 'The autograder only works with activities configured with grading by "Score" (not scales or no grading).';
