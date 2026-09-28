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

/**
 * CRUD over `local_autograder_config` — one row per course module with
 * autograder configured.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class config_repository {
    /**
     * The autograder configuration of one course module, if it has any.
     *
     * @param int $cmid
     * @return \stdClass|false
     */
    public static function get_for_cm(int $cmid) {
        global $DB;

        return $DB->get_record('local_autograder_config', ['cmid' => $cmid]);
    }

    /**
     * Every autograder configuration enabled for a course.
     *
     * @param int $courseid
     * @return \stdClass[]
     */
    public static function enabled_for_course(int $courseid): array {
        global $DB;

        return $DB->get_records('local_autograder_config', ['courseid' => $courseid, 'enabled' => 1]);
    }

    /**
     * Every course module with autograder currently enabled, across the
     * whole site — what `reconcile_pending` and bulk maintenance walk.
     *
     * @return \stdClass[]
     */
    public static function all_enabled(): array {
        global $DB;

        return $DB->get_records('local_autograder_config', ['enabled' => 1]);
    }

    /**
     * The activities of one module type autograder is switched on for.
     *
     * The type is a fact about the course module rather than about this
     * plugin's own row, so it is joined for rather than stored twice.
     *
     * @param string $modname
     * @return int[] Their course module ids.
     */
    public static function enabled_cmids_of_type(string $modname): array {
        global $DB;

        return array_map('intval', $DB->get_fieldset_sql(
            "SELECT c.cmid
               FROM {local_autograder_config} c
               JOIN {course_modules} cm ON cm.id = c.cmid
               JOIN {modules} m ON m.id = cm.module
              WHERE c.enabled = 1 AND m.name = :modname
           ORDER BY c.cmid",
            ['modname' => $modname]
        ));
    }

    /**
     * Creates or updates the configuration of one course module.
     *
     * @param int $cmid
     * @param int $courseid
     * @param bool $enabled
     * @param string $grademethod One of point, scale, rubric, guide.
     * @param float|null $gradevalue Set for point/scale, null otherwise.
     * @param string|null $advancedgrading JSON, set for rubric/guide, null otherwise.
     * @param int $delayseconds
     * @param int $userid The user saving the configuration.
     * @return \stdClass The row as saved, with `id` set.
     */
    public static function upsert_for_cm(
        int $cmid,
        int $courseid,
        bool $enabled,
        string $grademethod,
        ?float $gradevalue,
        ?string $advancedgrading,
        int $delayseconds,
        int $userid,
    ): \stdClass {
        global $DB;

        $now = time();
        $existing = self::get_for_cm($cmid);

        $record = (object) [
            'cmid' => $cmid,
            'courseid' => $courseid,
            'enabled' => $enabled ? 1 : 0,
            'grademethod' => $grademethod,
            'gradevalue' => $gradevalue,
            'advancedgrading' => $advancedgrading,
            'delayseconds' => $delayseconds,
            'usermodified' => $userid,
            'timemodified' => $now,
        ];

        if ($existing) {
            $record->id = $existing->id;
            $DB->update_record('local_autograder_config', $record);
        } else {
            $record->timecreated = $now;
            $record->id = $DB->insert_record('local_autograder_config', $record);
        }

        return $record;
    }

    /**
     * Removes a course module's configuration entirely — the module itself
     * was deleted, there is nothing left to keep it for.
     *
     * @param int $cmid
     */
    public static function delete_for_cm(int $cmid): void {
        global $DB;

        $DB->delete_records('local_autograder_config', ['cmid' => $cmid]);
    }
}
