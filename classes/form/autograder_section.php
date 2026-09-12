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
use local_autograder\event\config_deleted;
use local_autograder\event\config_updated;
use local_autograder\local\advanced_grading;
use local_autograder\local\config_repository;
use local_autograder\local\decision_repository;
use local_autograder\local\eligibility;
use local_autograder\task\cancel_module;
use local_autograder\task\catch_up_module;
use local_autograder\task\recalculate_module;

/**
 * The autograder section on an activity's own settings form.
 *
 * On a brand new activity there is no grade item yet to read `grademethod`
 * from — the form's own grade fields have not been saved anywhere — so the
 * section offers plain point grading, the common case; `save()` re-derives
 * the real method from the grade_item that exists by the time it runs (the
 * module has already been created). A teacher who picks scale, rubric or
 * guide for the very module they configure autograder on in that same step
 * gets a mismatched `gradevalue` — a known, narrow gap, no worse than what
 * v2 did (docs-refactor/autograder-local/tasks.md, Fase 1).
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class autograder_section {
    /**
     * @var int Past this many students already waiting, moving them is left to
     *          the queue rather than done while the form saves.
     */
    private const INLINE_RECALCULATION_LIMIT = 200;

    /**
     * Adds the section to a module's settings form, if this module qualifies.
     *
     * @param \moodleform_mod $formwrapper
     * @param \MoodleQuickForm $mform
     */
    public static function add_elements(\moodleform_mod $formwrapper, \MoodleQuickForm $mform): void {
        $current = $formwrapper->get_current();
        $modname = $current->modulename ?? null;
        $cmid = (int) ($current->coursemodule ?? 0);

        if (!$modname || !eligibility::is_module_type_enabled($modname)) {
            return;
        }

        if ($cmid) {
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
        } else {
            // A brand new activity: there is no grade_item yet to read a
            // method from — the grade fields on this very form have not been
            // saved anywhere. Offer plain point grading, the common case and
            // what v2 always did; save() re-derives the real method once the
            // module (and its grade_item) exist. A teacher who picks scale,
            // rubric or guide for a module they configure autograder on in
            // this same step gets a mismatched gradevalue — a known,
            // documented gap (tasks.md, Fase 1) no worse than v2's own.
            if (!has_capability('local/autograder:configure', \context_course::instance((int) $current->course))) {
                return;
            }

            $cm = null;
            $grademethod = 'point';
            $config = false;
        }

        $typefield = self::grade_type_field($modname);

        $mform->addElement('header', 'autogradersection', get_string('form:heading', 'local_autograder'));

        $mform->addElement('advcheckbox', 'autograder_enabled', get_string('form:enabled', 'local_autograder'));
        $mform->addHelpButton('autograder_enabled', 'form:enabled', 'local_autograder');
        $mform->setDefault('autograder_enabled', $config ? $config->enabled : 0);

        self::add_grade_elements($mform, $modname, $grademethod, $cm, $config, $typefield);
        self::add_delay_elements($mform, $config);

        foreach (['autograder_days', 'autograder_hours', 'autograder_minutes'] as $name) {
            $mform->disabledIf($name, 'autograder_enabled');
        }

        // Nothing here can mean anything for an activity that is not graded,
        // so the whole section greys out the moment "None" is picked — left
        // visible rather than hidden, so it is obvious *why* it cannot be used.
        foreach (self::element_names() as $name) {
            $mform->disabledIf($name, $typefield, 'eq', 'none');
        }
    }

    /**
     * The fixed elements this section adds.
     *
     * The scale pickers are not here: there is one per scale and they are
     * already hidden unless the activity is graded by that very scale, so
     * "None" never leaves one on screen.
     *
     * @return string[]
     */
    private static function element_names(): array {
        return [
            'autograder_enabled',
            'autograder_grade_point',
            'autograder_days',
            'autograder_hours',
            'autograder_minutes',
        ];
    }

    /**
     * The name of the grade-type selector on this module's own form.
     *
     * Modules do not agree on it: assign calls its grade field `grade`, forum
     * calls its `grade_forum`. `component_gradeitems` is what core itself asks
     * (see `moodleform_mod::standard_grading_coursemodule_elements()`), so ask
     * it rather than guessing.
     *
     * @param string $modname
     * @return string e.g. "grade[modgrade_type]".
     */
    private static function grade_type_field(string $modname): string {
        $gradefield = \core_grades\component_gradeitems::get_field_name_for_itemnumber(
            "mod_{$modname}",
            eligibility::grade_itemnumber($modname),
            'grade',
        );

        return "{$gradefield}[modgrade_type]";
    }

    /**
     * The grade-to-assign fields.
     *
     * Both a number (for point grading) and a scale picker are built, and
     * core's own `hideIf` machinery shows whichever matches the grade type
     * the teacher currently has selected — so flipping that selector swaps
     * the field live, without a save and without any JavaScript of ours.
     *
     * The scale picker lists the items of the scale in effect when the form
     * was built. A teacher who changes *which* scale in the same edit is
     * caught by {@see validate()} rather than silently given the wrong item.
     *
     * @param \MoodleQuickForm $mform
     * @param string $modname
     * @param string $grademethod What the activity is graded by right now.
     * @param \cm_info|\stdClass|null $cm Null for a brand new activity.
     * @param \stdClass|false $config
     * @param string $typefield The module's own grade-type selector.
     */
    private static function add_grade_elements(
        \MoodleQuickForm $mform,
        string $modname,
        string $grademethod,
        \cm_info|\stdClass|null $cm,
        $config,
        string $typefield,
    ): void {
        $mform->addElement('text', 'autograder_grade_point', get_string('form:grade', 'local_autograder'));
        $mform->setType('autograder_grade_point', PARAM_FLOAT);
        $mform->setDefault(
            'autograder_grade_point',
            ($config && $grademethod !== 'scale')
                ? $config->gradevalue
                : get_config('local_autograder', 'default_grade'),
        );
        $mform->addRule(
            'autograder_grade_point',
            get_string('form:error_numeric', 'local_autograder'),
            'numeric',
            null,
            'client',
        );
        $mform->hideIf('autograder_grade_point', $typefield, 'neq', 'point');
        $mform->disabledIf('autograder_grade_point', 'autograder_enabled');

        // Says the range out loud instead of leaving the teacher to find it
        // by being refused. It is what the activity is graded out of *now*;
        // one changed in this same save is caught by validate().
        $maximum = $cm ? eligibility::maximum_grade($cm) : null;

        if ($maximum !== null) {
            $mform->addElement(
                'static',
                'autograder_grade_point_range',
                '',
                get_string('form:grade_range', 'local_autograder', format_float($maximum, -1)),
            );
            $mform->hideIf('autograder_grade_point_range', $typefield, 'neq', 'point');
        }

        $scalenames = self::add_scale_elements($mform, $grademethod, $cm, $config, $typefield);

        // A rubric or marking guide replaces the plain grade entirely, so the
        // number and the scale pickers have nothing to say while one is
        // selected. Hiding them off the module's own "Grading method" selector
        // keeps that live: switch to Rubric and they go, switch back and they
        // return, with nothing to save in between.
        $advancedfield = self::advanced_method_field($modname);

        if ($advancedfield === null) {
            return;
        }

        foreach (array_merge(['autograder_grade_point'], $scalenames) as $name) {
            $mform->hideIf($name, $advancedfield, 'neq', '');
        }

        if ($mform->elementExists('autograder_grade_point_range')) {
            $mform->hideIf('autograder_grade_point_range', $advancedfield, 'neq', '');
        }

        if ($cm) {
            self::add_advanced_grading_notice($mform, $cm, $config);
            $mform->hideIf('autograder_advanced_notice', $advancedfield, 'eq', '');
        }
    }

    /**
     * For a rubric or marking guide, a line saying where to set which levels
     * autograder marks — and whether that has been done.
     *
     * It cannot be set here: choosing "Rubric" does not create a rubric, and
     * the definition is written afterwards on Moodle's own page, so there may
     * be no criteria to show yet at this point.
     *
     * @param \MoodleQuickForm $mform
     * @param \cm_info|\stdClass $cm
     * @param \stdClass|false $config
     */
    private static function add_advanced_grading_notice(
        \MoodleQuickForm $mform,
        \cm_info|\stdClass $cm,
        $config,
    ): void {
        if (!advanced_grading::is_defined($cm)) {
            $url = advanced_grading::definition_url($cm);
            $message = $url === null
                ? get_string('form:advanced_undefined', 'local_autograder')
                : get_string('form:advanced_define_first', 'local_autograder', $url->out());

            $mform->addElement('static', 'autograder_advanced_notice', '', $message);

            return;
        }

        $set = $config && advanced_grading::filling_is_current($cm, $config->advancedgrading);
        $url = new \moodle_url('/local/autograder/advanced.php', ['cmid' => $cm->id]);

        $mform->addElement(
            'static',
            'autograder_advanced_notice',
            '',
            get_string(
                $set ? 'form:advanced_set' : 'form:advanced_unset',
                'local_autograder',
                $url->out(),
            ),
        );
    }

    /**
     * One scale picker per scale the course offers, each shown only when the
     * teacher has that very scale selected on the activity itself.
     *
     * A single picker could not work: which items to list depends on which
     * scale is chosen in the module's own selector, and that is a live
     * client-side choice. Building one per scale and letting core's `hideIf`
     * reveal the right one keeps the whole thing live — pick "Scale", pick
     * which scale, and the matching list of items appears — with no
     * JavaScript of ours and nothing to save first.
     *
     * @param \MoodleQuickForm $mform
     * @param string $grademethod
     * @param \cm_info|\stdClass|null $cm
     * @param \stdClass|false $config
     * @param string $typefield
     * @return string[] The names of the pickers it built.
     */
    private static function add_scale_elements(
        \MoodleQuickForm $mform,
        string $grademethod,
        \cm_info|\stdClass|null $cm,
        $config,
        string $typefield,
    ): array {
        $names = [];

        $scalefield = str_replace('[modgrade_type]', '[modgrade_scale]', $typefield);
        $courseid = $cm->course ?? 0;
        $current = ($config && $grademethod === 'scale') ? (int) $config->gradevalue : null;
        $selectedscale = $cm ? self::current_scale_id($cm) : null;

        foreach (get_scales_menu($courseid) as $scaleid => $scalename) {
            $items = self::scale_items_of($scaleid);

            if (empty($items)) {
                continue;
            }

            $name = "autograder_grade_scale_{$scaleid}";
            $mform->addElement('select', $name, get_string('form:grade', 'local_autograder'), $items);

            if ($current !== null && $scaleid === $selectedscale) {
                $mform->setDefault($name, $current);
            }

            // Hidden unless the activity is graded by a scale *and* by this
            // one. Several hideIf conditions on an element are OR-ed, which is
            // exactly "hide when either does not match".
            $mform->hideIf($name, $typefield, 'neq', 'scale');
            $mform->hideIf($name, $scalefield, 'neq', (string) $scaleid);
            $mform->disabledIf($name, 'autograder_enabled');

            $names[] = $name;
        }

        return $names;
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
     * Completion tracking is deliberately *not* required. Where an activity
     * tracks completion autograder counts from the completion; where it does
     * not, it counts from the moment the student handed the activity in
     * (plan.md §4) — so demanding completion here would refuse perfectly
     * gradeable activities.
     *
     * @param array $data
     * @return array<string, string> Field name => error message.
     */
    public static function validate(array $data): array {
        $errors = [];

        if (empty($data['autograder_enabled'])) {
            return $errors;
        }

        $modname = $data['modulename'] ?? null;

        if ($modname === null) {
            return $errors;
        }

        $grade = self::submitted_grade($data, $modname);
        $advanced = self::submitted_advanced_method($data, $modname);

        if ($grade['type'] === 'none') {
            $errors['autograder_enabled'] = get_string('form:error_not_graded', 'local_autograder');
        } else if ($advanced !== null) {
            $errors += self::validate_advanced_grading($data, $advanced);
        } else if ($grade['type'] === 'point') {
            $errors += self::validate_point_grade($data, $grade['maximum']);
        } else if ($grade['type'] === 'scale') {
            $errors += self::validate_scale_grade($data, $grade['scaleid']);
        }

        $errors += self::validate_delay($data);

        return $errors;
    }

    /**
     * The advanced grading method this activity is about to use, if any.
     *
     * A rubric or marking guide takes the place of a plain grade entirely, so
     * what has to be checked is not a number but whether autograder has been
     * told what to mark.
     *
     * @param array $data The submitted form data.
     * @param string $modname
     * @return string|null "rubric", "guide", or null.
     */
    private static function submitted_advanced_method(array $data, string $modname): ?string {
        $field = self::advanced_method_field($modname);
        $method = $field === null ? null : ($data[$field] ?? null);

        return ($method === 'rubric' || $method === 'guide') ? $method : null;
    }

    /**
     * The name of the "Grading method" selector on this module's own form.
     *
     * Core names it after the gradable area — `advancedgradingmethod_submissions`
     * for an assignment — and its empty value means simple direct grading.
     *
     * @param string $modname
     * @return string|null Null for a module with no advanced grading at all.
     */
    private static function advanced_method_field(string $modname): ?string {
        $area = eligibility::advanced_grading_area($modname);

        return $area === null ? null : 'advancedgradingmethod_' . $area['area'];
    }

    /**
     * Refuses switching autograder on for a rubric or marking guide it has not
     * been told what to mark on.
     *
     * A configuration that says "enabled" and can never post a grade is worse
     * than one that is plainly off, and the message says where to go and fix
     * it. A rubric that does not exist yet is not held against the teacher —
     * they cannot have chosen levels on a definition they have not written.
     *
     * @param array $data The submitted form data.
     * @param string $method "rubric" or "guide".
     * @return array<string, string>
     */
    private static function validate_advanced_grading(array $data, string $method): array {
        $cmid = (int) ($data['coursemodule'] ?? 0);

        if ($cmid === 0) {
            return [];
        }

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm || !advanced_grading::is_defined($cm)) {
            return [];
        }

        $config = config_repository::get_for_cm($cmid);

        if ($config && advanced_grading::filling_is_current($cm, $config->advancedgrading)) {
            return [];
        }

        $url = new \moodle_url('/local/autograder/advanced.php', ['cmid' => $cmid]);

        return [
            'autograder_enabled' => get_string(
                'form:error_advanced_unset',
                'local_autograder',
                $url->out()
            ),
        ];
    }

    /**
     * Checks that an item of the scale in use has actually been picked.
     *
     * @param array $data The submitted form data.
     * @param int $scaleid
     * @return array<string, string>
     */
    private static function validate_scale_grade(array $data, int $scaleid): array {
        if ($scaleid <= 0) {
            return [];
        }

        $field = "autograder_grade_scale_{$scaleid}";
        $chosen = (int) ($data[$field] ?? 0);
        $items = self::scale_items_of($scaleid);

        if ($chosen <= 0) {
            return [$field => get_string('form:error_scale_unset', 'local_autograder')];
        }

        if (!isset($items[$chosen])) {
            // The teacher changed which scale the activity uses in this very
            // save, so the item they picked belongs to the old one.
            return [$field => get_string('form:error_scale_mismatch', 'local_autograder')];
        }

        return [];
    }

    /**
     * Checks the grade to assign against the maximum this very form is about
     * to save.
     *
     * The maximum is read from the module's own grade element rather than from
     * the stored grade item, because a teacher can lower the maximum and set
     * the autograder grade in the same save: checking the old maximum would
     * wave through a grade the activity will not accept a moment later.
     *
     * @param array $data The submitted form data.
     * @param float $maximum What the activity is about to be graded out of.
     * @return array<string, string>
     */
    private static function validate_point_grade(array $data, float $maximum): array {
        $field = 'autograder_grade_point';
        $raw = $data[$field] ?? null;

        if ($raw === null || trim((string) $raw) === '') {
            return [$field => get_string('form:error_grade_required', 'local_autograder')];
        }

        if (!is_numeric($raw)) {
            return [$field => get_string('form:error_numeric', 'local_autograder')];
        }

        $grade = (float) $raw;

        if ($grade < 0) {
            return [$field => get_string('form:error_grade_negative', 'local_autograder')];
        }

        if ($maximum > 0 && $grade > $maximum) {
            return [
                $field => get_string(
                    'form:error_grade_above_max',
                    'local_autograder',
                    format_float($maximum, -1)
                ),
            ];
        }

        return [];
    }

    /**
     * Checks the wait, which has to be a whole number of each unit and has to
     * add up to something.
     *
     * A delay of zero is a legitimate choice — grade the moment the student is
     * due — so what is refused is not zero but a negative or fractional one.
     *
     * @param array $data The submitted form data.
     * @return array<string, string>
     */
    private static function validate_delay(array $data): array {
        $errors = [];

        foreach (['autograder_days', 'autograder_hours', 'autograder_minutes'] as $name) {
            $raw = $data[$name] ?? 0;

            if (trim((string) $raw) === '') {
                continue;
            }

            if (!is_numeric($raw) || (float) $raw != (int) $raw) {
                $errors[$name] = get_string('form:error_whole_number', 'local_autograder');

                continue;
            }

            if ((int) $raw < 0) {
                $errors[$name] = get_string('form:error_negative_time', 'local_autograder');
            }
        }

        if (!empty($errors)) {
            return $errors;
        }

        if ((int) ($data['autograder_hours'] ?? 0) > 23) {
            $errors['autograder_hours'] = get_string('form:error_hours_range', 'local_autograder');
        }

        if ((int) ($data['autograder_minutes'] ?? 0) > 59) {
            $errors['autograder_minutes'] = get_string('form:error_minutes_range', 'local_autograder');
        }

        return $errors;
    }

    /**
     * How the activity is about to be graded, read off this very submission.
     *
     * The `modgrade` element does not submit its three controls separately:
     * `exportValue()` collapses them into **one number** under the grade field
     * — positive is the maximum for point grading, negative is minus the id of
     * the scale, zero is not graded at all. Core reads it exactly this way
     * itself when it checks the grade to pass
     * (`moodleform_mod::validation()`), and reading it any other way is how
     * this section silently validated nothing at all.
     *
     * @param array $data The submitted form data.
     * @param string $modname
     * @return array{type: string|null, maximum: float, scaleid: int} The type
     *         is null when this module's form carries no grade element.
     */
    private static function submitted_grade(array $data, string $modname): array {
        $none = ['type' => null, 'maximum' => 0.0, 'scaleid' => 0];
        $gradefield = \core_grades\component_gradeitems::get_field_name_for_itemnumber(
            "mod_{$modname}",
            eligibility::grade_itemnumber($modname),
            'grade',
        );
        $raw = $data[$gradefield] ?? null;

        if ($raw === null) {
            return $none;
        }

        // A module that hands the raw element group through rather than its
        // exported value is read on its own terms.
        if (is_array($raw)) {
            return [
                'type' => $raw['modgrade_type'] ?? 'none',
                'maximum' => (float) ($raw['modgrade_point'] ?? 0),
                'scaleid' => (int) ($raw['modgrade_scale'] ?? 0),
            ];
        }

        if (!is_numeric($raw)) {
            return $none;
        }

        $value = (float) $raw;

        if ($value > 0) {
            return ['type' => 'point', 'maximum' => $value, 'scaleid' => 0];
        }

        if ($value < 0) {
            return ['type' => 'scale', 'maximum' => 0.0, 'scaleid' => (int) -$value];
        }

        return ['type' => 'none', 'maximum' => 0.0, 'scaleid' => 0];
    }

    /**
     * Saves the section's submission, firing the matching event.
     *
     * @param \stdClass $data The whole module form submission.
     */
    public static function save(\stdClass $data): void {
        global $USER;

        $modname = $data->modulename ?? null;
        $cmid = (int) ($data->coursemodule ?? 0);

        if (!$modname || !eligibility::is_module_type_enabled($modname) || !$cmid) {
            return;
        }

        $cm = get_coursemodule_from_id($modname, $cmid, 0, false, MUST_EXIST);

        // Re-derived from the grade item that exists *now* — the teacher may
        // have changed the grading method in this very save, and for a brand
        // new activity there was nothing to read when the form was built.
        $grademethod = eligibility::grademethod_for($cm);

        if ($grademethod === null) {
            // The activity ended up not graded at all; drop any configuration
            // it used to have rather than leave one that can never fire.
            if (config_repository::get_for_cm($cmid)) {
                config_repository::delete_for_cm($cmid);
                config_deleted::create([
                    'objectid' => $cmid,
                    'context' => \context_module::instance($cmid),
                ])->trigger();

                self::apply_switch($cmid, false);
            }

            return;
        }

        $existing = config_repository::get_for_cm($cmid);
        $delayseconds = self::parts_to_seconds(
            (int) ($data->autograder_days ?? 0),
            (int) ($data->autograder_hours ?? 0),
            (int) ($data->autograder_minutes ?? 0),
        );

        $advanced = ($grademethod === 'rubric' || $grademethod === 'guide');

        $config = config_repository::upsert_for_cm(
            $cmid,
            (int) $data->course,
            !empty($data->autograder_enabled),
            $grademethod,
            $advanced ? null : self::submitted_grade_value($data, $grademethod, $cm),
            // The per-criterion filling is set on its own page, not here, so
            // carry whatever is already stored rather than wiping it.
            $advanced && $existing ? $existing->advancedgrading : null,
            $delayseconds,
            (int) $USER->id,
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

        self::apply_switch($cmid, (bool) $config->enabled);
    }

    /**
     * Acts on what the teacher just chose.
     *
     * Switched on, every student who already finished has to be caught up, or
     * turning it on would only ever reach the ones who finish afterwards.
     * Switched off, everything still queued has to be called off. Both run as
     * adhoc tasks so that saving the activity stays as quick as it was, however
     * large the class.
     *
     * @param int $cmid
     * @param bool $enabled
     */
    private static function apply_switch(int $cmid, bool $enabled): void {
        if ($enabled) {
            // The students who are already waiting are moved here and now: the
            // teacher has just changed the wait and is about to open the
            // report to see it. Leaving that to the queue means the report
            // shows the old dates until the next cron run, which reads as
            // "the setting did nothing".
            self::recalculate_now($cmid);

            $task = new catch_up_module();
            $task->set_custom_data((object) ['cmid' => $cmid]);
        } else {
            $task = new cancel_module();
            $task->set_custom_data((object) ['cmid' => $cmid, 'reason' => 'autograderoff']);
        }

        // Deduplicated: saving the same form twice earns one pass. The sweep
        // stays queued either way — finding the students who have no decision
        // yet means looking at every enrolled user, which is not something to
        // do while somebody waits for a form to save.
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Moves the decisions that are already waiting, unless there are so many
     * that doing it here would make saving the form slow.
     *
     * The cap is a judgement, not a rule of the domain: re-planning a student
     * is a handful of queries, so a normal class is imperceptible and a
     * cohort-sized one is not. Past the cap the queued sweep does it instead,
     * a minute later.
     *
     * @param int $cmid
     */
    private static function recalculate_now(int $cmid): void {
        $pending = count(decision_repository::pending_for_cm($cmid));

        if ($pending === 0 || $pending > self::INLINE_RECALCULATION_LIMIT) {
            return;
        }

        recalculate_module::run_for_cm($cmid);
    }

    /**
     * The grade to store, read from whichever field belongs to the method the
     * activity actually ended up with.
     *
     * @param \stdClass $data
     * @param string $grademethod
     * @return float|null
     */
    private static function submitted_grade_value(\stdClass $data, string $grademethod, \cm_info|\stdClass $cm): ?float {
        if ($grademethod !== 'scale') {
            return isset($data->autograder_grade_point) ? (float) $data->autograder_grade_point : null;
        }

        // Read the picker belonging to the scale the activity actually ended
        // up with — the form shows one per scale (see add_scale_elements()).
        $scaleid = self::current_scale_id($cm);
        $field = "autograder_grade_scale_{$scaleid}";

        return isset($data->{$field}) ? (float) $data->{$field} : null;
    }

    /**
     * A scale's items, id => text, as a select would need them.
     *
     * @param \cm_info|\stdClass $cm
     * @return array<int, string>
     */
    private static function scale_items(\cm_info|\stdClass $cm): array {
        $scaleid = self::current_scale_id($cm);

        return $scaleid === null ? [] : self::scale_items_of($scaleid);
    }

    /**
     * The scale this activity is graded by right now, or null when it is not
     * graded by one.
     *
     * @param \cm_info|\stdClass $cm
     * @return int|null
     */
    private static function current_scale_id(\cm_info|\stdClass $cm): ?int {
        $gradeitem = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
            'itemnumber' => eligibility::grade_itemnumber($cm->modname),
            'courseid' => $cm->course,
        ]);

        return ($gradeitem && $gradeitem->scaleid) ? (int) $gradeitem->scaleid : null;
    }

    /**
     * One scale's items, as a select needs them.
     *
     * Keyed from 1, because a scale grade in Moodle *is* the item's position.
     *
     * @param int $scaleid
     * @return array<int, string>
     */
    private static function scale_items_of(int $scaleid): array {
        global $DB;

        $scale = $DB->get_record('scale', ['id' => $scaleid]);

        if (!$scale) {
            return [];
        }

        $items = [];

        foreach (explode(',', $scale->scale) as $index => $label) {
            $items[$index + 1] = format_string(trim($label));
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
