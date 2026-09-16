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
 * Library callbacks Moodle calls into directly (as opposed to `db/events.php`
 * observers or `db/hooks.php` hook callbacks).
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_autograder\form\autograder_section;

/**
 * Adds the autograder section to a supported activity's settings form.
 *
 * @param moodleform_mod $formwrapper
 * @param MoodleQuickForm $mform
 */
function local_autograder_coursemodule_standard_elements($formwrapper, $mform) {
    autograder_section::add_elements($formwrapper, $mform);
}

/**
 * Validates the autograder section of a submitted activity settings form.
 *
 * Moodle's own dispatcher (`moodleform_mod::plugin_extend_coursemodule_validation()`)
 * calls this as `$pluginfunction($this, $data)` — the form wrapper first, the
 * submitted data second. There is no `$files` parameter here at all, unlike
 * `moodleform::validation()` itself.
 *
 * @param moodleform_mod $formwrapper
 * @param array $data
 * @return array<string, string> Field name => error message.
 */
function local_autograder_coursemodule_validation($formwrapper, $data) {
    return autograder_section::validate($data);
}

/**
 * Saves the autograder section once the activity itself has been saved.
 *
 * @param stdClass $data
 * @return stdClass The same data, unmodified — Moodle expects it back.
 */
function local_autograder_coursemodule_edit_post_actions($data) {
    autograder_section::save($data);

    return $data;
}

/**
 * Adds the "do not grade in my name" preference to a user's Preferences page.
 *
 * @param navigation_node $parentnode The "Preferences" node being built.
 * @param stdClass $user The user whose preferences these are.
 * @param context $usercontext
 * @param stdClass $course
 * @param context $coursecontext
 */
function local_autograder_extend_navigation_user_settings($parentnode, $user, $usercontext, $course, $coursecontext) {
    if (!get_config('local_autograder', 'allowoptout')) {
        // The site has not offered this to its users.
        return;
    }

    // Shown to everyone the site offers it to. Whether this particular user
    // could ever be chosen depends on `moodle/grade:edit` in some course of
    // theirs, and there is no single context here that answers that cheaply.
    // Offering the page to somebody it never applies to is a harmless no-op,
    // not a wrong answer.
    $parentnode->add(
        get_string('preference:heading', 'local_autograder'),
        new moodle_url('/local/autograder/optout.php', ['userid' => $user->id]),
        navigation_node::TYPE_SETTING,
    );
}

/**
 * Declares this plugin's own user preference.
 *
 * @return array
 */
function local_autograder_user_preferences() {
    return [
        'local_autograder_optout' => [
            'type' => PARAM_BOOL,
            'null' => NULL_NOT_ALLOWED,
            'default' => false,
            'permissioncallback' => static function (int $userid): bool {
                global $USER;

                return $userid === (int) $USER->id || has_capability(
                    'moodle/user:editprofile',
                    \context_user::instance($userid),
                );
            },
        ],
    ];
}
