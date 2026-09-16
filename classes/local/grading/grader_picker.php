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
 * Which teacher autograder grades a given student on behalf of.
 *
 * The answer is `local_resume`'s, asked through {@see teacher_source} rather
 * than worked out again here — and it is that answer entire, with nothing
 * added to it. It used to be worked out here, from a capability of this
 * plugin's own, and that produced a different list from the one the student is
 * shown as their own teachers: a grade signed by somebody the student has
 * never been told is their teacher. The capability has since been dropped.
 *
 * Three rules settle the rest, in order:
 *
 * - A site administrator is never chosen, for anything. They hold every
 *   capability in every course, so any rule phrased in capabilities picks
 *   them everywhere, and their name on a grade says nothing true.
 * - A student with no teacher falls to the site's configured fallback grader.
 * - With no fallback either, the decision fails and says so. Nothing is
 *   posted in a name that was not really behind it — least of all somebody
 *   who merely holds a grading capability in the course.
 *
 * No capability is checked while choosing — see {@see self::pick_for()}.
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
     * when the student has none.
     *
     * Who a student's teachers are is not decided here: it is
     * `local_resume`'s answer, asked through {@see teacher_source}, so that
     * the name on the grade is one of the names the student is shown as their
     * own teachers. Nothing else would be defensible to either of them.
     *
     * No capability is checked while choosing. Whether the chosen teacher can
     * really post the grade is settled by trying, at the moment of grading —
     * see {@see \local_autograder\task\grade_student}, which falls back and
     * then fails rather than guessing in advance.
     *
     * Deliberately called at the moment of grading, not when the decision is
     * created: a teacher can join or leave the course, or a group, in the days
     * a decision waits to be due.
     *
     * @param int $cmid
     * @param int $studentid
     * @return int|null The chosen user id, or null when the student has no teacher.
     */
    public static function pick_for(int $cmid, int $studentid): ?int {
        $cm = get_coursemodule_from_id(null, $cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return null;
        }

        return self::pick_for_course((int) $cm->course, $studentid);
    }

    /**
     * The same choice, made from the course rather than one of its activities.
     *
     * Who a student's teachers are is a fact about the course, so nothing in
     * the answer needs the activity — which is what lets a report show, for a
     * whole course at once, who would be grading whom.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int|null
     */
    public static function pick_for_course(int $courseid, int $studentid): ?int {
        $candidates = self::candidates_for_course($courseid, $studentid);

        if (empty($candidates)) {
            return null;
        }

        return self::tie_break(array_flip($candidates), $courseid);
    }

    /**
     * Those of a list who may actually be chosen.
     *
     * Public so that a report showing who could grade a course filters the
     * list through the same rule the picker itself applies, instead of
     * offering names this class would then refuse.
     *
     * @param int[] $userids
     * @return int[]
     */
    public static function usable(array $userids): array {
        $usable = [];

        foreach ($userids as $userid) {
            if (!self::must_never_grade($userid) && !self::has_opted_out($userid)) {
                $usable[] = (int) $userid;
            }
        }

        return $usable;
    }

    /**
     * Everybody who could be chosen for this student, not just the one who is.
     *
     * The honest answer when something asks who might grade this student
     * rather than who would today: the choice can change between now and the
     * moment the grade is due, as teachers join and leave the course.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function candidates_for_course(int $courseid, int $studentid): array {
        return self::teachers_of($courseid, $studentid);
    }

    /**
     * The student's teachers, minus the ones who must never be chosen.
     *
     * Cached per request and per student, because the answer costs
     * local_resume several queries and one activity's catch-up asks it for
     * every student on the course.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    private static function teachers_of(int $courseid, int $studentid): array {
        $cache = self::request_cache();
        $key = 'teachers-' . $courseid . '-' . $studentid;
        $cached = $cache->get($key);

        if ($cached !== false) {
            return $cached;
        }

        $teachers = self::usable(teacher_source::teachers_of($courseid, $studentid));

        $cache->set($key, $teachers);

        return $teachers;
    }

    /**
     * Whether a user must never have a grade posted in their name.
     *
     * A site administrator never grades. They hold every capability
     * everywhere, so any rule written in terms of capabilities picks them for
     * every course on the site — and a grade signed by the administrator
     * account tells a student nothing true about who taught them.
     *
     * @param int $userid
     * @return bool
     */
    public static function must_never_grade(int $userid): bool {
        return $userid <= 0 || is_siteadmin($userid) || isguestuser($userid);
    }

    /**
     * The site's configured last resort, for when the student has no teacher
     * who could post the grade.
     *
     * @param int $cmid
     * @return int|null
     */
    public static function fallback_for(int $cmid): ?int {
        unset($cmid);

        return self::fallback_grader();
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
     * The site's configured last resort, for a student whose course has no
     * teacher of their own — or whose teacher turned out not to be able to
     * post the grade.
     *
     * @return int|null
     */
    private static function fallback_grader(): ?int {
        $fallbackid = (int) get_config('local_autograder', 'fallback_grader');

        if (self::must_never_grade($fallbackid) || self::has_opted_out($fallbackid)) {
            return null;
        }

        // No capability check here either: whether they can really post this
        // grade is settled by posting it. If they cannot, there is nobody left
        // and the decision fails — which is the honest outcome, and the one an
        // administrator would otherwise have been silently used to hide.
        return $fallbackid;
    }

    /**
     * Picks one candidate deterministically, per the site's configured rule.
     *
     * @param array $candidates Keyed by user id.
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
     * @param array $candidateids Their user ids.
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
        // Honoured whenever it is set, and only then. Somebody who may grade
        // is assumed willing to be graded on behalf of; the one thing that
        // changes that is their own answer saying otherwise. The site setting
        // decides whether the preference is *offered*, not whether an answer
        // already given still counts — withdrawing the offer must not start
        // putting grades in the name of somebody who asked us not to.
        return (bool) get_user_preferences('local_autograder_optout', false, $userid);
    }
}
