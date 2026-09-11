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

namespace local_autograder\form;

use local_autograder\event\config_created;
use local_autograder\event\config_updated;
use local_autograder\local\config_repository;
use local_autograder\local\eligibility;

/**
 * The autograder section on an activity's own settings form.
 *
 * Only offered on an **existing** activity: at add-time there is no grade
 * item yet to read `grademethod` from (the form's own grade fields have not
 * been saved anywhere), so `add_elements()` is a no-op until the module has
 * been created once. Reading the submitted grade type straight off the
 * add-form to lift this restriction is a known follow-up
 * (docs-refactor/autograder-local/tasks.md, Fase 1).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class autograder_section {
    /**
     * Adds the section to a module's settings form, if this module qualifies.
     *
     * @param \moodleform_mod $formwrapper
     * @param \MoodleQuickForm $mform
     */
    public static function add_elements(\moodleform_mod $formwrapper, \MoodleQuickForm $mform): void {
        $current = $formwrapper->get_current();
        $modname = $current->modulename ?? null;
        $cmid = $current->coursemodule ?? 0;

        if (!$modname || !eligibility::is_module_type_enabled($modname) || !$cmid) {
            return;
        }

        $context = \context_module::instance($cmid);

        if (!eligibility::can_configure($context)) {
            return;
        }

        $cm = get_coursemodule_from_id($modname, $cmid, 0, false, MUST_EXIST);
        $grademethod = eligibility::grademethod_for($cm);

        if ($grademethod === null) {
            return;
        }

        $config = config_repository::get_for_cm($cmid);

        $mform->addElement('header', 'autogradersection', get_string('form:heading', 'local_autograder'));

        $mform->addElement('advcheckbox', 'autograder_enabled', get_string('form:enabled', 'local_autograder'));
        $mform->addHelpButton('autograder_enabled', 'form:enabled', 'local_autograder');
        $mform->setDefault('autograder_enabled', $config ? $config->enabled : 0);

        self::add_grade_elements($mform, $grademethod, $cm, $config);
        self::add_delay_elements($mform, $config);

        $mform->disabledIf('autograder_days', 'autograder_enabled');
        $mform->disabledIf('autograder_hours', 'autograder_enabled');
        $mform->disabledIf('autograder_minutes', 'autograder_enabled');
    }

    /**
     * The grade-to-assign field, shaped by `$grademethod`.
     *
     * Rubric/guide are deliberately not built here yet — they need the
     * activity's grading-form definition rendered as a real grading panel
     * (Fase 4), not a single input. Until then a rubric/guide activity simply
     * offers no autograder section (`eligibility::grademethod_for()` still
     * reports it, so this only affects what the form shows, not eligibility
     * elsewhere).
     *
     * @param \MoodleQuickForm $mform
     * @param string $grademethod
     * @param \stdClass $cm
     * @param \stdClass|false $config
     */
    private static function add_grade_elements(
        \MoodleQuickForm $mform,
        string $grademethod,
        \stdClass $cm,
        $config,
    ): void {
        if ($grademethod === 'scale') {
            $scaleitems = self::scale_items($cm);

            $mform->addElement(
                'select',
                'autograder_grade',
                get_string('form:grade', 'local_autograder'),
                $scaleitems,
            );
            $mform->setDefault('autograder_grade', $config ? (int) $config->gradevalue : array_key_first($scaleitems));
        } else {
            $mform->addElement('text', 'autograder_grade', get_string('form:grade', 'local_autograder'));
            $mform->setType('autograder_grade', PARAM_FLOAT);
            $mform->setDefault(
                'autograder_grade',
                $config ? $config->gradevalue : get_config('local_autograder', 'default_grade'),
            );
            $mform->addRule('autograder_grade', get_string('form:error_numeric', 'local_autograder'), 'numeric', null, 'client');
        }

        $mform->disabledIf('autograder_grade', 'autograder_enabled');
    }

    /**
     * The days/hours/minutes fields that compose `delayseconds`.
     *
     * @param \MoodleQuickForm $mform
     * @param \stdClass|false $config
     */
    private static function add_delay_elements(\MoodleQuickForm $mform, $config): void {
        [$days, $hours, $minutes] = $config
            ? self::seconds_to_parts((int) $config->delayseconds)
            : [
                (int) get_config('local_autograder', 'default_days'),
                (int) get_config('local_autograder', 'default_hours'),
                (int) get_config('local_autograder', 'default_minutes'),
            ];

        $fields = [
            'autograder_days' => [$days, 'form:days_to_complete'],
            'autograder_hours' => [$hours, 'form:hours_to_complete'],
            'autograder_minutes' => [$minutes, 'form:minutes_to_complete'],
        ];

        foreach ($fields as $name => [$default, $labelkey]) {
            $mform->addElement('text', $name, get_string($labelkey, 'local_autograder'));
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, $default);
            $mform->addHelpButton($name, 'form:time_to_complete', 'local_autograder');
            $mform->addRule($name, null, 'numeric', null, 'client');
        }
    }

    /**
     * Validates the section's own fields.
     *
     * @param array $data
     * @return array<string, string> Field name => error message.
     */
    public static function validate(array $data): array {
        $errors = [];

        if (empty($data['autograder_enabled'])) {
            return $errors;
        }

        if (empty($data['completion']) || (int) $data['completion'] === COMPLETION_TRACKING_NONE) {
            $errors['autograder_enabled'] = get_string('form:error_completion_tracking', 'local_autograder');
        }

        foreach (['autograder_days', 'autograder_hours', 'autograder_minutes'] as $name) {
            if (isset($data[$name]) && (int) $data[$name] < 0) {
                $errors[$name] = get_string('form:error_negative_time', 'local_autograder');
            }
        }

        return $errors;
    }

    /**
     * Saves the section's submission, firing the matching event.
     *
     * @param \stdClass $data The whole module form submission.
     */
    public static function save(\stdClass $data): void {
        $modname = $data->modulename ?? null;
        $cmid = (int) ($data->coursemodule ?? 0);

        if (!$modname || !eligibility::is_module_type_enabled($modname) || !$cmid) {
            return;
        }

        $cm = get_coursemodule_from_id($modname, $cmid, 0, false, MUST_EXIST);
        $grademethod = eligibility::grademethod_for($cm);

        if ($grademethod === null || $grademethod === 'rubric' || $grademethod === 'guide') {
            // See add_grade_elements(): the section is not offered for these
            // yet, so there is nothing of ours in $data to save.
            return;
        }

        $existing = config_repository::get_for_cm($cmid);
        $delayseconds = self::parts_to_seconds(
            (int) ($data->autograder_days ?? 0),
            (int) ($data->autograder_hours ?? 0),
            (int) ($data->autograder_minutes ?? 0),
        );

        $config = config_repository::upsert_for_cm(
            $cmid,
            (int) $data->course,
            !empty($data->autograder_enabled),
            $grademethod,
            isset($data->autograder_grade) ? (float) $data->autograder_grade : null,
            null,
            $delayseconds,
            (int) $data->userid ?? 0,
        );

        $context = \context_module::instance($cmid);
        $eventclass = $existing ? config_updated::class : config_created::class;
        $eventclass::create([
            'objectid' => $config->id,
            'context' => $context,
            'other' => [
                'grademethod' => $config->grademethod,
                'gradevalue' => $config->gradevalue,
                'delayseconds' => $config->delayseconds,
                'enabled' => (bool) $config->enabled,
            ],
        ])->trigger();
    }

    /**
     * A scale's items, id => text, as a select would need them.
     *
     * @param \stdClass $cm
     * @return array<int, string>
     */
    private static function scale_items(\stdClass $cm): array {
        global $DB;

        $gradeitem = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
            'courseid' => $cm->course,
        ]);

        if (!$gradeitem || !$gradeitem->scaleid) {
            return [];
        }

        $scale = $DB->get_record('scale', ['id' => $gradeitem->scaleid], '*', MUST_EXIST);
        $items = [];

        foreach (explode(',', $scale->scale) as $index => $label) {
            $items[$index + 1] = trim($label);
        }

        return $items;
    }

    /**
     * Splits a delay in seconds into days, hours and minutes.
     *
     * @param int $seconds
     * @return array{0: int, 1: int, 2: int} Days, hours, minutes.
     */
    private static function seconds_to_parts(int $seconds): array {
        $days = intdiv($seconds, DAYSECS);
        $seconds %= DAYSECS;
        $hours = intdiv($seconds, HOURSECS);
        $seconds %= HOURSECS;
        $minutes = intdiv($seconds, MINSECS);

        return [$days, $hours, $minutes];
    }

    /**
     * Composes days, hours and minutes back into a delay in seconds.
     *
     * @param int $days
     * @param int $hours
     * @param int $minutes
     * @return int
     */
    private static function parts_to_seconds(int $days, int $hours, int $minutes): int {
        return max(0, $days) * DAYSECS + max(0, $hours) * HOURSECS + max(0, $minutes) * MINSECS;
    }
}
