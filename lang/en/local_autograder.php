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
 * English language strings.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['autograder:configure'] = 'Turn autograder on or off for an activity';
$string['autograder:gradeonbehalf'] = 'Be eligible to have autograder post grades on your behalf';
$string['autograder:manage'] = 'Manage autograder site-wide settings';
$string['autograder:viewreport'] = 'View the autograder report for a course';
$string['error:gradewritefailed'] = 'Moodle refused the grade autograder tried to post.';
$string['error:nogradeitem'] = 'This activity has no grade item to write to.';
$string['event:config_created'] = 'Autograder configuration created';
$string['event:config_deleted'] = 'Autograder configuration deleted';
$string['event:config_updated'] = 'Autograder configuration updated';
$string['form:days_to_complete'] = 'Days';
$string['form:enabled'] = 'Enable autograder';
$string['form:enabled_help'] = 'When enabled, a student who completes this activity is graded automatically, the configured time after it was due, with the grade set below — unless someone grades them by hand first.';
$string['form:error_completion_tracking'] = 'Autograder needs completion tracking enabled on this activity.';
$string['form:error_negative_time'] = 'Cannot be negative.';
$string['form:error_numeric'] = 'Must be a number.';
$string['form:grade'] = 'Grade to assign';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Hours';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:time_to_complete'] = 'Time to wait before grading';
$string['form:time_to_complete_help'] = 'How long to wait, after the due date, before grading.';
$string['modules:intro'] = 'Only activity types enabled here can have autograder configured on one of their instances.';
$string['pluginname'] = 'Autograder';
$string['preference:optout'] = 'Do not let autograder grade in my name';
$string['preference:optout_help'] = 'When checked, autograder will never choose you as the teacher who grades a student, even where you would otherwise qualify.';
$string['preference:saved'] = 'Preference saved.';
$string['privacy:metadata'] = 'Autograder keeps, per student, whether and when they are due to be graded automatically, and a log of every grading attempt.';
$string['setting:default_days'] = 'Default delay (days)';
$string['setting:default_grade'] = 'Default grade';
$string['setting:default_grade_desc'] = 'Suggested grade when autograder is first enabled on an activity.';
$string['setting:default_hours'] = 'Default delay (hours)';
$string['setting:default_minutes'] = 'Default delay (minutes)';
$string['setting:default_time_desc'] = 'How long to wait, after the due date, before grading — split across days, hours and minutes.';
$string['setting:fallback_grader'] = 'Fallback grader';
$string['setting:fallback_grader_desc'] = 'Used only when no teacher in the course itself is eligible to grade a student (see the "gradeonbehalf" capability). Only users who could plausibly grade something are offered.';
$string['setting:fallback_grader_ineligible'] = 'That user does not hold a grading capability and cannot be set as the fallback grader.';
$string['setting:fallback_grader_none'] = 'None';
$string['setting:fallback_grader_placeholder'] = 'Search for a user…';
$string['setting:modules_disable'] = 'Disable autograder for {$a}';
$string['setting:modules_enable'] = 'Enable autograder for {$a}';
$string['setting:modules_enabled_column'] = 'Enabled';
$string['setting:modules_heading'] = 'Choose which activity types can have autograder configured on the <a href="{$a->url}">autogradable activities page</a>.';
$string['setting:retentiondays'] = 'Retention (days)';
$string['setting:retentiondays_desc'] = 'How long a finished decision and its grading log are kept before being purged.';
$string['setting:tiebreak'] = 'Tie-break rule';
$string['setting:tiebreak_desc'] = 'When more than one teacher qualifies to grade a student, which one is chosen.';
$string['setting:tiebreak_last_course_access'] = 'The one who accessed the course most recently';
$string['setting:tiebreak_lowest_userid'] = 'The one with the lowest user ID';
$string['settings:generaltab'] = 'General';
$string['settings:modulestab'] = 'Autogradable activities';
$string['settings:retentiontab'] = 'Retention';
