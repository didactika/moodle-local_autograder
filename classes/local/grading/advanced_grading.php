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

namespace local_autograder\local\grading;

use local_autograder\local\config\eligibility;

/**
 * Reads a rubric or marking guide definition, and shapes what autograder
 * stores for it.
 *
 * The filling autograder saves is the very structure
 * `gradingform_rubric_instance::update()` and its guide counterpart expect —
 * `['criteria' => [criterionid => [...]]]` — so nothing has to be translated
 * at grading time; it is handed straight to `submit_and_get_grade()` and
 * Moodle works the resulting number out exactly as it would from a teacher
 * filling the panel in by hand.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class advanced_grading {
    /**
     * The grading controller for a course module's advanced-grading area, or
     * null when it has none or none is active.
     *
     * @param \cm_info|\stdClass $cm A course module record with `modname` and `id`.
     * @return \gradingform_controller|null
     */
    public static function controller(\cm_info|\stdClass $cm): ?\gradingform_controller {
        global $CFG;

        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $area = eligibility::advanced_grading_area($cm->modname);

        if ($area === null) {
            return null;
        }

        $manager = get_grading_manager(\context_module::instance($cm->id), $area['component'], $area['area']);
        $controller = $manager->get_active_controller();

        return $controller ?: null;
    }

    /**
     * Whether the teacher has actually defined the rubric or guide yet.
     *
     * Choosing "Rubric" in an activity's settings does not create one — the
     * definition is written afterwards, on Moodle's own advanced grading
     * page. Until that is done and marked ready there is nothing for
     * autograder to be configured against.
     *
     * @param \cm_info|\stdClass $cm
     * @return bool
     */
    public static function is_defined(\cm_info|\stdClass $cm): bool {
        $controller = self::controller($cm);

        return $controller !== null && $controller->is_form_defined() && $controller->is_form_available();
    }

    /**
     * Where Moodle's own page for defining this activity's rubric or guide
     * lives, so the teacher can be pointed at it.
     *
     * @param \cm_info|\stdClass $cm
     * @return \moodle_url|null
     */
    public static function definition_url(\cm_info|\stdClass $cm): ?\moodle_url {
        $area = eligibility::advanced_grading_area($cm->modname);

        if ($area === null) {
            return null;
        }

        return new \moodle_url('/grade/grading/manage.php', [
            'contextid' => \context_module::instance($cm->id)->id,
            'component' => $area['component'],
            'area' => $area['area'],
        ]);
    }

    /**
     * A grading instance to hand to core's own `grading` form element, so the
     * teacher fills the real rubric or marking guide rather than a stand-in.
     *
     * Deliberately **not** persisted. Core's `get_or_create_instance()` writes
     * a row to `grading_instances` for a real act of grading a real student;
     * here nobody is being graded, the answer is stored in this plugin's own
     * configuration, and a row per page view would be litter. The renderers
     * only ever read the definition off the controller and the value off the
     * form element, so an unsaved instance draws exactly the same form.
     *
     * @param \cm_info|\stdClass $cm
     * @return \gradingform_instance|null Null when there is no definition yet.
     */
    public static function template_instance(\cm_info|\stdClass $cm): ?\gradingform_instance {
        global $USER;

        $controller = self::controller($cm);

        if ($controller === null || !$controller->is_form_defined()) {
            return null;
        }

        $method = eligibility::grademethod_for($cm);
        $class = 'gradingform_' . $method . '_instance';

        if (($method !== 'rubric' && $method !== 'guide') || !class_exists($class)) {
            return null;
        }

        return new $class($controller, (object) [
            'id' => null,
            'definitionid' => $controller->get_definition()->id,
            'raterid' => (int) $USER->id,
            'itemid' => null,
            'status' => \gradingform_instance::INSTANCE_STATUS_INCOMPLETE,
            'feedback' => null,
            'feedbackformat' => FORMAT_MOODLE,
            'rawgrade' => null,
            'timemodified' => time(),
        ]);
    }

    /**
     * The criteria of this activity's rubric or guide, normalised so the form
     * does not care which of the two it is drawing.
     *
     * Each entry is `['description' => string, 'levels' => [levelid =>
     * ['definition' => string, 'score' => float]], 'maxscore' => float|null]`:
     * a rubric fills `levels`, a guide fills `maxscore`.
     *
     * @param \cm_info|\stdClass $cm
     * @return array<int, array>
     */
    public static function criteria(\cm_info|\stdClass $cm): array {
        $controller = self::controller($cm);

        if ($controller === null || !$controller->is_form_defined()) {
            return [];
        }

        $definition = $controller->get_definition();
        // The controller's own get_method_name() is protected; eligibility
        // already resolves this through the grading manager.
        $method = eligibility::grademethod_for($cm);

        if ($method === 'rubric') {
            return self::rubric_criteria($definition);
        }

        if ($method === 'guide') {
            return self::guide_criteria($definition);
        }

        return [];
    }

    /**
     * A rubric's criteria, each with the levels it offers.
     *
     * @param \stdClass $definition
     * @return array
     */
    private static function rubric_criteria(\stdClass $definition): array {
        $criteria = [];

        foreach ($definition->rubric_criteria ?? [] as $criterionid => $criterion) {
            $levels = [];

            foreach ($criterion['levels'] ?? [] as $levelid => $level) {
                $levels[(int) $levelid] = [
                    'definition' => (string) ($level['definition'] ?? ''),
                    'score' => (float) ($level['score'] ?? 0),
                ];
            }

            $criteria[(int) $criterionid] = [
                'description' => (string) ($criterion['description'] ?? ''),
                'levels' => $levels,
                'maxscore' => null,
            ];
        }

        return $criteria;
    }

    /**
     * A marking guide's criteria, each scored out of its own maximum.
     *
     * @param \stdClass $definition
     * @return array
     */
    private static function guide_criteria(\stdClass $definition): array {
        $criteria = [];

        foreach ($definition->guide_criteria ?? [] as $criterionid => $criterion) {
            $criteria[(int) $criterionid] = [
                'description' => (string) ($criterion['shortname'] ?? $criterion['description'] ?? ''),
                'levels' => [],
                'maxscore' => (float) ($criterion['maxscore'] ?? 0),
            ];
        }

        return $criteria;
    }

    /**
     * The stored filling, or an empty structure when there is none.
     *
     * @param string|null $json The `advancedgrading` column.
     * @return array<int, array<string, mixed>> Keyed by criterion id.
     */
    public static function decode(?string $json): array {
        if (empty($json)) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) && isset($decoded['criteria']) && is_array($decoded['criteria'])
            ? $decoded['criteria']
            : [];
    }

    /**
     * Wraps a per-criterion filling for storage, in the shape the grading
     * form instance will later be handed verbatim.
     *
     * @param array $criteria Each criterion's filling, by criterion id.
     * @return string|null Null when there is nothing to store.
     */
    public static function encode(array $criteria): ?string {
        if (empty($criteria)) {
            return null;
        }

        return json_encode(['criteria' => $criteria]);
    }

    /**
     * Whether a stored filling still matches the definition — every criterion
     * answered, and every answer one the definition actually offers.
     *
     * A rubric edited after autograder was configured for it can leave a
     * filling pointing at a level that no longer exists, which would grade
     * wrongly or not at all.
     *
     * @param \cm_info|\stdClass $cm
     * @param string|null $json
     * @return bool
     */
    public static function filling_is_current(\cm_info|\stdClass $cm, ?string $json): bool {
        $criteria = self::criteria($cm);
        $filling = self::decode($json);

        if (empty($criteria) || empty($filling)) {
            return false;
        }

        foreach ($criteria as $criterionid => $criterion) {
            if (!isset($filling[$criterionid])) {
                return false;
            }

            $answer = $filling[$criterionid];

            if (!empty($criterion['levels'])) {
                if (!isset($answer['levelid']) || !isset($criterion['levels'][(int) $answer['levelid']])) {
                    return false;
                }

                continue;
            }

            if (!isset($answer['score']) || $answer['score'] > $criterion['maxscore']) {
                return false;
            }
        }

        return true;
    }
}
