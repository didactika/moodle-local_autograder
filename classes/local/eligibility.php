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

namespace local_autograder\local;

/**
 * Whether a course module may have autograder configured at all, and which of
 * the four grading methods (point, scale, rubric, guide) its grade item uses.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class eligibility {
    /**
     * The module types with a native advanced-grading area, and where it is —
     * the same {@see get_grading_manager()} lookup Moodle's own grading UI
     * uses for each. Quiz is deliberately absent: it has no gradingform area
     * of its own, only per-question marking inside an attempt.
     *
     * @var array<string, array{component: string, area: string}>
     */
    private const ADVANCED_GRADING_AREAS = [
        'assign' => ['component' => 'mod_assign', 'area' => 'submissions'],
        'forum' => ['component' => 'mod_forum', 'area' => 'forum'],
    ];

    /**
     * Whether a module type is one the site allows autograder on at all.
     *
     * @param string $modname The module type, e.g. "assign".
     * @return bool
     */
    public static function is_module_type_enabled(string $modname): bool {
        $enabled = self::enabled_module_types();

        return in_array($modname, $enabled, true);
    }

    /**
     * The module types this site currently allows, in the order they were
     * configured. Defaults to assign, forum and quiz on a fresh install.
     *
     * @return string[]
     */
    public static function enabled_module_types(): array {
        $raw = get_config('local_autograder', 'enabled_modules');

        if ($raw === false || $raw === '') {
            return ['assign', 'forum', 'quiz'];
        }

        return array_filter(array_map('trim', explode(',', $raw)));
    }

    /**
     * Allows or disallows one module type from having autograder configured,
     * site-wide.
     *
     * @param string $modname
     * @param bool $enabled
     */
    public static function set_module_type_enabled(string $modname, bool $enabled): void {
        $current = self::enabled_module_types();

        if ($enabled) {
            $current[] = $modname;
        } else {
            $current = array_diff($current, [$modname]);
        }

        set_config('enabled_modules', implode(',', array_unique($current)), 'local_autograder');
    }

    /**
     * Every installed module type that could ever be offered on the site
     * settings page — anything gradeable, whether or not it is one of the
     * types currently enabled.
     *
     * @return string[] Module names, e.g. ["assign", "forum", "quiz", ...].
     */
    public static function gradeable_module_types(): array {
        $gradeable = [];

        foreach (\core_component::get_plugin_list('mod') as $modname => $unused) {
            if (plugin_supports('mod', $modname, FEATURE_GRADE_HAS_GRADE, false)) {
                $gradeable[] = $modname;
            }
        }

        sort($gradeable);

        return $gradeable;
    }

    /**
     * Which of the four grading methods a course module's grade item uses, or
     * null when none of them applies — the form must not let autograder be
     * enabled in that case (outcomes-only, "no grade", or an advanced-grading
     * method this plugin does not simulate).
     *
     * @param \stdClass $cm A course-module record (or `cm_info`) with
     *                      `modname`, `instance` and `course`.
     * @return string|null One of "point", "scale", "rubric", "guide", or null.
     */
    public static function grademethod_for($cm): ?string {
        global $CFG;

        // Neither the grade subsystem (grade_item and friends) nor the
        // advanced-grading one is part of Moodle's normal bootstrap — unlike
        // most of the module edit form's own code path, a task or an event
        // observer has no guarantee either is already loaded.
        require_once($CFG->libdir . '/gradelib.php');
        require_once($CFG->dirroot . '/grade/grading/lib.php');

        $advanced = self::advanced_grademethod_for($cm);

        if ($advanced !== null) {
            return $advanced;
        }

        $gradeitem = self::grade_item_for($cm);

        if (!$gradeitem) {
            return null;
        }

        if ((int) $gradeitem->gradetype === GRADE_TYPE_VALUE) {
            return 'point';
        }

        if ((int) $gradeitem->gradetype === GRADE_TYPE_SCALE) {
            return 'scale';
        }

        return null;
    }

    /**
     * The active advanced-grading method for a module, if it has an area and
     * one is actually selected.
     *
     * @param \stdClass $cm
     * @return string|null "rubric", "guide", or null.
     */
    private static function advanced_grademethod_for($cm): ?string {
        if (!isset(self::ADVANCED_GRADING_AREAS[$cm->modname])) {
            return null;
        }

        // Its file is required by grademethod_for(), the only caller of this
        // private method.
        ['component' => $component, 'area' => $area] = self::ADVANCED_GRADING_AREAS[$cm->modname];
        $context = \context_module::instance($cm->id);
        $manager = get_grading_manager($context, $component, $area);
        $method = $manager->get_active_method();

        if ($method === 'rubric' || $method === 'guide') {
            return $method;
        }

        return null;
    }

    /**
     * The grade_item behind a course module's activity grade.
     *
     * @param \stdClass $cm
     * @return \grade_item|null
     */
    private static function grade_item_for($cm): ?\grade_item {
        $item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
            'courseid' => $cm->course,
        ]);

        return $item ?: null;
    }

    /**
     * Whether a user may turn autograder on or off for a course module.
     *
     * @param \context_module $context
     * @param \stdClass|int|null $user Defaults to the current user.
     * @return bool
     */
    public static function can_configure(\context_module $context, $user = null): bool {
        return has_capability('local/autograder:configure', $context, $user);
    }
}
