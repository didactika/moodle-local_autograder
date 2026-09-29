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
 * A course names its teachers by role: whoever holds a teaching role,
 * assigned in the course itself, and is actively enrolled there. Among them a
 * student's own teachers are the ones closest to them by group: the most
 * groups in common, then — with none in common — a teacher in no group at
 * all, and only then any teacher of the course, rather than the student being
 * left with no teacher. Where the course separates its groups through a
 * default grouping, only the groups of that grouping count.
 *
 * What makes a role a teaching role is worked out, not listed. By default it
 * is any role that grants one of the capabilities a grade is actually written
 * through ({@see grader_picker::grade_capabilities()}), which means a site
 * that defines its own marking role gets it recognised the moment the role
 * exists. Naming roles by hand is the failure this avoids: a list holding the
 * two stock roles, a site adding a third that grades, and every course it
 * teaches reporting that nobody can grade it — with nothing on screen to say
 * why.
 *
 * A site that wants the choice pinned can still make it by hand; the setting
 * is a fallback from the automatic rule, not the normal way to run it.
 *
 * The capability is read off the *role*, once per course, and never per user
 * afterwards. A role that can grade makes its holders teachers; whether a
 * given grade can then be stored is settled by storing it.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @author Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_source {
    /** @var string Teaching roles are whichever ones can grade. */
    public const MODE_GRADING_ROLES = 'grading_roles';

    /** @var string Teaching roles are the ones the site named. */
    public const MODE_CHOSEN_ROLES = 'chosen_roles';

    /**
     * Everybody who teaches this course, before any student narrows it.
     *
     * Only an active enrolment counts: a teacher whose enrolment is suspended,
     * not started yet or over has stopped teaching the course, whatever role
     * is still assigned to them, and a grade in their name would say they had
     * not.
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

        $context = \context_course::instance($courseid);
        $roleids = self::teaching_roles();
        $teachers = [];

        if ($roleids !== []) {
            [$insql, $params] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);
            [$enrolledsql, $enrolledparams] = get_enrolled_sql($context, '', 0, true);
            $params['contextid'] = $context->id;
            // The course context and only the course context. A role held over
            // the whole category makes somebody a teacher of every course in
            // it, and a manager assigned at the system level a teacher of the
            // entire site — which is not what a grade should be signed with.
            $teachers = array_map('intval', $DB->get_fieldset_sql(
                "SELECT DISTINCT ra.userid
                   FROM {role_assignments} ra
                   JOIN ({$enrolledsql}) enrolled ON enrolled.id = ra.userid
                  WHERE ra.contextid = :contextid AND ra.roleid {$insql}",
                $params + $enrolledparams
            ));
        }

        $cache->set($key, $teachers);

        return $teachers;
    }

    /**
     * The roles that teach a course, as ids.
     *
     * One answer for the whole site either way, so it is worked out once
     * however many courses a report walks.
     *
     * @return int[]
     */
    private static function teaching_roles(): array {
        $chosen = get_config('local_autograder', 'teacher_source_mode') === self::MODE_CHOSEN_ROLES;
        $cache = self::request_cache();
        $key = $chosen ? 'roles-chosen' : 'roles-grading';
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $roleids = $chosen ? self::chosen_roles() : self::grading_roles();

        $cache->set($key, $roleids);

        return $roleids;
    }

    /**
     * Every role that grants one of the capabilities a grade is written with.
     *
     * One query for the whole site rather than one per course: a site report
     * covering a hundred thousand courses asks this once. Asking it per course
     * context — which is what would honour an override made in one course —
     * would be a query per capability per course, and the report would stop
     * being usable long before it became more accurate.
     *
     * So a role counts if it is granted a grading capability anywhere. That
     * errs towards including a role rather than leaving it out, which is the
     * right way round: a teacher wrongly left out means an activity nobody
     * can grade and no explanation on screen, while a role wrongly included
     * only reaches a grade if somebody holds it inside the course itself.
     *
     * @return int[]
     */
    private static function grading_roles(): array {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal(grader_picker::grade_capabilities(), SQL_PARAMS_NAMED);
        $params['allow'] = CAP_ALLOW;

        return array_map('intval', $DB->get_fieldset_sql(
            "SELECT DISTINCT rc.roleid
               FROM {role_capabilities} rc
              WHERE rc.capability {$insql} AND rc.permission = :allow",
            $params
        ));
    }

    /**
     * The roles the site named by hand, as ids.
     *
     * One list for the whole site: a course has teachers, and they are
     * whoever holds one of these roles in it. Nothing here sorts courses into
     * kinds and gives each kind its own list — that is a distinction a site
     * makes about its own courses, and repeating it would only give an
     * administrator two places to configure the same thing and one of them to
     * get wrong.
     *
     * @return int[]
     */
    private static function chosen_roles(): array {
        global $DB;

        $setting = get_config('local_autograder', 'teacher_roles');
        $shortnames = array_values(array_filter(array_map('trim', explode(',', (string) $setting))));

        if ($shortnames === []) {
            return [];
        }

        return array_map('intval', array_keys($DB->get_records_list('role', 'shortname', $shortnames, '', 'id')));
    }

    /**
     * The teachers among these who are closest to the student by group.
     *
     * A group is how a course says who belongs with whom, and a student in
     * "lang.es" and "program.abc" belongs most with the teacher in both. Every
     * group of the course counts, except where the course separates its
     * groups through a default grouping: there the course has said which
     * groups divide it, and only those count — a teacher whose groups all lie
     * outside it is, for this, a teacher in no group.
     *
     * Closest is the most groups in common — one is enough where no teacher
     * shares more. With none in common, a teacher in no group at all, who
     * teaches the course as a whole rather than some other part of it; and
     * with no such teacher either, every one of them, rather than the student
     * being told they have none. Where several are equally close, choosing
     * between them is the caller's tie-break.
     *
     * @param int $courseid
     * @param int $studentid
     * @param int[] $teachers The teachers who could grade, already narrowed to
     *                        the ones who may.
     * @return int[]
     */
    public static function closest_to(int $courseid, int $studentid, array $teachers): array {
        $teachers = array_values(array_map('intval', $teachers));

        if (!$teachers) {
            return [];
        }

        self::prime_groups($courseid, $teachers);
        $groupingid = self::separating_grouping($courseid, $studentid);
        $teachergroups = [];

        foreach ($teachers as $teacherid) {
            $teachergroups[$teacherid] = self::groups_of($courseid, $teacherid, $groupingid);
        }

        $groupless = array_keys(array_filter($teachergroups, static fn(array $groups): bool => $groups === []));

        // No teacher in any group: nothing about the student's groups can tell
        // them apart, so there is nothing to look up for each student.
        if (count($groupless) === count($teachers)) {
            return $teachers;
        }

        $studentgroups = self::groups_of($courseid, $studentid, $groupingid);
        $closest = [];
        $most = 0;

        foreach ($teachergroups as $teacherid => $groups) {
            $shared = count(array_intersect($studentgroups, $groups));

            if ($shared === 0 || $shared < $most) {
                continue;
            }

            if ($shared > $most) {
                $most = $shared;
                $closest = [];
            }

            $closest[] = $teacherid;
        }

        return $closest ?: ($groupless ?: $teachers);
    }

    /**
     * The grouping the course separates its groups by, for this student.
     *
     * Only a course in separate groups mode with a default grouping has one,
     * and only for a student it actually separates: one allowed to see every
     * group is in none of them for this purpose, and every group counts.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int The grouping's id, or 0 where every group counts.
     */
    private static function separating_grouping(int $courseid, int $studentid): int {
        $course = self::course($courseid);

        if (
            groups_get_course_groupmode($course) != SEPARATEGROUPS
            || !$course->defaultgroupingid
            || has_capability('moodle/site:accessallgroups', \context_course::instance($courseid), $studentid)
        ) {
            return 0;
        }

        return (int) $course->defaultgroupingid;
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
            $allowed = array_map(
                'intval',
                $DB->get_fieldset_select('groupings_groups', 'groupid', 'groupingid = ?', [$groupingid])
            );
            $cache->set($key, $allowed);
        }

        return array_values(array_intersect($groups, $allowed));
    }

    /**
     * The course record, read once per request.
     *
     * @param int $courseid
     * @return \stdClass
     */
    private static function course(int $courseid): \stdClass {
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
     * The request-only cache this class keeps its answers in.
     *
     * @return \cache_loader
     */
    private static function request_cache(): \cache_loader {
        return \cache::make_from_params(\cache_store::MODE_REQUEST, 'local_autograder', 'teachersource');
    }
}
