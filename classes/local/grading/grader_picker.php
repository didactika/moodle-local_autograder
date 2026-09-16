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
 * Select an associated teacher who can grade this student in this activity.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grader_picker {
    /** @var array Native grading permissions; other adapters override the gradebook. */
    private const GRADE_CAPABILITY_BY_MODULE = [
        'assign' => 'mod/assign:grade',
        'forum' => 'mod/forum:grade',
    ];

    /**
     * Select among associated teachers, after checking activity access.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null
     */
    public static function pick_for(int $cmid, int $studentid): ?int {
        $cm = self::module($cmid);
        if (!$cm) {
            return null;
        }
        $candidates = array_filter(
            self::candidates_for_course((int) $cm->course, $studentid),
            static fn(int $id): bool => self::can_grade($cm, $id, $studentid)
        );
        return self::tie_break($candidates, (int) $cm->course);
    }

    /**
     * The same effective choice used by both the worker and the report.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null
     */
    public static function resolve_for(int $cmid, int $studentid): ?int {
        return self::pick_for($cmid, $studentid) ?? self::fallback_for($cmid, $studentid);
    }

    /**
     * Course association only: activity overrides can change the final choice.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int|null
     */
    public static function pick_for_course(int $courseid, int $studentid): ?int {
        return self::tie_break(self::candidates_for_course($courseid, $studentid), $courseid);
    }

    /**
     * Active, non-administrator accounts that have not opted out.
     *
     * @param int[] $userids
     * @return int[]
     */
    public static function usable(array $userids): array {
        global $DB;

        $userids = array_values(array_unique(array_filter(array_map('intval', $userids))));
        if (!$userids) {
            return [];
        }
        $cache = self::request_cache();
        $key = 'usable-' . sha1(implode(',', $userids));
        $cached = $cache->get($key);
        if ($cached !== false) {
            return $cached;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['preference'] = 'local_autograder_optout';
        $users = $DB->get_records_sql(
            "SELECT u.id, p.value AS optout
               FROM {user} u
          LEFT JOIN {user_preferences} p ON p.userid = u.id AND p.name = :preference
              WHERE u.id {$insql} AND u.deleted = 0 AND u.suspended = 0",
            $params
        );
        $usable = [];
        foreach ($users as $user) {
            if (!self::must_never_grade((int) $user->id) && empty($user->optout)) {
                $usable[] = (int) $user->id;
            }
        }
        $cache->set($key, $usable);
        return $usable;
    }

    /**
     * The student's associated teachers, before activity permission checks.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function candidates_for_course(int $courseid, int $studentid): array {
        return array_values(array_diff(self::usable(teacher_source::teachers_of($courseid, $studentid)), [$studentid]));
    }

    /**
     * @param int $userid
     * @return bool Whether this identity must never sign an automatic grade.
     */
    public static function must_never_grade(int $userid): bool {
        return $userid <= 0 || is_siteadmin($userid) || isguestuser($userid);
    }

    /**
     * Configured fallback; when an activity is supplied it must be gradable.
     * A zero cmid exposes the configured identity only, for course summaries.
     *
     * @param int $cmid
     * @param int $studentid Zero when no student is in scope.
     * @return int|null
     */
    public static function fallback_for(int $cmid, int $studentid = 0): ?int {
        $id = (int) get_config('local_autograder', 'fallback_grader');
        if (!self::usable([$id])) {
            return null;
        }
        if ($cmid > 0) {
            $cm = self::module($cmid);
            if (!$cm || !self::can_grade($cm, $id, $studentid)) {
                return null;
            }
        }
        return $id;
    }

    /**
     * Validate only permissions needed to grade: course access, the adapter's
     * grading capability, and access to the student's activity group.
     * Module context resolves inherited grants and activity prohibitions.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $graderid
     * @param int $studentid
     * @return bool
     */
    public static function can_grade(\cm_info|\stdClass $cm, int $graderid, int $studentid = 0): bool {
        if ($graderid === $studentid || !self::usable([$graderid])) {
            return false;
        }
        $cache = self::request_cache();
        $key = 'permission-' . $cm->id . '-' . $graderid;
        $allowed = $cache->get($key);
        if ($allowed === false) {
            $coursecontext = \context_course::instance((int) $cm->course);
            $allowed = (int) (self::grades_this_module($cm, $graderid)
                && (is_enrolled($coursecontext, $graderid, '', true)
                    || has_capability('moodle/course:view', $coursecontext, $graderid)));
            $cache->set($key, $allowed);
        }
        if (!$allowed) {
            return false;
        }
        $context = \context_module::instance((int) $cm->id);
        if (groups_get_activity_groupmode($cm, teacher_source::course((int) $cm->course)) != SEPARATEGROUPS
                || has_capability('moodle/site:accessallgroups', $context, $graderid)) {
            return true;
        }
        // A course summary has no target student; it cannot promise group access.
        if (!$studentid) {
            return true;
        }
        $groups = teacher_source::groups_of((int) $cm->course, $graderid, (int) $cm->groupingid);
        return (bool) array_intersect($groups, teacher_source::groups_of((int) $cm->course, $studentid));
    }

    /**
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return bool Whether the adapter's grading capability is granted.
     */
    public static function grades_this_module(\cm_info|\stdClass $cm, int $userid): bool {
        return has_capability(
            self::GRADE_CAPABILITY_BY_MODULE[$cm->modname] ?? 'moodle/grade:edit',
            \context_module::instance((int) $cm->id),
            $userid
        );
    }

    /**
     * Deterministic tie break, including equal last-access timestamps.
     *
     * @param int[] $candidates
     * @param int $courseid
     * @return int|null
     */
    private static function tie_break(array $candidates, int $courseid): ?int {
        global $DB;

        if (!$candidates) {
            return null;
        }
        sort($candidates, SORT_NUMERIC);
        if (count($candidates) > 1 && get_config('local_autograder', 'tiebreak') === 'last_course_access') {
            $cache = self::request_cache();
            $key = 'access-' . $courseid . '-' . sha1(implode(',', $candidates));
            $picked = $cache->get($key);
            if ($picked === false) {
                [$insql, $params] = $DB->get_in_or_equal($candidates, SQL_PARAMS_NAMED);
                $params['courseid'] = $courseid;
                $records = $DB->get_records_sql(
                    "SELECT userid FROM {user_lastaccess}
                      WHERE courseid = :courseid AND userid {$insql}
                   ORDER BY timeaccess DESC, userid ASC", $params, 0, 1
                );
                $picked = $records ? (int) array_key_first($records) : $candidates[0];
                $cache->set($key, $picked);
            }
            return $picked;
        }
        return $candidates[0];
    }

    /**
     * @param int $cmid
     * @return \stdClass|null Module lookup shared by every row on a page.
     */
    private static function module(int $cmid): ?\stdClass {
        $cache = self::request_cache();
        $key = 'module-' . $cmid;
        $cm = $cache->get($key);
        if ($cm === false) {
            $cm = get_coursemodule_from_id(null, $cmid, 0, false, IGNORE_MISSING) ?: null;
            $cache->set($key, $cm);
        }
        return $cm;
    }

    /** Clear selection data when a long-running cron worker starts another task. */
    public static function reset_caches(): void {
        self::request_cache()->purge();
        \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'teachersource')->purge();
    }

    /** @return \cache_loader Request-only cache, reset between cron tasks by Moodle. */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'graderpicker');
    }
}
