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
 * Who teaches the course is {@see teacher_source}'s answer. Of those, only a
 * teacher who could give this grade themselves may sign it: one who can grade
 * the activity, and who can see the student there — in an activity that
 * separates its groups, a teacher without access to every group grades only
 * the groups they are in. A grade is a record of who gave it, and one in the
 * name of somebody who could not have is a false record, whether or not the
 * write would have let it through; most of them would, as only an assignment
 * asks. Among the teachers left, the one closest to the student by group is
 * chosen, and the configured tie-break settles the rest.
 *
 * Nobody else is asked for, either: an administrator or a guest never signs
 * a grade, and a teacher who has opted out does not while the site offers
 * that. With no teacher left the site fallback signs it — the one the site
 * chose on purpose, and so not held to these rules — and with no fallback the
 * decision fails and says so.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grader_picker {
    /** @var string The capability a module without one of its own is graded through. */
    private const DEFAULT_GRADE_CAPABILITY = 'moodle/grade:edit';

    /**
     * The teacher an automatic grade on this activity is signed as.
     *
     * Two activities of one course pick the same teacher for a student unless
     * the teacher may grade one of them and not the other.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null Null when no teacher of the student can grade them here.
     */
    public static function pick_for(int $cmid, int $studentid): ?int {
        $cm = self::module($cmid);

        return $cm ? self::tie_break(self::candidates_for_module($cm, $studentid), (int) $cm->course) : null;
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
     * Course association only: who may grade each activity can change the
     * final choice, which is {@see self::pick_for()}'s.
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
     * The opt-out preference is only honoured while the site offers it. A site
     * that turns the preference off is saying it decides who grades, not the
     * teachers, and a preference set before that — or by a teacher who no
     * longer means it — must not go on quietly removing somebody.
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
        $honouroptout = !empty(get_config('local_autograder', 'allowoptout'));
        $usable = [];
        foreach ($users as $user) {
            if (self::must_never_grade((int) $user->id)) {
                continue;
            }
            if ($honouroptout && !empty($user->optout)) {
                continue;
            }
            $usable[] = (int) $user->id;
        }
        $cache->set($key, $usable);
        return $usable;
    }

    /**
     * The student's closest teachers who may sign the grade.
     *
     * Who may sign is settled first and closeness second. The other way round,
     * the student's closest teacher having opted out left the choice with
     * nobody and handed it to the site fallback, while another of their
     * teachers — one group further away, but theirs — sat unused.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function candidates_for_course(int $courseid, int $studentid): array {
        $teachers = array_diff(self::usable(teacher_source::possible_graders_in($courseid)), [$studentid]);

        return teacher_source::closest_to($courseid, $studentid, $teachers);
    }

    /**
     * The student's closest teachers who may sign the grade on this activity.
     *
     * Who could give the grade is settled before who is closest, for the same
     * reason the opt-out is: a closest teacher who cannot grade the student
     * here hands over to the next closest who can, not past all of them.
     *
     * @param \stdClass $cm
     * @param int $studentid
     * @return int[]
     */
    private static function candidates_for_module(\stdClass $cm, int $studentid): array {
        $courseid = (int) $cm->course;
        $teachers = array_diff(self::usable(teacher_source::possible_graders_in($courseid)), [$studentid]);
        $able = array_filter(
            $teachers,
            static fn(int $teacherid): bool => self::can_grade_student($cm, $teacherid, $studentid),
        );

        return teacher_source::closest_to($courseid, $studentid, $able);
    }

    /**
     * Whether this teacher could grade this student on this activity by hand.
     *
     * Moodle's own two conditions for it: the activity's grading capability,
     * read in the activity itself so that an override made there or in the
     * course counts; and, where the activity separates its groups, access to
     * the student's group — every group, or one of the activity's grouping
     * they share with the student.
     *
     * @param \stdClass $cm
     * @param int $teacherid
     * @param int $studentid
     * @return bool
     */
    private static function can_grade_student(\stdClass $cm, int $teacherid, int $studentid): bool {
        if (!self::has_capability_here($cm, self::grade_capability_for((string) $cm->modname), $teacherid)) {
            return false;
        }

        $course = teacher_source::course((int) $cm->course);

        if (groups_get_activity_groupmode($cm, $course) != SEPARATEGROUPS) {
            return true;
        }

        if (self::has_capability_here($cm, 'moodle/site:accessallgroups', $teacherid)) {
            return true;
        }

        $groupingid = (int) $cm->groupingid;

        return (bool) array_intersect(
            teacher_source::groups_of((int) $cm->course, $studentid, $groupingid),
            teacher_source::groups_of((int) $cm->course, $teacherid, $groupingid),
        );
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
     * Every capability that lets somebody put a grade on something.
     *
     * The generic gradebook one plus each module type's own. Used to work out
     * which roles teach a course — a role that grants one of these is a role
     * that grades — so that a site defining its own grading role does not also
     * have to tell autograder about it.
     *
     * @return string[]
     */
    public static function grade_capabilities(): array {
        global $DB;

        $cache = self::request_cache();
        $cached = $cache->get('gradecapabilities');

        if ($cached !== false) {
            return $cached;
        }

        // Read out of the capability table rather than listed here: every
        // module that can be graded declares `mod/<name>:grade` by Moodle's
        // own convention, so a module installed later is covered without this
        // plugin being told about it. The gradebook capability comes along for
        // the module types graded by overriding the gradebook instead.
        $capabilities = $DB->get_fieldset_sql(
            'SELECT DISTINCT name FROM {capabilities} WHERE ' . $DB->sql_like('name', ':pattern'),
            ['pattern' => 'mod/%:grade']
        );
        $capabilities[] = self::DEFAULT_GRADE_CAPABILITY;
        $capabilities = array_values(array_unique($capabilities));

        $cache->set('gradecapabilities', $capabilities);

        return $capabilities;
    }

    /**
     * The capability a grade on this activity is written through.
     *
     * Its module's own where it declares one, and the gradebook capability
     * where it does not — which is what the generic adapter writes through.
     *
     * @param string $modname
     * @return string
     */
    public static function grade_capability_for(string $modname): string {
        $capability = 'mod/' . $modname . ':grade';

        return in_array($capability, self::grade_capabilities(), true)
            ? $capability
            : self::DEFAULT_GRADE_CAPABILITY;
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
            self::grade_capability_for((string) $cm->modname),
            \context_module::instance((int) $cm->id),
            $userid
        );
    }

    /**
     * A teacher's capability in an activity, asked once per request.
     *
     * The answer is the same for every student, and a report or a run of
     * grading tasks asks it for each of them; for somebody other than the
     * current user, core checks their access is not stale on every call.
     *
     * @param \stdClass $cm
     * @param string $capability
     * @param int $userid
     * @return bool
     */
    private static function has_capability_here(\stdClass $cm, string $capability, int $userid): bool {
        $cache = self::request_cache();
        $key = 'capability-' . $cm->id . '-' . $userid . '-' . $capability;
        $allowed = $cache->get($key);

        if ($allowed === false) {
            $allowed = has_capability($capability, \context_module::instance((int) $cm->id), $userid) ? 1 : 0;
            $cache->set($key, $allowed);
        }

        return (bool) $allowed;
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
