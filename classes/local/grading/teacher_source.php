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

/**
 * Teacher association using local_resume's configured roles and grouping rules.
 *
 * Profile role visibility is deliberately not a grading permission. Association
 * must not depend on the report viewer or the cron user. The picker validates
 * the resulting candidates against the actual activity grading permission.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_source {
    /** @return bool Whether the association provider is installed. */
    public static function is_available(): bool {
        return \core_component::get_component_directory('local_resume') !== null
            && class_exists('\local_resume\local\teachers');
    }

    /**
     * Course candidates, without mixing teachers and programme coordinators.
     *
     * @param int $courseid
     * @return int[]
     */
    public static function possible_graders_in(int $courseid): array {
        global $DB;

        $cache = self::request_cache();
        $key = 'teachers-' . $courseid;
        $cached = $cache->get($key);
        if ($cached !== false) {
            return $cached;
        }
        if (!self::is_available()) {
            return [];
        }

        $course = self::course($courseid);
        // Match local_resume::get_course_type(), including subject precedence.
        $program = $course->category != get_config('local_resume', 'subject_course_category')
            && $course->category == get_config('local_resume', 'program_course_category');
        $roles = array_filter(array_map('trim', explode(',', (string) get_config(
            'local_resume', $program ? 'coordinator_roles' : 'teacher_roles'
        ))));
        $teachers = [];
        if ($roles !== []) {
            [$insql, $params] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED);
            $params['contextid'] = \context_course::instance($courseid)->id;
            $teachers = array_map('intval', $DB->get_fieldset_sql(
                "SELECT DISTINCT ra.userid
                   FROM {role_assignments} ra
                   JOIN {role} r ON r.id = ra.roleid
                  WHERE ra.contextid = :contextid AND r.shortname {$insql}",
                $params
            ));
        }
        $cache->set($key, $teachers);
        return $teachers;
    }

    /**
     * Preload only the requested users' memberships, shared across activities.
     *
     * @param int $courseid
     * @param int[] $userids
     */
    public static function prime_groups(int $courseid, array $userids): void {
        global $DB;

        $cache = self::request_cache();
        $missing = [];
        foreach (array_unique($userids) as $userid) {
            if ($cache->get('groups-' . $courseid . '-' . $userid) === false) {
                $missing[(int) $userid] = [];
            }
        }
        // Bound IN lists even when a caller processes a whole course.
        foreach (array_chunk(array_keys($missing), 500) as $ids) {
            [$insql, $params] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $params['courseid'] = $courseid;
            $records = $DB->get_records_sql(
                "SELECT gm.id, gm.userid, gm.groupid
                   FROM {groups_members} gm
                   JOIN {groups} g ON g.id = gm.groupid
                  WHERE g.courseid = :courseid AND gm.userid {$insql}",
                $params
            );
            foreach ($records as $record) {
                $missing[(int) $record->userid][] = (int) $record->groupid;
            }
        }
        foreach ($missing as $userid => $groups) {
            $cache->set('groups-' . $courseid . '-' . $userid, $groups);
        }
    }

    /**
     * User groups, optionally narrowed to one grouping.
     *
     * @param int $courseid
     * @param int $userid
     * @param int $groupingid
     * @return int[]
     */
    public static function groups_of(int $courseid, int $userid, int $groupingid = 0): array {
        global $DB;

        self::prime_groups($courseid, [$userid]);
        $cache = self::request_cache();
        $groups = $cache->get('groups-' . $courseid . '-' . $userid);
        if (!$groupingid) {
            return $groups;
        }
        $key = 'grouping-' . $groupingid;
        $allowed = $cache->get($key);
        if ($allowed === false) {
            $allowed = $DB->get_fieldset_select('groupings_groups', 'groupid', 'groupingid = ?', [$groupingid]);
            $cache->set($key, $allowed);
        }
        return array_values(array_intersect($groups, $allowed));
    }

    /**
     * Associated teachers; an empty group match falls back to course teachers,
     * as in local_resume. Activity permissions are checked by grader_picker.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function teachers_of(int $courseid, int $studentid): array {
        $teachers = self::possible_graders_in($courseid);
        $course = self::course($courseid);
        if (!$teachers || groups_get_course_groupmode($course) != SEPARATEGROUPS
                || !$course->defaultgroupingid
                || has_capability('moodle/site:accessallgroups', \context_course::instance($courseid), $studentid)) {
            return $teachers;
        }
        self::prime_groups($courseid, array_merge($teachers, [$studentid]));
        $groups = self::groups_of($courseid, $studentid, (int) $course->defaultgroupingid);
        $matched = array_filter($teachers, static function(int $teacherid) use ($courseid, $groups): bool {
            return (bool) array_intersect($groups, self::groups_of($courseid, $teacherid));
        });
        return array_values($matched ?: $teachers);
    }

    /**
     * @param int $courseid
     * @return \stdClass Course metadata shared by students and activities.
     */
    public static function course(int $courseid): \stdClass {
        $cache = self::request_cache();
        $key = 'course-' . $courseid;
        $course = $cache->get($key);
        if ($course === false) {
            $course = get_course($courseid);
            $cache->set($key, $course);
        }
        return $course;
    }

    /** @return \cache_loader Request-only association data. */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'teachersource');
    }
}
