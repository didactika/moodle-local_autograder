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
 * Which level of each rubric criterion — or which score on each marking-guide
 * criterion — autograder marks for this activity.
 *
 * Separate from the activity's settings form on purpose: picking "Rubric"
 * there does not create a rubric, so at that moment there may be no criteria
 * to show. This page is reached once the definition exists.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_autograder\event\config_updated;
use local_autograder\form\advanced_grading_form;
use local_autograder\local\advanced_grading;
use local_autograder\local\config_repository;
use local_autograder\local\eligibility;

$cmid = required_param('cmid', PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($cmid);

require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('local/autograder:configure', $context);

$returnurl = new moodle_url('/course/modedit.php', ['update' => $cm->id]);

$PAGE->set_url(new moodle_url('/local/autograder/advanced.php', ['cmid' => $cm->id]));
$PAGE->set_context($context);
$PAGE->set_title(get_string('advanced:heading', 'local_autograder'));
$PAGE->set_heading($course->fullname);
$PAGE->set_secondary_active_tab('modulesettings');

$grademethod = eligibility::grademethod_for($cm);

if ($grademethod !== 'rubric' && $grademethod !== 'guide') {
    redirect(
        $returnurl,
        get_string('advanced:not_advanced', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_WARNING,
    );
}

if (!advanced_grading::is_defined($cm)) {
    $definitionurl = advanced_grading::definition_url($cm);

    redirect(
        $definitionurl ?? $returnurl,
        get_string('advanced:define_first', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_WARNING,
    );
}

$instance = advanced_grading::template_instance($cm);

if ($instance === null) {
    redirect(
        $returnurl,
        get_string('advanced:not_advanced', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_WARNING,
    );
}

$config = config_repository::get_for_cm($cm->id);
$filling = advanced_grading::decode($config ? $config->advancedgrading : null);

$form = new advanced_grading_form($PAGE->url, ['gradinginstance' => $instance]);
$form->set_data((object) [
    'cmid' => $cm->id,
    // Already the shape the grading element reads and submits, so what was
    // stored last time comes back marked on the form as it was left.
    'advancedgrading' => ['criteria' => $filling],
]);

if ($form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $form->get_data()) {
    $encoded = advanced_grading::encode((array) ($data->advancedgrading['criteria'] ?? []));

    $saved = config_repository::upsert_for_cm(
        $cm->id,
        (int) $course->id,
        $config ? (bool) $config->enabled : false,
        $grademethod,
        null,
        $encoded,
        $config ? (int) $config->delayseconds : 0,
        (int) $USER->id,
    );

    config_updated::create([
        'objectid' => $saved->id,
        'context' => $context,
        'other' => [
            'grademethod' => $saved->grademethod,
            'gradevalue' => null,
            'delayseconds' => $saved->delayseconds,
            'enabled' => (bool) $saved->enabled,
        ],
    ])->trigger();

    redirect(
        $returnurl,
        get_string('advanced:saved', 'local_autograder'),
        null,
        \core\output\notification::NOTIFY_SUCCESS,
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('advanced:heading', 'local_autograder'));
echo html_writer::tag('p', get_string('advanced:intro', 'local_autograder', format_string($cm->name)));

$definitionurl = advanced_grading::definition_url($cm);

if ($definitionurl !== null) {
    echo html_writer::tag(
        'p',
        get_string('advanced:edit_definition', 'local_autograder', $definitionurl->out()),
        ['class' => 'text-muted'],
    );
}

$form->display();
echo $OUTPUT->footer();
