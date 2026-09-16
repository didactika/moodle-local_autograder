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
 * Who gets to grade is not configured here. It is whoever Moodle already says
 * may grade — `moodle/grade:edit` — minus anyone whose own
 * `local_autograder_optout` preference asks not to be chosen.
 * `fallback_grader` below is the one exception: the last resort when the
 * course itself has nobody eligible.
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

    $general->add(new admin_setting_configcheckbox(
        'local_autograder/notifystudent',
        get_string('setting:notifystudent', 'local_autograder'),
        get_string('setting:notifystudent_desc', 'local_autograder'),
        0,
    ));

    $settings->add($general);

    // Who counts as a teacher is what decides whose name every grade carries,
    // so a site says it here rather than autograder reading it out of another
    // plugin's configuration — where a corrector role could be added, appear
    // on the course, and still leave autograder thinking nobody taught it.
    $teachers = new admin_settingpage(
        'local_autograder_teachers',
        get_string('settings:teacherstab', 'local_autograder')
    );

    $teachers->add(new admin_setting_heading(
        'local_autograder/teachers_heading',
        '',
        get_string('setting:teachers_heading', 'local_autograder'),
    ));

    // Worked out rather than listed, by default. Naming the roles by hand is
    // what broke this: a site added a corrector role, every course it taught
    // reported that nobody could grade it, and nothing on screen said why.
    $teachers->add(new admin_setting_configselect(
        'local_autograder/teacher_source_mode',
        get_string('setting:teacher_source_mode', 'local_autograder'),
        get_string('setting:teacher_source_mode_desc', 'local_autograder'),
        \local_autograder\local\grading\teacher_source::MODE_GRADING_ROLES,
        [
            \local_autograder\local\grading\teacher_source::MODE_GRADING_ROLES =>
                get_string('setting:teacher_source_mode_grading', 'local_autograder'),
            \local_autograder\local\grading\teacher_source::MODE_CHOSEN_ROLES =>
                get_string('setting:teacher_source_mode_chosen', 'local_autograder'),
        ],
    ));

    $roleoptions = [];
    $teacherarchetypes = [];

    foreach (role_fix_names(get_all_roles(), null, ROLENAME_ORIGINAL) as $role) {
        $roleoptions[$role->shortname] = $role->localname . ' (' . $role->shortname . ')';

        if (in_array($role->archetype, ['editingteacher', 'teacher'], true)) {
            $teacherarchetypes[] = $role->shortname;
        }
    }

    // Every role built on a teacher archetype, ticked to begin with: a site
    // that names a corrector role almost always builds it on one, and the
    // alternative default — the two stock roles — is exactly what left such a
    // role out and the course with no teacher.
    $teachers->add(new admin_setting_configmultiselect(
        'local_autograder/teacher_roles',
        get_string('setting:teacher_roles', 'local_autograder'),
        get_string('setting:teacher_roles_desc', 'local_autograder'),
        $teacherarchetypes,
        $roleoptions,
    ));

    // The role list belongs to the hand-picked answer, so it is not on screen
    // at all while the roles are being worked out.
    $teachers->hide_if(
        'local_autograder/teacher_roles',
        'local_autograder/teacher_source_mode',
        'neq',
        \local_autograder\local\grading\teacher_source::MODE_CHOSEN_ROLES,
    );

    $settings->add($teachers);

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
