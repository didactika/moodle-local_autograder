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

namespace local_autograder;

use local_autograder\local\config\config_repository;
use local_autograder\local\decision\decision_repository;
use local_autograder\local\config\eligibility;
use local_autograder\local\grading\grade_log_repository;
use local_autograder\local\module\module_adapter;
use local_autograder\task\catch_up_module;
use local_autograder\task\recalculate_module;

/**
 * What this plugin does when Moodle says something happened.
 *
 * These are about *promptness*, not correctness: every one of them only
 * creates, moves or calls off a decision, and the task that eventually grades
 * re-reads all the conditions for itself anyway. So a missed event delays the
 * right answer, it never produces a wrong one.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * A student completed an activity, or undid it.
     *
     * @param \core\event\course_module_completion_updated $event
     */
    public static function completion_changed(\core\event\course_module_completion_updated $event): void {
        self::reconsider((int) $event->contextinstanceid, (int) $event->relateduserid);
    }

    /**
     * A student submitted something.
     *
     * Matters where completion is not tracked, since then the submission is
     * what autograder counts from.
     *
     * @param \core\event\base $event
     */
    public static function submitted(\core\event\base $event): void {
        $userid = (int) ($event->relateduserid ?: $event->userid);

        self::reconsider((int) $event->contextinstanceid, $userid);
    }

    /**
     * Somebody was graded.
     *
     * If it was a person rather than autograder, autograder stands down: a
     * teacher's judgement is not something to overwrite on a timer.
     *
     * @param \core\event\user_graded $event
     */
    public static function graded(\core\event\user_graded $event): void {
        if (module_adapter::is_writing()) {
            // This is autograder's own grade landing. Reacting to it would
            // mark the decision we are in the middle of completing as though
            // a person had stepped in.
            return;
        }

        $userid = (int) $event->relateduserid;
        $cm = self::course_module_of_grade($event);

        if (!$cm) {
            return;
        }

        $decision = decision_repository::for_cm_user((int) $cm->id, $userid);

        if ($decision && $decision->status === decision_repository::STATUS_PENDING) {
            decision_repository::cancel($decision, 'gradedbyhand', decision_repository::STATUS_MANUAL);
        }
    }

    /**
     * A student left a course, or their enrolment was suspended.
     *
     * @param \core\event\base $event
     */
    public static function enrolment_ended(\core\event\base $event): void {
        self::cancel_users_decisions_in_course(
            (int) $event->courseid,
            (int) $event->relateduserid,
            'unenrolled'
        );
    }

    /**
     * An enrolment changed — which may mean it was suspended.
     *
     * @param \core\event\user_enrolment_updated $event
     */
    public static function enrolment_updated(\core\event\user_enrolment_updated $event): void {
        $courseid = (int) $event->courseid;
        $userid = (int) $event->relateduserid;

        if (is_enrolled(\context_course::instance($courseid), $userid, '', true)) {
            return;
        }

        self::cancel_users_decisions_in_course($courseid, $userid, 'unenrolled');
    }

    /**
     * Group membership changed, which can change whose exception applies.
     *
     * @param \core\event\base $event
     */
    public static function group_membership_changed(\core\event\base $event): void {
        self::recalculate_course((int) $event->courseid);
    }

    /**
     * An exception was added, changed or lifted.
     *
     * @param \core\event\base $event
     */
    public static function override_changed(\core\event\base $event): void {
        self::queue_recalculation((int) $event->contextinstanceid);
    }

    /**
     * An activity's own settings changed, possibly its dates.
     *
     * @param \core\event\course_module_updated $event
     */
    public static function module_updated(\core\event\course_module_updated $event): void {
        self::queue_recalculation((int) $event->contextinstanceid);
    }

    /**
     * An activity was deleted; nothing about it is worth keeping.
     *
     * @param \core\event\course_module_deleted $event
     */
    public static function module_deleted(\core\event\course_module_deleted $event): void {
        $cmid = (int) $event->contextinstanceid;

        decision_repository::delete_for_cm($cmid);
        grade_log_repository::delete_for_cm($cmid);
        config_repository::delete_for_cm($cmid);
    }

    /**
     * A course was reset, which takes its completions, submissions and the
     * students who made them with it.
     *
     * Core offers local plugins no `reset_userdata` callback, so this event is
     * the hook. Everything is cleared and then worked out again from what the
     * course actually holds now, rather than trying to guess from the reset
     * options which decisions survived: a reset that turns out to have changed
     * nothing relevant costs one sweep, not a student's grade.
     *
     * @param \core\event\base $event
     */
    public static function course_reset(\core\event\base $event): void {
        $courseid = (int) $event->courseid;

        decision_repository::delete_for_course($courseid);
        grade_log_repository::delete_for_course($courseid);

        foreach (config_repository::enabled_for_course($courseid) as $config) {
            self::queue_catch_up((int) $config->cmid);
        }
    }

    /**
     * Creates, refreshes or calls off one student's decision on one activity.
     *
     * @param int $cmid
     * @param int $userid
     */
    private static function reconsider(int $cmid, int $userid): void {
        if ($cmid === 0 || $userid === 0) {
            return;
        }

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return;
        }

        $config = config_repository::get_for_cm($cmid);

        if (!$config || empty($config->enabled)) {
            return;
        }

        decision_repository::ensure($cm, $config, $userid);
    }

    /**
     * Calls off whatever one student still had waiting across a course.
     *
     * @param int $courseid
     * @param int $userid
     * @param string $reason
     */
    private static function cancel_users_decisions_in_course(int $courseid, int $userid, string $reason): void {
        global $DB;

        if ($courseid === 0 || $userid === 0) {
            return;
        }

        $decisions = $DB->get_records('local_autograder_decision', [
            'courseid' => $courseid,
            'userid' => $userid,
            'status' => decision_repository::STATUS_PENDING,
        ]);

        foreach ($decisions as $decision) {
            decision_repository::cancel($decision, $reason);
        }
    }

    /**
     * Asks for one activity's waiting decisions to be worked out again.
     *
     * @param int $cmid
     */
    private static function queue_recalculation(int $cmid): void {
        if ($cmid === 0) {
            return;
        }

        $config = config_repository::get_for_cm($cmid);

        // Switched off means nothing is waiting to be moved — turning it off
        // called everything off. Asked here rather than left to the task,
        // which would only load the same row to reach the same answer: an
        // activity whose dates are edited often is a steady trickle of tasks
        // that exist to do nothing.
        //
        // Safe to read now: core saves this plugin's configuration in
        // edit_module_post_actions() before it fires course_module_updated,
        // so what is in the table here is what the teacher just saved.
        if (!$config || empty($config->enabled)) {
            return;
        }

        $task = new recalculate_module();
        $task->set_custom_data((object) ['cmid' => $cmid]);

        // Deduplicated: a burst of edits to one activity earns one pass.
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Asks for one activity's students to be looked at from scratch.
     *
     * @param int $cmid
     */
    private static function queue_catch_up(int $cmid): void {
        $task = new catch_up_module();
        $task->set_custom_data((object) ['cmid' => $cmid]);

        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * Asks for every autograded activity in a course to be worked out again.
     *
     * @param int $courseid
     */
    private static function recalculate_course(int $courseid): void {
        if ($courseid === 0) {
            return;
        }

        foreach (config_repository::enabled_for_course($courseid) as $config) {
            self::queue_recalculation((int) $config->cmid);
        }
    }

    /**
     * Which activity a grading event was about, if it was about one at all —
     * and about the grade autograder itself writes, rather than a different
     * grade of the same activity.
     *
     * The grade item is read from the database by id rather than taken off
     * the event, so that this works the same for a restored event as for a
     * live one.
     *
     * @param \core\event\user_graded $event
     * @return \stdClass|null
     */
    private static function course_module_of_grade(\core\event\user_graded $event): ?\stdClass {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');

        $itemid = (int) ($event->other['itemid'] ?? 0);

        if ($itemid === 0) {
            return null;
        }

        $gradeitem = \grade_item::fetch(['id' => $itemid]);

        if (!$gradeitem || $gradeitem->itemtype !== 'mod') {
            return null;
        }

        $cm = get_coursemodule_from_instance(
            $gradeitem->itemmodule,
            $gradeitem->iteminstance,
            $gradeitem->courseid,
            false,
            IGNORE_MISSING
        );

        if (!$cm) {
            return null;
        }

        // A forum's post ratings are item 0 and its activity grade item 1.
        // Somebody rating a post is not somebody grading the activity, and
        // must not call autograder off.
        if ((int) $gradeitem->itemnumber !== eligibility::grade_itemnumber($cm->modname)) {
            return null;
        }

        return $cm;
    }
}
