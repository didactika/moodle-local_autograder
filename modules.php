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
 * One row for each activity type that takes the work of students and has a grade that a teacher gives, with what Autograder reads
 * for it and a switch. The chips filter the rows by whether Autograder is on, and the numbers on them are the ones of the rows.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_autograder\local\config\eligibility;
use local_autograder\local\module\module_adapter;

admin_externalpage_setup('local_autograder_modules_page');

$modname = optional_param('modname', '', PARAM_PLUGIN);
$enabled = optional_param('enabled', null, PARAM_BOOL);

if ($modname !== '' && $enabled !== null && confirm_sesskey()) {
    eligibility::set_module_type_enabled($modname, (bool) $enabled);

    $notice = $enabled
        ? get_string('setting:modules_enabled', 'local_autograder', get_string('modulename', $modname))
        : get_string('setting:modules_disabled', 'local_autograder', get_string('modulename', $modname));

    redirect(
        new moodle_url('/local/autograder/modules.php'),
        $notice,
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

$enabledtypes = eligibility::enabled_module_types();
$action = (new moodle_url('/local/autograder/modules.php'))->out(false);
$types = [];
$oncount = 0;

foreach (eligibility::supported_module_types() as $type) {
    $ison = in_array($type, $enabledtypes, true);
    $oncount += $ison ? 1 : 0;
    $typename = get_string('modulename', $type);
    $label = get_string($ison ? 'setting:modules_disable' : 'setting:modules_enable', 'local_autograder', $typename);

    // What Autograder reads to know that a student took part: the hand-ins, posts or attempts of the types it knows, and the
    // completion of the activity for the others.
    $adapter = module_adapter::class_for($type);
    $reads = $adapter::knows_submissions() ? $type : 'completion';

    // Each toggle posts the state it switches to, so the page needs no
    // knowledge of what it was before: flipping it again simply asks for the
    // opposite.
    //
    // Rendered here rather than included as a partial of this page's
    // template, so that each Moodle version draws its own switch while this
    // plugin's template holds only markup of its own. From Moodle 5.0 the
    // core template puts an autocomplete attribute on the checkbox, which
    // the plugin checker's HTML validation rejects in any template that
    // includes it.
    $toggle = $OUTPUT->render_from_template('core/toggle', [
        'id' => 'local-autograder-toggle-' . $type,
        'checked' => $ison,
        'dataattributes' => [['name' => 'submitonchange', 'value' => '1']],
        'title' => $label,
        'label' => $label,
        'labelclasses' => 'sr-only',
    ]);

    $types[] = [
        'component' => 'mod_' . $type,
        'name' => $typename,
        'reads' => get_string('modules:reads_' . $reads, 'local_autograder'),
        'ison' => $ison,
        'action' => $action,
        'params' => [
            ['name' => 'modname', 'value' => $type],
            ['name' => 'enabled', 'value' => $ison ? 0 : 1],
            ['name' => 'sesskey', 'value' => sesskey()],
        ],
        'toggle' => $toggle,
    ];
}

$PAGE->requires->js_call_amd('local_autograder/modules', 'init', ['#local-autograder-modules']);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings:modulestab', 'local_autograder'));
echo $OUTPUT->render_from_template('local_autograder/modules', [
    'lead' => get_string('modules:lead', 'local_autograder'),
    'intro' => get_string('modules:intro', 'local_autograder'),
    'label' => get_string('settings:modulestab', 'local_autograder'),
    'filters' => [
        ['key' => 'all', 'label' => get_string('modules:filter_all', 'local_autograder'), 'count' => count($types), 'active' => true],
        ['key' => 'on', 'label' => get_string('modules:filter_on', 'local_autograder'), 'count' => $oncount, 'active' => false],
        ['key' => 'off', 'label' => get_string('modules:filter_off', 'local_autograder'), 'count' => count($types) - $oncount, 'active' => false],
    ],
    'search' => count($types) >= 8 ? get_string('modules:search', 'local_autograder') : '',
    'empty' => get_string('modules:empty', 'local_autograder'),
    'count' => get_string('modules:count', 'local_autograder', count($types)),
    'types' => $types,
]);
echo $OUTPUT->footer();
