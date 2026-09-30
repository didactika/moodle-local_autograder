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

namespace local_autograder\local\decision;

use local_autograder\local\grading\grader_picker;
use local_autograder\local\module\module_adapter;

/**
 * Works out, from live Moodle data, whether a student is due to be graded and
 * when.
 *
 * This is the bridge between Moodle and {@see due_date_calculator}: it reads
 * the completion, the submission, the activity's close date, the exceptions
 * that apply to this student and the groups they are in, then hands all of it
 * to the rule, which knows nothing about the database. Everything here is
 * read fresh every time it is asked — a plan is never trusted from when it
 * was last written.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class decision_planner {
    /**
     * The completion conditions only a grade can meet.
     *
     * @var string[]
     */
    private const GRADE_CONDITIONS = [
        'completionusegrade',
        'completionpassgrade',
        // The quiz's "passing grade, or all attempts used": with attempts
        // still to go, only a pass meets it.
        'completionpassorattemptsexhausted',
    ];

    /**
     * What autograder should do about one student in one activity, right now.
     *
     * @param \cm_info|\stdClass $cm The course module.
     * @param \stdClass $config Its autograder configuration.
     * @param int $userid The student.
     * @return array{baselineduedate: int, duedatereason: string, scheduledgradetime: int}|null
     *         Null when there is nothing to grade: the student has not done
     *         the activity yet, as {@see self::engagement()} decides it.
     */
    public static function plan(\cm_info|\stdClass $cm, \stdClass $config, int $userid): ?array {
        $adapter = module_adapter::for_cm($cm, $config);
        [$completedat, $submittedat] = self::engagement($cm, $adapter, $userid);

        $result = due_date_calculator::calculate(
            $completedat,
            $submittedat,
            $adapter->close_date(),
            $adapter->user_override_date($userid),
            $adapter->group_override_dates(self::group_ids($cm, $userid)),
        );

        if ($result === null) {
            return null;
        }

        $result['scheduledgradetime'] = due_date_calculator::scheduled_grade_time(
            $result['baselineduedate'],
            (int) $config->delayseconds,
        );

        return $result;
    }

    /**
     * Whether autograder can ever tell that a student has done this activity.
     *
     * An activity with an adapter of its own can: handing it in is a thing
     * autograder knows how to read. Any other type can only be followed
     * through activity completion, and only through a condition the student
     * can meet on their own — marking it done, viewing it, or one of the
     * activity's own conditions. A completion that asks for a grade as well
     * is no help either, even beside conditions like those: until the grade
     * is there the activity never counts as done, so core never says the
     * student finished — and the grade it waits for is the one autograder
     * would give.
     *
     * Where the answer is no, autograder switched on there would sit waiting
     * for a signal that never comes, graded nobody, and said nothing.
     *
     * @param \cm_info $cm
     * @return bool
     */
    public static function can_tell_when_done(\cm_info $cm): bool {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $adapter = module_adapter::class_for((string) $cm->modname);

        if ($adapter::knows_submissions()) {
            return true;
        }

        $tracking = (new \completion_info($cm->get_course()))->is_enabled($cm);

        if ($tracking == COMPLETION_TRACKING_MANUAL) {
            return true;
        }

        if ($tracking != COMPLETION_TRACKING_AUTOMATIC) {
            return false;
        }

        if ($cm->completiongradeitemnumber !== null || !empty($cm->completionpassgrade)) {
            return false;
        }

        if (!empty($cm->completionview)) {
            return true;
        }

        // The module's own conditions, read the way core itself reads them:
        // switched on in its custom data, and backed by a completion class.
        $rules = array_filter((array) (((array) $cm->customdata)['customcompletionrules'] ?? []));

        return $rules !== [] && \core_completion\activity_custom_completion::get_cm_completion_class($cm->modname) !== null;
    }

    /**
     * Whether, and when, the student has done the activity.
     *
     * Where the activity tracks completion, completion is the answer: the
     * teacher has said what "done" means there, and handing something in is
     * not it until they say so. A forum that asks for three replies is not
     * done by opening one discussion — and the first post of a discussion is
     * a post like any other, so without this it counted as handing it in.
     * Where the activity tracks no completion, handing it in is the answer.
     *
     * Except for the conditions only a grade can meet. "Receive a grade" and
     * "receive a passing grade" can never be met before somebody grades the
     * student, and that somebody is autograder: counted, they would hold back
     * the very grade that meets them, and the student would never be graded.
     * So they are left out, and an activity whose completion asks for nothing
     * else is judged as though it tracked none. A quiz's "passing grade, or
     * all attempts used" is left out with them: a student with an essay
     * waiting and attempts to spare can meet it only by passing.
     *
     * A completion that is not complete is not a completion either: a student
     * who ticks a box and unticks it has undone it, and the decision that
     * followed has to go with it.
     *
     * @param \cm_info|\stdClass $cm
     * @param module_adapter $adapter
     * @param int $userid
     * @return array{0: int|null, 1: int|null} When they completed it and when
     *         they handed it in, as due_date_calculator::calculate() takes
     *         them — both null when they have not done it yet.
     */
    private static function engagement(\cm_info|\stdClass $cm, module_adapter $adapter, int $userid): array {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $cminfo = get_fast_modinfo($cm->course, $userid)->get_cm((int) $cm->id);
        $completion = \core_completion\cm_completion_details::get_instance($cminfo, $userid);

        if (!$completion->has_completion()) {
            return [null, $adapter->submitted_at($userid)];
        }

        if ($completion->is_overall_complete()) {
            return [$completion->get_timemodified() ?: $adapter->submitted_at($userid), null];
        }

        // Only the student can tick manual completion, and they have not.
        if ($completion->is_manual()) {
            return [null, null];
        }

        $conditions = array_diff_key($completion->get_details(), array_flip(self::GRADE_CONDITIONS));

        if ($conditions === []) {
            return [null, $adapter->submitted_at($userid)];
        }

        foreach ($conditions as $condition) {
            if ((int) $condition->status === COMPLETION_INCOMPLETE) {
                return [null, null];
            }
        }

        // Every condition the student can meet is met; only the grade is left.
        // Core does not rewrite the completion record while it stays
        // incomplete, so the hand-in is the better clock where there is one.
        return [$adapter->submitted_at($userid) ?? ($completion->get_timemodified() ?: null), null];
    }

    /**
     * The groups this student belongs to in the activity's course.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return int[]
     */
    public static function group_ids(\cm_info|\stdClass $cm, int $userid): array {
        $groups = groups_get_all_groups($cm->course, $userid);

        return array_map('intval', array_keys($groups));
    }

    /**
     * Whether the student is still actively enrolled where this activity is.
     *
     * Completion at one instant says nothing about a fortnight later, when
     * the grade is actually due — so this is checked again at grading time,
     * never assumed from when the decision was made.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return bool
     */
    public static function is_still_enrolled(\cm_info|\stdClass $cm, int $userid): bool {
        return is_enrolled(\context_course::instance($cm->course), $userid, '', true);
    }

    /**
     * Whether a user is someone this activity grades at all.
     *
     * The rule is the gradebook's own: an active enrolment and one of the
     * site's graded roles ($CFG->gradebookroles), assigned in the course or
     * above it — exactly the people the grader report lists. On top of that,
     * never somebody who grades the activity themselves.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return bool
     */
    public static function is_gradable_student(\cm_info|\stdClass $cm, int $userid): bool {
        global $CFG, $DB;

        $context = \context_course::instance($cm->course);

        if (!is_enrolled($context, $userid, '', true)) {
            return false;
        }

        $roleids = array_filter(array_map('intval', explode(',', (string) ($CFG->gradebookroles ?? ''))));

        if (!$roleids) {
            return false;
        }

        [$rolesql, $roleparams] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'grbr');
        [$contextsql, $contextparams] = $DB->get_in_or_equal(
            $context->get_parent_context_ids(true),
            SQL_PARAMS_NAMED,
            'ctx'
        );

        $hasgradedrole = $DB->record_exists_select(
            'role_assignments',
            "userid = :userid AND roleid $rolesql AND contextid $contextsql",
            ['userid' => $userid] + $roleparams + $contextparams
        );

        if (!$hasgradedrole) {
            return false;
        }

        return !grader_picker::grades_this_module($cm, $userid);
    }
}
