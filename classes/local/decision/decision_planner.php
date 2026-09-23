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
     * What autograder should do about one student in one activity, right now.
     *
     * @param \cm_info|\stdClass $cm The course module.
     * @param \stdClass $config Its autograder configuration.
     * @param int $userid The student.
     * @return array{baselineduedate: int, duedatereason: string, scheduledgradetime: int}|null
     *         Null when there is nothing to grade: the student has neither
     *         completed nor submitted.
     */
    public static function plan(\cm_info|\stdClass $cm, \stdClass $config, int $userid): ?array {
        $adapter = module_adapter::for_cm($cm, $config);

        $result = due_date_calculator::calculate(
            self::completed_at($cm, $userid),
            $adapter->submitted_at($userid),
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
     * When the student completed the activity, or null when completion is not
     * tracked here or they have not completed it.
     *
     * A completion row whose state is "incomplete" is not a completion — a
     * student who ticks a box and unticks it has undone it, and the decision
     * that followed must go with it.
     *
     * @param \cm_info|\stdClass $cm
     * @param int $userid
     * @return int|null
     */
    public static function completed_at(\cm_info|\stdClass $cm, int $userid): ?int {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $course = get_course($cm->course);
        $completion = new \completion_info($course);

        if (!$completion->is_enabled($cm)) {
            return null;
        }

        $data = $completion->get_data($cm, false, $userid);

        if (empty($data->completionstate) || $data->completionstate == COMPLETION_INCOMPLETE) {
            return null;
        }

        return !empty($data->timemodified) ? (int) $data->timemodified : null;
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
