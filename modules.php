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
 * Site-wide "which activity types can have autograder" page (plan.md §9, D4).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_autograder\form\enabled_modules_form;

admin_externalpage_setup('local_autograder_modules_page');

$form = new enabled_modules_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'local_autograder_modules']));
} else if ($data = $form->get_data()) {
    set_config('enabled_modules', implode(',', enabled_modules_form::enabled_from_submission($data)), 'local_autograder');

    redirect(
        new moodle_url('/local/autograder/modules.php'),
        get_string('modules:saved', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings:modulestab', 'local_autograder'));
$form->display();
echo $OUTPUT->footer();
