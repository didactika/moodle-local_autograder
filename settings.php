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
 * The Teachers tab is where a site says whose name an automatic grade carries.
 * By default autograder works that out rather than being told: a role counts as
 * teaching if it grants one of the capabilities a grade is written through —
 * `moodle/grade:edit`, or a module's own `mod/<name>:grade` — and a course's
 * teachers are whoever holds such a role assigned in the course itself. The
 * capability is read off the role, once per site, and never checked per user;
 * see {@see \local_autograder\local\grading\teacher_source} for why, and
 * {@see \local_autograder\local\grading\grader_picker} for who is then left out
 * — administrators, guests, and opt-outs while the site offers the preference.
 *
 * `fallback_grader` is the last resort, for a student with no teacher left.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
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

    // Two tabs, because a site answers two separate questions here: what
    // autograder applies to and what it leaves behind (General), and who a
    // grade ends up signed by (Teachers). Activity types and data retention
    // were tabs of their own holding one control each, which made the reader
    // open three pages to read four settings.
    $general = new admin_settingpage('local_autograder_general', get_string('settings:generaltab', 'local_autograder'));

    // Both blocks on this tab are titled. An admin_setting_heading with no
    // title renders its description on its own, outside the label/control grid
    // every real setting sits in — which put the text hard against the left
    // margin with the settings below it indented, reading as a stray label
    // rather than a section of its own.
    $general->add(new admin_setting_heading(
        'local_autograder/grading_heading',
        get_string('setting:grading_heading', 'local_autograder'),
        '',
    ));

    $general->add(new admin_setting_configcheckbox(
        'local_autograder/notifystudent',
        get_string('setting:notifystudent', 'local_autograder'),
        get_string('setting:notifystudent_desc', 'local_autograder'),
        0,
    ));

    // Off unless a site asks for it, like every other message this plugin
    // can send. Switching it on starts the count from that moment, so the
    // first summary is not every failure the site has ever had.
    $notifyfailures = new admin_setting_configcheckbox(
        'local_autograder/notifyfailures',
        get_string('setting:notifyfailures', 'local_autograder'),
        get_string('setting:notifyfailures_desc', 'local_autograder'),
        0,
    );
    $notifyfailures->set_updatedcallback('\\local_autograder\\task\\notify_failures::setting_changed');
    $general->add($notifyfailures);

    // Digits only, rather than PARAM_INT, which accepts a minus sign. A
    // negative number of days is not a shorter retention, and the task reads
    // it as the same "keep everything" that 0 means — so it is refused at the
    // form rather than saved and quietly meaning something else than it says.
    $general->add(new admin_setting_configtext(
        'local_autograder/retentiondays',
        get_string('setting:retentiondays', 'local_autograder'),
        get_string('setting:retentiondays_desc', 'local_autograder'),
        '120',
        '/^\d+$/',
    ));

    // Last, because it is the one block on this tab that is not a setting: a
    // way out to the page that holds them, after everything that can actually
    // be changed here.
    $general->add(new admin_setting_heading(
        'local_autograder/modules_heading',
        get_string('settings:modulestab', 'local_autograder'),
        get_string('setting:modules_heading', 'local_autograder', [
            'url' => (new \moodle_url('/local/autograder/modules.php'))->out(),
        ]),
    ));

    $settings->add($general);

    // Who counts as a teacher is what decides whose name every grade carries,
    // so a site says it here — in one place, in this plugin's own settings.
    // Read out of somewhere else and a role could be added, be assigned on the
    // course, and still leave autograder thinking nobody taught it.
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
    // the failure this avoids: a site defines a third role that grades, leaves
    // it off the list, and every course it teaches reports that nobody can
    // grade it with nothing on screen to say why.
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
    // defining its own marking role almost always builds it on one, and the
    // alternative default — the two stock roles — is exactly what would leave
    // such a role out and the course with no teacher.
    $teachers->add(new admin_setting_configmultiselect(
        'local_autograder/teacher_roles',
        get_string('setting:teacher_roles', 'local_autograder'),
        get_string('setting:teacher_roles_desc', 'local_autograder'),
        $teacherarchetypes,
        $roleoptions,
    ));

    $teachers->add(new admin_setting_configselect(
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
    $teachers->add(new \local_autograder\local\config\fallback_grader_setting(
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
    $teachers->add(new admin_setting_configcheckbox(
        'local_autograder/allowoptout',
        get_string('setting:allowoptout', 'local_autograder'),
        get_string('setting:allowoptout_desc', 'local_autograder'),
        0,
    ));

    $settings->add($teachers);

    // The role list belongs to the hand-picked answer, so it is not on screen
    // at all while the roles are being worked out.
    //
    // Registered on the tabs page and after the tab is added, not on the tab
    // itself: theme_boost_admin_settingspage_tabs::add_tab() copies a tab's
    // settings up to the parent but not its dependencies, and admin/settings.php
    // reads them off the parent — so a hide_if left on the tab was simply
    // never handed to the browser, and the setting stayed visible in every mode.
    $settings->hide_if(
        'local_autograder/teacher_roles',
        'local_autograder/teacher_source_mode',
        'neq',
        \local_autograder\local\grading\teacher_source::MODE_CHOSEN_ROLES,
    );

    $ADMIN->add('localplugins', $settings);
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_autograder_modules_page',
        get_string('settings:modulestab', 'local_autograder'),
        new \moodle_url('/local/autograder/modules.php'),
        'local/autograder:manage',
        true,
    ));
}
