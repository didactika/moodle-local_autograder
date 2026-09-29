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
 * whole of the selection. Nothing is asked of the chosen teacher afterwards.
 *
 * In particular no capability is re-checked per user here. It would decide
 * nothing — the grade is written through
 * `component_gradeitem::store_grade_from_formdata()`, which checks no
 * capability at all, so a refusal here forbids what the write would have
 * allowed — and asking it twice is how a teacher ended up rejected on one
 * activity while teaching the course the grade belongs to. Capabilities are
 * consulted once, by {@see teacher_source}, to work out which *roles* teach a
 * course at all.
 *
 * A teacher is therefore assumed willing and able unless one of two things
 * says otherwise — they are an administrator or guest, or they have set the
 * opt-out preference on a site that offers it. With no teacher left the site
 * fallback signs it, and with no fallback the decision fails and says so.
 *
 * @package local_autograder
 * @copyright 2026 Didactika.org
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grader_picker {
    /** @var string The capability a module without one of its own is graded through. */
    private const DEFAULT_GRADE_CAPABILITY = 'moodle/grade:edit';

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
