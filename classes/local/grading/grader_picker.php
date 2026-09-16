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
 * Select the associated teacher whose name an automatic grade is posted under.
 *
 * Who teaches the student is {@see teacher_source}'s answer, and that is the
 * whole of the selection. No grading capability is consulted, for
 * two reasons. It decides nothing: the grade is written through
 * `component_gradeitem::store_grade_from_formdata()`, which checks no
 * capability at all, so a check here forbids what the write would have allowed.
 * And it decides it wrongly: a site names its correctors through roles of its
 * own, which routinely carry no `mod/forum:grade`, so the check rejected
 * precisely the teachers the student is shown as theirs.
 *
 * A teacher is therefore assumed willing and able unless one of two things
 * says otherwise — they are an administrator or guest, or they have set the
 * opt-out preference. With no teacher left the site fallback signs it, and
 * with no fallback the decision fails and says so.
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
     * Select among the student's associated teachers in the activity's course.
     *
     * The activity narrows nothing: association is a fact about the course, so
     * two activities of one course pick the same teacher for one student.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null
     */
    public static function pick_for(int $cmid, int $studentid): ?int {
        $cm = self::module($cmid);

        return $cm ? self::pick_for_course((int) $cm->course, $studentid) : null;
    }

    /**
     * The same effective choice used by both the worker and the report.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null
     */
    public static function resolve_for(int $cmid, int $studentid): ?int {
        return self::pick_for($cmid, $studentid) ?? self::fallback_for();
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
     * Whether a user must never have a grade posted in their name.
     *
     * An administrator holds every capability in every course, so their name
     * on a grade says nothing true about who taught the student.
     *
     * @param int $userid
     * @return bool
     */
    public static function must_never_grade(int $userid): bool {
        return $userid <= 0 || is_siteadmin($userid) || isguestuser($userid);
    }

    /**
     * The site's configured last resort, for a student with no teacher left.
     *
     * Only {@see self::usable()} narrows it: the activity is not consulted,
     * because a fallback rejected for want of a capability the write never
     * checks would leave the decision failing with a grader sitting unused.
     *
     * @return int|null
     */
    public static function fallback_for(): ?int {
        $id = (int) get_config('local_autograder', 'fallback_grader');

        return self::usable([$id]) ? $id : null;
    }

    /**
     * Whether this user is one of the people who grade this activity.
     *
     * Asked to keep an activity's own graders off the list of people it
     * grades, and for nothing else — never to decide whether a chosen teacher
     * may post, which is not this plugin's question to answer.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return bool
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
                   ORDER BY timeaccess DESC, userid ASC",
                    $params,
                    0,
                    1
                );
                $picked = $records ? (int) array_key_first($records) : $candidates[0];
                $cache->set($key, $picked);
            }
            return $picked;
        }
        return $candidates[0];
    }

    /**
     * The course module record, read once per request.
     *
     * @param int $cmid
     * @return \stdClass|null Null when the activity is gone.
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

    /**
     * Clears selection data when a long-running cron worker starts another task.
     */
    public static function reset_caches(): void {
        self::request_cache()->purge();
        \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'teachersource')->purge();
    }

    /**
     * The request-only cache this class keeps its answers in.
     *
     * @return \cache_loader Reset between cron tasks by Moodle.
     */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'graderpicker');
    }
}
