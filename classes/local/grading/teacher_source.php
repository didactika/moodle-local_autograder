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
 * Which teachers a student has in a course.
 *
 * Two questions, one rule. A course names its teachers by role: whoever holds
 * one of the configured teacher roles, assigned in the course itself. Where
 * the course separates its groups and names a default grouping, that list is
 * then narrowed to the teachers sharing one of the student's groups inside
 * that grouping — and if none do, the whole list stands rather than the
 * student being left with no teacher at all.
 *
 * Every part of that comes from this plugin's own settings, so a site can see
 * and change what decides it in one place. That is deliberate: the rule used
 * to be read out of another plugin's configuration, which meant a site could
 * add a corrector role, see it listed on the course, and never find out why
 * autograder thought the course had no teacher.
 *
 * No capability is consulted here or anywhere after it. Holding one of these
 * roles is what makes somebody a teacher of the course; whether the grade can
 * then be stored is settled by storing it.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_source {
    /**
     * Whether the site has said which roles make somebody a teacher.
     *
     * Nothing else can be answered until it has: with no roles configured
     * there is no rule, and inventing one would put grades in the names of
     * whoever happened to match it.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        return self::teacher_roles(false) !== [] || self::teacher_roles(true) !== [];
    }

    /**
     * Everybody who teaches this course, before any student narrows it.
     *
     * A property of the course, so it costs the same on a course of ten
     * students and one of ten thousand — which is what lets a report open on
     * it instead of walking every student.
     *
     * @param int $courseid
     * @return int[] Their user ids.
     */
    public static function possible_graders_in(int $courseid): array {
        global $DB;

        $cache = self::request_cache();
        $key = 'teachers-' . $courseid;
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $roles = self::teacher_roles(self::is_programme($courseid));
        $teachers = [];

        if ($roles !== []) {
            [$insql, $params] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED);
            $params['contextid'] = \context_course::instance($courseid)->id;
            // The course context and only the course context. A role held over
            // the whole category makes somebody a teacher of every course in
            // it, which is not what a grade should be signed with.
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
     * The teachers of one student, narrowed by group where the course says so.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function teachers_of(int $courseid, int $studentid): array {
        $teachers = self::possible_graders_in($courseid);
        $course = self::course($courseid);

        if (
            !$teachers
            || groups_get_course_groupmode($course) != SEPARATEGROUPS
            || !$course->defaultgroupingid
            || has_capability('moodle/site:accessallgroups', \context_course::instance($courseid), $studentid)
        ) {
            return $teachers;
        }

        self::prime_groups($courseid, array_merge($teachers, [$studentid]));
        $groups = self::groups_of($courseid, $studentid, (int) $course->defaultgroupingid);
        $matched = array_filter($teachers, static function (int $teacherid) use ($courseid, $groups): bool {
            return (bool) array_intersect($groups, self::groups_of($courseid, $teacherid));
        });

        // Nobody sharing a group is not the same as nobody teaching: the
        // course's teachers stand rather than the student being told they
        // have none.
        return array_values($matched ?: $teachers);
    }

    /**
     * Reads every requested user's group memberships in one pass.
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
     * A user's groups in a course, optionally narrowed to one grouping.
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
     * The course record, read once per request.
     *
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

    /**
     * Whether this course is a programme rather than a subject.
     *
     * A site can name one category for each. A course in neither, or in a
     * category named for both, is read as a subject: subjects are what most
     * courses are, and the teacher roles are the safer of the two lists to
     * fall back on.
     *
     * @param int $courseid
     * @return bool
     */
    private static function is_programme(int $courseid): bool {
        $course = self::course($courseid);
        $subject = get_config('local_autograder', 'subject_course_category');
        $programme = get_config('local_autograder', 'program_course_category');

        return $programme !== false && $programme !== ''
            && $course->category != $subject
            && $course->category == $programme;
    }

    /**
     * The role shortnames that make somebody a teacher of a course.
     *
     * @param bool $programme Read the programme list instead of the subject one.
     * @return string[]
     */
    private static function teacher_roles(bool $programme): array {
        $setting = get_config('local_autograder', $programme ? 'coordinator_roles' : 'teacher_roles');

        return array_values(array_filter(array_map('trim', explode(',', (string) $setting))));
    }

    /**
     * The request-only cache this class keeps its answers in.
     *
     * @return \cache_loader
     */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'teachersource');
    }
}
