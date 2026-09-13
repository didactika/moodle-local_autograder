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
 * Site-wide "which activity types can have autograder" page.
 *
 * Deliberately shaped like core's own "Manage activities"
 * (`admin/modules.php`): one row per gradeable activity type, name and a
 * toggle switch, nothing else — no version, no activity count, no settings
 * link, because none of those mean anything for this page's one job.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_autograder\local\eligibility;

admin_externalpage_setup('local_autograder_modules_page');

$modname = optional_param('modname', '', PARAM_PLUGIN);
$enabled = optional_param('enabled', null, PARAM_BOOL);

if ($modname !== '' && $enabled !== null && confirm_sesskey()) {
    eligibility::set_module_type_enabled($modname, (bool) $enabled);

    $notice = $enabled
        ? get_string('setting:modules_enable', 'local_autograder', get_string('modulename', $modname))
        : get_string('setting:modules_disable', 'local_autograder', get_string('modulename', $modname));

    redirect(
        new moodle_url('/local/autograder/modules.php'),
        $notice,
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

$enabledtypes = eligibility::enabled_module_types();

$table = new html_table();
$table->id = 'local-autograder-modules';
$table->attributes['class'] = 'admintable generaltable';
$table->head = [get_string('name'), get_string('setting:modules_enabled_column', 'local_autograder')];

foreach (eligibility::gradeable_module_types() as $type) {
    $ison = in_array($type, $enabledtypes, true);

    $toggleurl = new moodle_url('/local/autograder/modules.php', [
        'modname' => $type,
        'enabled' => $ison ? 0 : 1,
        'sesskey' => sesskey(),
    ]);

    $labelstr = $ison
        ? get_string('setting:modules_disable', 'local_autograder', get_string('modulename', $type))
        : get_string('setting:modules_enable', 'local_autograder', get_string('modulename', $type));

    $toggle = html_writer::start_tag('form', ['method' => 'post', 'action' => $toggleurl->out_omit_querystring()]);
    foreach ($toggleurl->params() as $name => $value) {
        $toggle .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => $name, 'value' => $value]);
    }
    $toggle .= $OUTPUT->render_from_template('core/toggle', [
        'id' => 'local-autograder-toggle-' . $type,
        'checked' => $ison,
        'dataattributes' => [['name' => 'submitonchange', 'value' => '1']],
        'title' => $labelstr,
        'label' => $labelstr,
        'labelclasses' => 'sr-only',
    ]);
    $toggle .= html_writer::end_tag('form');

    $table->data[] = [
        html_writer::span($OUTPUT->pix_icon('monologo', '', $type) . get_string('modulename', $type)),
        $toggle,
    ];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings:modulestab', 'local_autograder'));
echo html_writer::tag('p', get_string('modules:intro', 'local_autograder'));
echo html_writer::div(html_writer::table($table), 'local-autograder-table-scroll', [
    'tabindex' => '0',
    'role' => 'region',
    'aria-label' => get_string('settings:modulestab', 'local_autograder'),
]);
$PAGE->requires->js_amd_inline(
    "require(['jquery'], function($) {
        $('#local-autograder-modules input[data-submitonchange]').on('change', function() {
            $(this).closest('form').trigger('submit');
        });
    });"
);
echo $OUTPUT->footer();
