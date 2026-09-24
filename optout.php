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
 * A teacher's own "do not grade in my name" preference.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/user/editlib.php');

use local_autograder\form\optout_form;

// A full login, not only a session. The preference setup below settles who may
// edit whose preference, but on the site course it asks no more than whether
// somebody is logged in — leaving out what require_login() also enforces: the
// site policy, a forced password change, an incomplete profile. Core's own
// user/contentbank.php pairs the two the same way.
require_login();

$userid = optional_param('userid', $USER->id, PARAM_INT);

// Set before the setup below, which builds the navigation from it and, for a
// deleted account, prints a whole page.
$PAGE->set_url(new moodle_url('/local/autograder/optout.php', ['userid' => $userid]));

// The same setup every one of core's own preference pages does, rather than a
// hand-rolled subset of it. Editing somebody else's preference is not only a
// matter of holding `moodle/user:editprofile` over them: guests cannot be
// edited at all, nor can deleted or remote accounts, an administrator may only
// be edited by another administrator, and editing your own still asks for
// `moodle/user:editownprofile`. None of that is this plugin's rule to invent.
[$user, $unusedcourse] = useredit_setup_preference_page($userid, SITEID);

// Core's setup above already enforces these; they are repeated here so that
// the page states the rule it depends on rather than leaving it implicit.
if ((int) $user->id === (int) $USER->id) {
    require_capability('moodle/user:editownprofile', context_system::instance());
} else {
    require_capability('moodle/user:editprofile', context_user::instance($user->id));
}

if (!get_config('local_autograder', 'allowoptout')) {
    // The site does not offer this to its users, so the page is not there to
    // be reached by typing its address either. Asked after the access checks
    // above, so that somebody who may not be here is told that first.
    throw new moodle_exception('preference:notoffered', 'local_autograder');
}

$PAGE->set_title(get_string('preference:heading', 'local_autograder'));

// The user the setup settled on, rather than the id that came off the URL:
// that one is only a request, and this one is the account it was allowed to be.
$form = new optout_form();
$form->set_data([
    'userid' => $user->id,
    'optout' => (bool) get_user_preferences('local_autograder_optout', false, $user->id),
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/user/preferences.php', ['userid' => $user->id]));
} else if ($data = $form->get_data()) {
    set_user_preference('local_autograder_optout', !empty($data->optout), $user->id);

    redirect(
        new moodle_url('/user/preferences.php', ['userid' => $user->id]),
        get_string('preference:saved', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('preference:heading', 'local_autograder'));
$form->display();
echo $OUTPUT->footer();
