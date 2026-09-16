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

use local_autograder\event\decision_cancelled;
use local_autograder\local\grading\grade_log_repository;
use local_autograder\task\grade_student;

/**
 * One row per (activity, student) autograder is watching, and the adhoc task
 * that will grade it when the time comes.
 *
 * Scheduling lives here rather than in the tasks because the row and its task
 * have to move together: a due date that changes has to move the task with
 * it, and a decision that is called off has to take its task with it, or the
 * task would fire on data that no longer says what it said.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class decision_repository {
    /** @var string Waiting for its moment to grade. */
    public const STATUS_PENDING = 'pending';

    /** @var string Autograder graded it. */
    public const STATUS_GRADED = 'graded';

    /** @var string A person graded first, so autograder stood down. */
    public const STATUS_MANUAL = 'manual';

    /** @var string Nothing to do: an unsupported grading method, say. */
    public const STATUS_SKIPPED = 'skipped';

    /** @var string Called off before it came due. */
    public const STATUS_CANCELLED = 'cancelled';

    /** @var string Autograder tried and could not. */
    public const STATUS_FAILED = 'failed';

    /**
     * The states a decision never leaves.
     *
     * @return string[]
     */
    public static function terminal_statuses(): array {
        return [
            self::STATUS_GRADED,
            self::STATUS_MANUAL,
            self::STATUS_SKIPPED,
            self::STATUS_CANCELLED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * The states that are an answer rather than a circumstance.
     *
     * A decision stops being reconsidered only once somebody's grade is
     * actually on it — autograder's own, or a teacher's. Everything else was
     * settled by something that can stop being true: autograder was switched
     * off and is on again, the student left and came back, there was no
     * eligible teacher and now there is. Treating those as final is how an
     * activity ends up switched on, configured, and quietly grading nobody.
     *
     * @return string[]
     */
    public static function graded_statuses(): array {
        return [
            self::STATUS_GRADED,
            self::STATUS_MANUAL,
        ];
    }

    /**
     * One student's decision for one activity.
     *
     * @param int $cmid
     * @param int $userid
     * @return \stdClass|false
     */
    public static function for_cm_user(int $cmid, int $userid) {
        global $DB;

        return $DB->get_record('local_autograder_decision', ['cmid' => $cmid, 'userid' => $userid]);
    }

    /**
     * One decision by its own id.
     *
     * @param int $id
     * @return \stdClass|false
     */
    public static function get(int $id) {
        global $DB;

        return $DB->get_record('local_autograder_decision', ['id' => $id]);
    }

    /**
     * Every decision still waiting on one activity.
     *
     * @param int $cmid
     * @return \stdClass[]
     */
    public static function pending_for_cm(int $cmid): array {
        global $DB;

        return $DB->get_records('local_autograder_decision', [
            'cmid' => $cmid,
            'status' => self::STATUS_PENDING,
        ]);
    }

    /**
     * Pending decisions that are already due but have no live task behind
     * them — what the safety net looks for.
     *
     * @param int $now
     * @return \stdClass[]
     */
    public static function orphaned_pending(int $now): array {
        global $DB;

        $sql = "SELECT d.*
                  FROM {local_autograder_decision} d
             LEFT JOIN {task_adhoc} t ON t.id = d.adhoctaskid
                 WHERE d.status = :status
                   AND d.scheduledgradetime <= :now
                   AND t.id IS NULL";

        return $DB->get_records_sql($sql, ['status' => self::STATUS_PENDING, 'now' => $now]);
    }

    /**
     * Creates or refreshes the decision for one student, and makes sure a
     * task is waiting at the right moment for it.
     *
     * @param \cm_info|\stdClass $cm
     * @param \stdClass $config
     * @param int $userid
     * @return \stdClass|null The decision, or null when there is nothing to
     *                        grade for this student.
     */
    public static function ensure(\cm_info|\stdClass $cm, \stdClass $config, int $userid): ?\stdClass {
        global $DB;

        $existing = self::for_cm_user((int) $cm->id, $userid);

        if ($existing && in_array($existing->status, self::graded_statuses(), true)) {
            // Somebody's grade is on it — autograder's own or a teacher's —
            // and that is the one thing this never takes back.
            return $existing;
        }

        $plan = decision_planner::plan($cm, $config, $userid);

        if ($plan === null) {
            if ($existing) {
                self::cancel($existing, 'nolongerengaged');
            }

            return null;
        }

        $now = time();

        if ($existing) {
            self::move($existing, $plan);

            return $existing;
        }

        $decision = (object) [
            'cmid' => (int) $cm->id,
            'courseid' => (int) $cm->course,
            'userid' => $userid,
            'status' => self::STATUS_PENDING,
            'baselineduedate' => $plan['baselineduedate'],
            'duedatereason' => $plan['duedatereason'],
            'scheduledgradetime' => $plan['scheduledgradetime'],
            'adhoctaskid' => null,
            'graderid' => null,
            'gradedvalue' => null,
            'failurereason' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $decision->id = $DB->insert_record('local_autograder_decision', $decision);

        self::schedule($decision);

        return $decision;
    }

    /**
     * Moves a decision to a new moment and takes its task with it.
     *
     * Both halves have to happen together: a row saying one thing while a
     * task waits for another is how a student gets graded on a deadline that
     * no longer applies.
     *
     * @param \stdClass $decision Updated in place.
     * @param array $plan As returned by {@see decision_planner::plan()}.
     */
    public static function move(\stdClass $decision, array $plan): void {
        global $DB;

        $decision->baselineduedate = $plan['baselineduedate'];
        $decision->duedatereason = $plan['duedatereason'];
        $decision->scheduledgradetime = $plan['scheduledgradetime'];
        $decision->timemodified = time();

        // A decision that is waiting for a moment is, by definition, pending.
        // One that had been called off is being picked back up here, so the
        // reason it was called off goes with it — otherwise the row would sit
        // at its new date still saying "cancelled", and the task queued for it
        // would look at the status and do nothing.
        $decision->status = self::STATUS_PENDING;
        $decision->failurereason = null;
        $decision->graderid = null;
        $decision->gradedvalue = null;

        $DB->update_record('local_autograder_decision', $decision);

        self::schedule($decision);
    }

    /**
     * Puts a task in the queue for when this decision comes due, replacing
     * whatever was queued for it before.
     *
     * @param \stdClass $decision
     */
    public static function schedule(\stdClass $decision): void {
        global $DB;

        self::unschedule($decision);

        $task = new grade_student();
        $task->set_custom_data((object) ['decisionid' => (int) $decision->id]);
        $task->set_next_run_time((int) $decision->scheduledgradetime);

        $taskid = \core\task\manager::queue_adhoc_task($task);

        $decision->adhoctaskid = $taskid ?: null;
        $DB->set_field('local_autograder_decision', 'adhoctaskid', $decision->adhoctaskid, ['id' => $decision->id]);
    }

    /**
     * Takes this decision's task back out of the queue, if it is still there
     * and has not started running.
     *
     * @param \stdClass $decision
     */
    public static function unschedule(\stdClass $decision): void {
        global $DB;

        if (empty($decision->adhoctaskid)) {
            return;
        }

        // A task already claimed by a runner has `timestarted` set; pulling it
        // out from under itself would be worse than letting it run and find
        // the decision settled.
        $DB->delete_records_select(
            'task_adhoc',
            'id = :id AND timestarted IS NULL',
            ['id' => $decision->adhoctaskid]
        );

        $decision->adhoctaskid = null;
        $DB->set_field('local_autograder_decision', 'adhoctaskid', null, ['id' => $decision->id]);
    }

    /**
     * Moves a decision to a state it will not leave, and stops anything that
     * was queued for it.
     *
     * @param \stdClass $decision
     * @param string $status One of the STATUS_* constants.
     * @param string|null $failurereason Stored when the status is "failed".
     * @param int|null $graderid The teacher it was graded as, when it was.
     * @param float|null $gradedvalue The grade posted, when one was.
     */
    public static function settle(
        \stdClass $decision,
        string $status,
        ?string $failurereason = null,
        ?int $graderid = null,
        ?float $gradedvalue = null,
    ): void {
        global $DB;

        self::unschedule($decision);

        $decision->status = $status;
        $decision->failurereason = $failurereason;
        $decision->graderid = $graderid;
        $decision->gradedvalue = $gradedvalue;
        $decision->timemodified = time();

        $DB->update_record('local_autograder_decision', $decision);
    }

    /**
     * Calls a decision off and says why, both in the log and as an event.
     *
     * @param \stdClass $decision
     * @param string $reason A short slug, e.g. "unenrolled".
     * @param string $status Defaults to cancelled; manual grading uses its own.
     */
    public static function cancel(
        \stdClass $decision,
        string $reason,
        string $status = self::STATUS_CANCELLED,
    ): void {
        if (in_array($decision->status, self::terminal_statuses(), true)) {
            return;
        }

        self::settle($decision, $status, $reason);

        grade_log_repository::record(
            $decision,
            $status === self::STATUS_MANUAL
                ? grade_log_repository::OUTCOME_SKIPPED
                : grade_log_repository::OUTCOME_CANCELLED,
            $reason,
        );

        $cm = get_coursemodule_from_id('', (int) $decision->cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return;
        }

        decision_cancelled::create([
            'objectid' => (int) $decision->id,
            'context' => \context_module::instance((int) $decision->cmid),
            'relateduserid' => (int) $decision->userid,
            'other' => [
                'modname' => $cm->modname,
                'status' => $status,
                'reason' => $reason,
            ],
        ])->trigger();
    }

    /**
     * Calls off everything still waiting on one activity.
     *
     * @param int $cmid
     * @param string $reason
     * @return int How many were called off.
     */
    public static function cancel_all_for_cm(int $cmid, string $reason): int {
        $pending = self::pending_for_cm($cmid);

        foreach ($pending as $decision) {
            self::cancel($decision, $reason);
        }

        return count($pending);
    }

    /**
     * Removes every trace of an activity's decisions — for a module that has
     * been deleted, or a course being reset.
     *
     * @param int $cmid
     */
    public static function delete_for_cm(int $cmid): void {
        global $DB;

        foreach (self::pending_for_cm($cmid) as $decision) {
            self::unschedule($decision);
        }

        $DB->delete_records('local_autograder_decision', ['cmid' => $cmid]);
    }

    /**
     * Removes every decision in a course.
     *
     * @param int $courseid
     */
    public static function delete_for_course(int $courseid): void {
        global $DB;

        $decisions = $DB->get_records('local_autograder_decision', ['courseid' => $courseid]);

        foreach ($decisions as $decision) {
            self::unschedule($decision);
        }

        $DB->delete_records('local_autograder_decision', ['courseid' => $courseid]);
    }

    /**
     * Deletes settled decisions older than the given instant — what the
     * retention task walks.
     *
     * @param int $before
     * @return int Rows deleted.
     */
    public static function purge_settled_before(int $before): int {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal(self::terminal_statuses(), SQL_PARAMS_NAMED);
        $params['before'] = $before;

        $select = "status {$insql} AND timemodified < :before";
        $count = $DB->count_records_select('local_autograder_decision', $select, $params);
        $DB->delete_records_select('local_autograder_decision', $select, $params);

        return $count;
    }
}
