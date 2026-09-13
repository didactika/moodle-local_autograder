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
 * Which teacher autograder grades a given student on behalf of.
 *
 * The group/grouping narrowing is the same logic
 * `local_resume\local\teachers::get_user_teachers_by_groups` uses to decide
 * which teachers a student may see — reimplemented here, not depended on,
 * because the two plugins are independent. The starting pool is different on
 * purpose: `local_resume` starts from a configured list of teacher roles;
 * this starts from who holds the `local/autograder:gradeonbehalf` capability —
 * a permission, not a role list, so a site adjusts it with an ordinary role
 * override instead of a setting.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grader_picker {
    /**
     * The capability that actually lets Moodle accept a grade for a module
     * type, by `modname`. A type not listed here falls back to
     * `moodle/grade:edit` — the generic gradebook-override capability, which
     * is what the generic adapter writes through.
     */
    private const GRADE_CAPABILITY_BY_MODULE = [
        'assign' => 'mod/assign:grade',
        'quiz' => 'mod/quiz:grade',
        'forum' => 'mod/forum:grade',
    ];

    /** @var string The capability a type not in {@see GRADE_CAPABILITY_BY_MODULE} is graded through. */
    private const DEFAULT_GRADE_CAPABILITY = 'moodle/grade:edit';

    /**
     * Picks the teacher to grade one student in one course module, or null
     * when nobody qualifies.
     *
     * Deliberately called at the moment of grading, not when the decision is
     * created — a teacher can join or leave the course, a group, or the
     * capability in the days a decision waits to be due.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null The chosen user id, or null when nobody does.
     */
    public static function pick_for(int $cmid, int $studentid): ?int {
        $cm = get_coursemodule_from_id(null, $cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return null;
        }

        $candidates = self::candidates_for($cm);
        $candidates = self::narrow_by_groups($cm, $studentid, $candidates);

        if (empty($candidates)) {
            return self::fallback_grader($cm);
        }

        return self::tie_break($candidates, $cm->course);
    }

    /**
     * Every user who could plausibly grade this module: holds
     * `local/autograder:gradeonbehalf` and the module's own real grading
     * capability, in its context, and has not opted out.
     *
     * @param \cm_info|\stdClass $cm
     * @return array<int, \stdClass> Candidate users keyed by id.
     */
    private static function candidates_for(\cm_info|\stdClass $cm): array {
        $cache = self::request_cache();
        $key = 'candidates-' . (int) $cm->id;
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $modulecontext = \context_module::instance($cm->id);
        $gradecapability = self::grade_capability_for($cm->modname);

        $permitted = get_users_by_capability($modulecontext, 'local/autograder:gradeonbehalf');
        $graders = get_users_by_capability($modulecontext, $gradecapability);

        $candidates = [];

        foreach ($permitted as $userid => $user) {
            if (!isset($graders[$userid])) {
                continue;
            }

            if (self::has_opted_out((int) $userid)) {
                continue;
            }

            $candidates[$userid] = $user;
        }

        $cache->set($key, $candidates);

        return $candidates;
    }

    /**
     * Narrows candidates to those who share a group with the student, when
     * the course actually separates by group — otherwise a no-op.
     *
     * A candidate with `moodle/site:accessallgroups` in the course is never
     * filtered out: they can grade anyone regardless of group. If the group
     * filter would leave nobody, it is discarded and every candidate stands —
     * a course misconfigured with no shared group is not reason to grade
     * nobody.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $studentid
     * @param array<int, \stdClass> $candidates
     * @return array<int, \stdClass>
     */
    private static function narrow_by_groups(\cm_info|\stdClass $cm, int $studentid, array $candidates): array {
        if (empty($candidates)) {
            return $candidates;
        }

        $course = get_course($cm->course);

        if ((int) groups_get_course_groupmode($course) !== SEPARATEGROUPS || empty($course->defaultgroupingid)) {
            return $candidates;
        }

        $membership = self::grouping_membership((int) $cm->course, (int) $course->defaultgroupingid);
        $studentgroupids = $membership[$studentid] ?? [];

        if (empty($studentgroupids)) {
            return $candidates;
        }

        $coursecontext = \context_course::instance($cm->course);
        $filtered = [];

        foreach ($candidates as $candidateid => $candidate) {
            if (has_capability('moodle/site:accessallgroups', $coursecontext, $candidateid)) {
                $filtered[$candidateid] = $candidate;

                continue;
            }

            if (array_intersect($studentgroupids, $membership[(int) $candidateid] ?? [])) {
                $filtered[$candidateid] = $candidate;
            }
        }

        return empty($filtered) ? $candidates : $filtered;
    }

    /**
     * Who belongs to which group of one grouping, as a single map.
     *
     * Read in one go rather than once per person: the student and every
     * candidate are weighed against the same grouping, and the report asks
     * this question for a whole page of students at a time.
     *
     * @param int $courseid
     * @param int $groupingid
     * @return array<int, int[]> Group ids, by user id.
     */
    private static function grouping_membership(int $courseid, int $groupingid): array {
        global $DB;

        $cache = self::request_cache();
        $key = "grouping-{$courseid}-{$groupingid}";
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $sql = "SELECT gm.id, gm.userid, gm.groupid
                  FROM {groups_members} gm
                  JOIN {groups} g ON g.id = gm.groupid
                  JOIN {groupings_groups} gg ON gg.groupid = g.id
                 WHERE g.courseid = :courseid AND gg.groupingid = :groupingid";
        $membership = [];

        foreach ($DB->get_records_sql($sql, ['courseid' => $courseid, 'groupingid' => $groupingid]) as $row) {
            $membership[(int) $row->userid][] = (int) $row->groupid;
        }

        $cache->set($key, $membership);

        return $membership;
    }

    /**
     * Where the answers that cannot change during one request are kept.
     *
     * Who may grade a module is asked once per student, and the report asks it
     * for every waiting student on a page. The questions underneath — who
     * holds a capability in this context, who is in which group — each cost a
     * real query against role assignments or group membership, and none of
     * them can differ between two rows of the same page. A request cache
     * rather than a static array, so that a test resetting the site clears
     * this along with everything else.
     *
     * @return \cache_loader
     */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'graderpicker');
    }

    /**
     * The site's configured last resort, when the course itself has nobody
     * eligible — revalidated against the module's own grading capability
     * — a user holding `moodle/grade:edit` site-wide is not guaranteed to
     * still hold it once a role override narrows it back down in one course
     * or category. `local/autograder:gradeonbehalf` is deliberately not
     * required of the fallback grader — they stand in exactly because the
     * course has nobody who holds it.
     *
     * @param \cm_info|\stdClass $cm
     * @return int|null
     */
    private static function fallback_grader(\cm_info|\stdClass $cm): ?int {
        $fallbackid = (int) get_config('local_autograder', 'fallback_grader');

        if ($fallbackid <= 0 || self::has_opted_out($fallbackid)) {
            return null;
        }

        $modulecontext = \context_module::instance($cm->id);
        $gradecapability = self::grade_capability_for($cm->modname);

        if (!has_capability($gradecapability, $modulecontext, $fallbackid)) {
            return null;
        }

        return $fallbackid;
    }

    /**
     * Picks one candidate deterministically, per the site's configured rule.
     *
     * @param array<int, \stdClass> $candidates
     * @param int $courseid
     * @return int
     */
    private static function tie_break(array $candidates, int $courseid): int {
        if (count($candidates) === 1) {
            return (int) array_key_first($candidates);
        }

        if (get_config('local_autograder', 'tiebreak') === 'last_course_access') {
            $mostrecent = self::most_recently_active(array_keys($candidates), $courseid);

            if ($mostrecent !== null) {
                return $mostrecent;
            }
        }

        $ids = array_map('intval', array_keys($candidates));
        sort($ids);

        return $ids[0];
    }

    /**
     * The candidate who accessed the course most recently, or null when none
     * of them ever has (falls back to the lowest user id, same as the
     * default rule).
     *
     * @param array<int|string> $candidateids
     * @param int $courseid
     * @return int|null
     */
    private static function most_recently_active(array $candidateids, int $courseid): ?int {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal(array_map('intval', $candidateids), SQL_PARAMS_NAMED);
        $params['courseid'] = $courseid;

        $sql = "SELECT userid
                  FROM {user_lastaccess}
                 WHERE courseid = :courseid AND userid $insql
              ORDER BY timeaccess DESC";

        $userid = $DB->get_field_sql($sql, $params, IGNORE_MULTIPLE);

        return $userid ? (int) $userid : null;
    }

    /**
     * The capability that lets Moodle accept a grade for this module type.
     *
     * @param string $modname
     * @return string
     */
    private static function grade_capability_for(string $modname): string {
        return self::GRADE_CAPABILITY_BY_MODULE[$modname] ?? self::DEFAULT_GRADE_CAPABILITY;
    }

    /**
     * Whether a user is someone who grades this activity rather than someone
     * who is graded on it.
     *
     * Used to keep teachers out of the set of students autograder watches.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return bool
     */
    public static function grades_this_module(\cm_info|\stdClass $cm, int $userid): bool {
        return has_capability(
            self::grade_capability_for($cm->modname),
            \context_module::instance((int) $cm->id),
            $userid
        );
    }

    /**
     * Whether a user has asked never to be chosen.
     *
     * @param int $userid
     * @return bool
     */
    private static function has_opted_out(int $userid): bool {
        // The site decides whether the preference counts at all. Somewhere it
        // was once offered and then switched off, a teacher's old answer must
        // stop taking them out of the rota — otherwise the site would still be
        // honouring an option it no longer admits to having.
        if (!get_config('local_autograder', 'allowoptout')) {
            return false;
        }

        return (bool) get_user_preferences('local_autograder_optout', false, $userid);
    }
}
