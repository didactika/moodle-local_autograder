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

namespace local_autograder\task;

use local_autograder\event\grading_failed;
use local_autograder\event\student_graded;
use local_autograder\local\config_repository;
use local_autograder\local\decision_planner;
use local_autograder\local\decision_repository;
use local_autograder\local\grade_log_repository;
use local_autograder\local\grader_picker;
use local_autograder\local\module\module_adapter;

/**
 * Grades one student, at the moment their grade came due.
 *
 * Scheduled for the exact instant rather than swept for periodically, so a
 * change of mind — an extension, a withdrawn submission, a teacher grading by
 * hand — is acted on immediately by moving or cancelling this task, instead of
 * being noticed some minutes later (plan.md D1).
 *
 * Nothing it was told when it was queued is trusted: every condition is read
 * again here, because days may have passed.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class grade_student extends \core\task\adhoc_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:grade_student', 'local_autograder');
    }

    /**
     * Re-checks everything, then grades — or decides not to.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $decision = decision_repository::get((int) ($data->decisionid ?? 0));

        if (!$decision || $decision->status !== decision_repository::STATUS_PENDING) {
            // Settled while this was waiting its turn. Nothing to do.
            return;
        }

        $cm = get_coursemodule_from_id('', (int) $decision->cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            decision_repository::cancel($decision, 'modulegone');

            return;
        }

        $config = config_repository::get_for_cm((int) $decision->cmid);

        if (!$config || empty($config->enabled)) {
            decision_repository::cancel($decision, 'autograderoff');

            return;
        }

        if (!decision_planner::is_still_enrolled($cm, (int) $decision->userid)) {
            decision_repository::cancel($decision, 'unenrolled');

            return;
        }

        $plan = decision_planner::plan($cm, $config, (int) $decision->userid);

        if ($plan === null) {
            decision_repository::cancel($decision, 'nolongerengaged');

            return;
        }

        $adapter = module_adapter::for_cm($cm, $config);

        if ($adapter->existing_person_grade((int) $decision->userid) !== null) {
            decision_repository::cancel($decision, 'gradedbyhand', decision_repository::STATUS_MANUAL);

            return;
        }

        if ($plan['scheduledgradetime'] > time()) {
            // An extension arrived after this was queued. Move the decision and
            // come back at the new time rather than grading early.
            decision_repository::move($decision, $plan);

            return;
        }

        $this->grade($decision, $cm, $config, $adapter);
    }

    /**
     * Posts the grade, as the teacher chosen for this student.
     *
     * @param \stdClass $decision
     * @param \stdClass $cm
     * @param \stdClass $config
     * @param module_adapter $adapter
     */
    private function grade(
        \stdClass $decision,
        \stdClass $cm,
        \stdClass $config,
        module_adapter $adapter,
    ): void {
        $userid = (int) $decision->userid;
        $graderid = grader_picker::pick_for((int) $cm->id, $userid);

        if ($graderid === null) {
            $this->fail($decision, $cm, 'no_grader');

            return;
        }

        try {
            $posted = $adapter->write_grade($userid, $graderid);
        } catch (\Throwable $e) {
            // One student's grade failing must not take the rest down with it,
            // so this is recorded and left, not rethrown.
            $this->fail($decision, $cm, 'grade_write_failed', $e->getMessage());

            return;
        }

        decision_repository::settle(
            $decision,
            decision_repository::STATUS_GRADED,
            null,
            $graderid,
            $posted,
        );

        grade_log_repository::record(
            $decision,
            grade_log_repository::OUTCOME_GRADED,
            "graded by {$config->grademethod}",
            $graderid,
            $posted,
        );

        student_graded::create([
            'objectid' => (int) $decision->id,
            'context' => \context_module::instance((int) $cm->id),
            'relateduserid' => $userid,
            'other' => [
                'modname' => $cm->modname,
                'graderid' => $graderid,
                'grademethod' => $config->grademethod,
                'gradevalue' => $posted,
            ],
        ])->trigger();
    }

    /**
     * Records that this one could not be graded, and why.
     *
     * @param \stdClass $decision
     * @param \stdClass $cm
     * @param string $reason
     * @param string $detail
     */
    private function fail(\stdClass $decision, \stdClass $cm, string $reason, string $detail = ''): void {
        decision_repository::settle($decision, decision_repository::STATUS_FAILED, $reason);

        grade_log_repository::record(
            $decision,
            grade_log_repository::OUTCOME_FAILED,
            trim("{$reason} {$detail}"),
        );

        grading_failed::create([
            'objectid' => (int) $decision->id,
            'context' => \context_module::instance((int) $cm->id),
            'relateduserid' => (int) $decision->userid,
            'other' => [
                'modname' => $cm->modname,
                'failurereason' => $reason,
            ],
        ])->trigger();
    }
}
