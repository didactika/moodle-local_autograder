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
 * Admin settings for local_autograder.
 *
 * Who gets to grade is not configured here — see the
 * `local/autograder:gradeonbehalf` capability and the `local_autograder_optout`
 * user preference. `fallback_grader` below is the one exception:
 * the last resort when the course itself has nobody eligible.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    global $CFG;
    require_once($CFG->dirroot . '/theme/boost/classes/admin_settingspage_tabs.php');

    $settings = new theme_boost_admin_settingspage_tabs(
        'local_autograder_settings',
        get_string('pluginname', 'local_autograder'),
    );

    $general = new admin_settingpage('local_autograder_general', get_string('settings:generaltab', 'local_autograder'));

    $general->add(new admin_setting_configselect(
        'local_autograder/tiebreak',
        get_string('setting:tiebreak', 'local_autograder'),
        get_string('setting:tiebreak_desc', 'local_autograder'),
        'lowest_userid',
        [
            'lowest_userid' => get_string('setting:tiebreak_lowest_userid', 'local_autograder'),
            'last_course_access' => get_string('setting:tiebreak_last_course_access', 'local_autograder'),
        ],
    ));

    $general->add(new admin_setting_configtext(
        'local_autograder/default_grade',
        get_string('setting:default_grade', 'local_autograder'),
        get_string('setting:default_grade_desc', 'local_autograder'),
        '10',
        PARAM_FLOAT,
    ));

    $general->add(new admin_setting_configtext(
        'local_autograder/default_days',
        get_string('setting:default_days', 'local_autograder'),
        get_string('setting:default_time_desc', 'local_autograder'),
        '2',
        PARAM_INT,
    ));

    $general->add(new admin_setting_configtext(
        'local_autograder/default_hours',
        get_string('setting:default_hours', 'local_autograder'),
        '',
        '0',
        PARAM_INT,
    ));

    $general->add(new admin_setting_configtext(
        'local_autograder/default_minutes',
        get_string('setting:default_minutes', 'local_autograder'),
        '',
        '0',
        PARAM_INT,
    ));

    // Defaulted to user id 0, "no fallback grader": there is no sensible
    // non-zero user to pick on a site's behalf.
    $general->add(new \local_autograder\local\config\fallback_grader_setting(
        'local_autograder/fallback_grader',
        get_string('setting:fallback_grader', 'local_autograder'),
        get_string('setting:fallback_grader_desc', 'local_autograder'),
        0,
    ));

    // Off unless a site decides otherwise: letting teachers take themselves
    // out of the rota changes who gets graded and when, and on a site with few
    // eligible teachers it can leave an activity with nobody to grade as. A
    // site that wants to offer it can, and until then the preference is
    // neither shown nor honoured.
    $general->add(new admin_setting_configcheckbox(
        'local_autograder/allowoptout',
        get_string('setting:allowoptout', 'local_autograder'),
        get_string('setting:allowoptout_desc', 'local_autograder'),
        0,
    ));

    $settings->add($general);

    $modules = new admin_settingpage('local_autograder_modules', get_string('settings:modulestab', 'local_autograder'));
    $modules->add(new admin_setting_heading(
        'local_autograder/modules_heading',
        '',
        get_string('setting:modules_heading', 'local_autograder', [
            'url' => (new \moodle_url('/local/autograder/modules.php'))->out(),
        ]),
    ));
    $settings->add($modules);

    $retention = new admin_settingpage('local_autograder_retention', get_string('settings:retentiontab', 'local_autograder'));
    $retention->add(new admin_setting_configtext(
        'local_autograder/retentiondays',
        get_string('setting:retentiondays', 'local_autograder'),
        get_string('setting:retentiondays_desc', 'local_autograder'),
        '120',
        PARAM_INT,
    ));
    $settings->add($retention);

    $ADMIN->add('localplugins', $settings);
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_autograder_modules_page',
        get_string('settings:modulestab', 'local_autograder'),
        new \moodle_url('/local/autograder/modules.php'),
        'local/autograder:manage',
        true,
    ));
}
