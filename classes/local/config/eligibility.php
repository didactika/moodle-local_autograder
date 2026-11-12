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

namespace local_autograder\local\config;

use local_autograder\local\decision\decision_repository;
use local_autograder\task\cancel_module;
use local_autograder\task\catch_up_module;

/**
 * Whether a course module may have autograder configured at all, and which of
 * the four grading methods (point, scale, rubric, guide) its grade item uses.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
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
     * What a site that has never visited the activity types page allows.
     *
     * @var string[]
     */
    private const DEFAULT_MODULE_TYPES = ['assign', 'forum', 'quiz'];

    /**
     * Which grade item of a module type is the one for the activity as a
     * whole, where that is not the first.
     *
     * Forum is the case that matters: its item 0 is the rating of individual
     * posts and item 1 is the grade for the forum itself. Autograder grades
     * the activity, so it must look at item 1 — reading item 0 would both
     * report the wrong grading method and, later, write over post ratings.
     *
     * @var array<string, int>
     */
    private const GRADE_ITEMNUMBERS = [
        'forum' => 1,
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
     * An unset setting and an empty one are different answers: the first is a
     * site that has never said, and gets the defaults, while the second is a
     * site that has switched every type off — which must mean none rather than
     * quietly turning three back on.
     *
     * @return string[]
     */
    public static function enabled_module_types(): array {
        $raw = get_config('local_autograder', 'enabled_modules');

        if ($raw === false) {
            $types = self::DEFAULT_MODULE_TYPES;
        } else {
            $types = array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
        }

        // A type that no longer meets the rule, such as one whose plugin was uninstalled, is not one the site allows.
        return array_values(array_filter($types, [self::class, 'is_module_type_supported']));
    }

    /**
     * Allows or disallows one module type from having autograder configured,
     * site-wide.
     *
     * @param string $modname
     * @param bool $enabled
     */
    public static function set_module_type_enabled(string $modname, bool $enabled): void {
        if ($enabled && !self::is_module_type_supported($modname)) {
            throw new \invalid_parameter_exception('Autograder cannot be used with ' . $modname . '.');
        }

        if (self::is_module_type_enabled($modname) === $enabled) {
            return;
        }

        $current = self::enabled_module_types();

        if ($enabled) {
            $current[] = $modname;
        } else {
            $current = array_diff($current, [$modname]);
        }

        set_config('enabled_modules', implode(',', array_unique($current)), 'local_autograder');

        self::apply_type_switch($modname, $enabled);
    }

    /**
     * Acts on a whole activity type being switched on or off.
     *
     * Hiding the settings on the activity form is not enough on its own:
     * without this, an activity already configured would go on being graded by
     * a type the site has withdrawn, and its teacher would have no form left
     * to switch it off with. So everything still waiting on that type is
     * called off.
     *
     * What no part of this touches is an activity's own configuration. It
     * stays saved as its teacher left it, and switching the type back on
     * catches those activities up rather than leaving them enabled and grading
     * nobody — or asking every teacher to find their activity and tick the box
     * a second time. A cancelled decision is not a final one, which is what
     * makes resuming possible; only a grade that was actually recorded stops a
     * student being looked at again.
     *
     * Queued per activity rather than done here, because one type covers every
     * course on the site and this runs while somebody waits for a page.
     *
     * @param string $modname
     * @param bool $enabled
     */
    public static function apply_type_switch(string $modname, bool $enabled): void {
        $cmids = $enabled
            ? config_repository::enabled_cmids_of_type($modname)
            : decision_repository::pending_cmids_of_type($modname);

        foreach ($cmids as $cmid) {
            if ($enabled) {
                $task = new catch_up_module();
                $task->set_custom_data((object) ['cmid' => $cmid]);
            } else {
                $task = new cancel_module();
                $task->set_custom_data((object) ['cmid' => $cmid, 'reason' => 'moduletypeoff']);
            }

            // Deduplicated: toggling a type twice in a row earns one pass.
            \core\task\manager::queue_adhoc_task($task, true);
        }
    }

    /**
     * Takes the types that do not meet the rule off the ones the site allows, and calls off what was waiting for them.
     *
     * The configuration of their activities stays saved, and so does every grade that was given.
     *
     * @return string[] The types that were taken off, in the order the setting listed them.
     */
    public static function withdraw_unsupported_types(): array {
        $raw = get_config('local_autograder', 'enabled_modules');

        if ($raw === false) {
            return [];
        }

        $listed = array_values(array_filter(array_map('trim', explode(',', (string) $raw))));
        $kept = array_values(array_filter($listed, [self::class, 'is_module_type_supported']));
        $dropped = array_values(array_diff($listed, $kept));

        if ($dropped !== []) {
            set_config('enabled_modules', implode(',', $kept), 'local_autograder');

            foreach ($dropped as $modname) {
                self::apply_type_switch($modname, false);
            }
        }

        return $dropped;
    }

    /**
     * Every installed module type that Autograder can be used with, whether or
     * not the site has switched it on: the ones for which
     * {@see self::is_module_type_supported()} is true.
     *
     * @return string[] Module names, e.g. ["assign", "forum", "lesson", "quiz", "workshop"].
     */
    public static function supported_module_types(): array {
        $supported = [];

        foreach (\core_component::get_plugin_list('mod') as $modname => $unused) {
            if (self::is_module_type_supported($modname)) {
                $supported[] = $modname;
            }
        }

        sort($supported);

        return $supported;
    }

    /**
     * Whether a module type takes the work of students and has a grade that a teacher gives.
     *
     * Moodle has no single flag for "students hand something in", so the rule reads what every activity plugin already
     * declares, which means a plugin of the site is offered with no change here as soon as it declares it. The type must
     * have a grade, and then either declare the purpose "assessment" or define the capability mod/<type>:grade, with
     * which a teacher grades the work of students.
     *
     * @param string $modname The module type, e.g. "assign".
     * @return bool
     */
    public static function is_module_type_supported(string $modname): bool {
        global $DB;

        if (!array_key_exists($modname, \core_component::get_plugin_list('mod'))) {
            return false;
        }

        if (!plugin_supports('mod', $modname, FEATURE_GRADE_HAS_GRADE, false)) {
            return false;
        }

        // Older releases of Moodle do not have the purposes of the activities.
        if (defined('FEATURE_MOD_PURPOSE') && defined('MOD_PURPOSE_ASSESSMENT')) {
            $purpose = plugin_supports('mod', $modname, FEATURE_MOD_PURPOSE, null);

            if ($purpose === MOD_PURPOSE_ASSESSMENT) {
                return true;
            }
        }

        return $DB->record_exists('capabilities', ['name' => 'mod/' . $modname . ':grade']);
    }

    /**
     * Which of the four grading methods a course module's grade item uses, or
     * null when none of them applies — the form must not let autograder be
     * enabled in that case (outcomes-only, "no grade", or an advanced-grading
     * method this plugin does not simulate).
     *
     * @param \cm_info|\stdClass $cm A course-module record (or `cm_info`) with
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
     * @param \cm_info|\stdClass $cm
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
     * What this activity is graded out of, for a point-graded activity.
     *
     * @param \cm_info|\stdClass $cm
     * @return float|null Null when the activity is not graded by points.
     */
    public static function maximum_grade($cm): ?float {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');

        $gradeitem = self::grade_item_for($cm);

        if (!$gradeitem || (int) $gradeitem->gradetype !== GRADE_TYPE_VALUE) {
            return null;
        }

        return (float) $gradeitem->grademax;
    }

    /**
     * Which grade item of this module type carries the activity's own grade.
     *
     * @param string $modname
     * @return int
     */
    public static function grade_itemnumber(string $modname): int {
        return self::GRADE_ITEMNUMBERS[$modname] ?? 0;
    }

    /**
     * Where this module type keeps its advanced-grading area, or null when it
     * has none.
     *
     * @param string $modname
     * @return array{component: string, area: string}|null
     */
    public static function advanced_grading_area(string $modname): ?array {
        return self::ADVANCED_GRADING_AREAS[$modname] ?? null;
    }

    /**
     * The grade_item behind a course module's activity grade.
     *
     * @param \cm_info|\stdClass $cm
     * @return \grade_item|null
     */
    private static function grade_item_for($cm): ?\grade_item {
        $item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
            'itemnumber' => self::grade_itemnumber($cm->modname),
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
