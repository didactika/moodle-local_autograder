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
 * A teacher's own "do not grade in my name" preference (plan.md §5.1).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_autograder\form\optout_form;

$userid = optional_param('userid', $USER->id, PARAM_INT);

require_login();

$user = $userid === (int) $USER->id ? $USER : core_user::get_user($userid, '*', MUST_EXIST);
$usercontext = context_user::instance($user->id);

$PAGE->set_context($usercontext);
$PAGE->set_url(new moodle_url('/local/autograder/optout.php', ['userid' => $userid]));
$PAGE->set_title(get_string('preference:optout', 'local_autograder'));

if ($userid !== (int) $USER->id) {
    require_capability('moodle/user:editprofile', $usercontext);
}

$form = new optout_form();
$form->set_data([
    'userid' => $userid,
    'optout' => (bool) get_user_preferences('local_autograder_optout', false, $userid),
]);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/user/preferences.php', ['userid' => $userid]));
} else if ($data = $form->get_data()) {
    set_user_preference('local_autograder_optout', !empty($data->optout), $userid);

    redirect(
        new moodle_url('/user/preferences.php', ['userid' => $userid]),
        get_string('preference:saved', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('preference:optout', 'local_autograder'));
$form->display();
echo $OUTPUT->footer();
