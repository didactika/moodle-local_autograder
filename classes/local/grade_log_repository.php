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

namespace local_autograder\local;

/**
 * The audit trail of every grading attempt (plan.md §6).
 *
 * Written on success and on failure alike, and kept independently of the
 * decision it came from: a row here outlives the decision once retention
 * purges it, which is what makes "why did this student get this grade in
 * March" answerable in June.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grade_log_repository {
    /** @var string Autograder posted the grade. */
    public const OUTCOME_GRADED = 'graded';

    /** @var string Autograder tried and could not. */
    public const OUTCOME_FAILED = 'failed';

    /** @var string Autograder decided not to — unsupported grade type, a person's grade already there. */
    public const OUTCOME_SKIPPED = 'skipped';

    /** @var string The decision was called off before it came due. */
    public const OUTCOME_CANCELLED = 'cancelled';

    /**
     * Records one attempt.
     *
     * @param \stdClass $decision The decision row (id, cmid, courseid, userid).
     * @param string $outcome One of the OUTCOME_* constants.
     * @param string $message Human-readable detail.
     * @param int|null $graderid The teacher it was posted as, when there was one.
     * @param float|null $gradevalue The grade actually posted, when one was.
     * @return int The new row id.
     */
    public static function record(
        \stdClass $decision,
        string $outcome,
        string $message = '',
        ?int $graderid = null,
        ?float $gradevalue = null,
    ): int {
        global $DB;

        return $DB->insert_record('local_autograder_grade_log', (object) [
            'decisionid' => $decision->id ?? null,
            'cmid' => $decision->cmid,
            'courseid' => $decision->courseid,
            'userid' => $decision->userid,
            'outcome' => $outcome,
            'graderid' => $graderid,
            'gradevalue' => $gradevalue,
            'message' => $message,
            'timecreated' => time(),
        ]);
    }

    /**
     * The attempts recorded for one student in one module, newest first.
     *
     * @param int $cmid
     * @param int $userid
     * @return \stdClass[]
     */
    public static function for_cm_user(int $cmid, int $userid): array {
        global $DB;

        return $DB->get_records(
            'local_autograder_grade_log',
            ['cmid' => $cmid, 'userid' => $userid],
            'timecreated DESC',
        );
    }

    /**
     * Removes an activity's whole grading log.
     *
     * @param int $cmid
     */
    public static function delete_for_cm(int $cmid): void {
        global $DB;

        $DB->delete_records('local_autograder_grade_log', ['cmid' => $cmid]);
    }

    /**
     * Removes a course's whole grading log.
     *
     * @param int $courseid
     */
    public static function delete_for_course(int $courseid): void {
        global $DB;

        $DB->delete_records('local_autograder_grade_log', ['courseid' => $courseid]);
    }

    /**
     * Deletes log rows older than the given instant — what the retention
     * task walks (plan.md §9).
     *
     * @param int $before Unix timestamp.
     * @return int Rows deleted.
     */
    public static function purge_before(int $before): int {
        global $DB;

        $count = $DB->count_records_select('local_autograder_grade_log', 'timecreated < ?', [$before]);
        $DB->delete_records_select('local_autograder_grade_log', 'timecreated < ?', [$before]);

        return $count;
    }
}
