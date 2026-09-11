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
 * The one rule this whole plugin exists to apply (plan.md §4): when a student
 * should be graded, and why.
 *
 * Pure domain logic — no `$DB`, no `$CFG`, no Moodle API calls. Every caller
 * (the form's live preview, `grade_student`, `recalculate_module`) reads the
 * same live data and hands it here; this class never reads anything itself,
 * which is exactly what makes it recalculable from scratch on every run
 * instead of trusted stale (plan.md §3's whole point).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class due_date_calculator {
    /** @var string No module close date and no exception applied. */
    public const REASON_COMPLETION = 'completion';

    /** @var string The module's own close date applied, no exception. */
    public const REASON_DUEDATE = 'duedate';

    /** @var string A user-specific exception applied. */
    public const REASON_USER_OVERRIDE = 'useroverride';

    /** @var string A group exception applied (no user exception existed). */
    public const REASON_GROUP_OVERRIDE = 'groupoverride';

    /**
     * Works out the baseline due date for one student in one module, or
     * decides there is nothing to grade yet.
     *
     * Priority: a user override always outranks a group override; either
     * outranks the module's own close date; the module's close date outranks
     * the bare completion instant. All four still require a completion —
     * an exception changes *when* to grade, it never creates or waives that
     * requirement (plan.md §4, rule 4).
     *
     * @param int|null $completedat When the student completed the activity
     *                              (Unix timestamp), or null if they have not.
     * @param int|null $closedate The module's own close date, or null if it
     *                            has none (e.g. `cutoffdate`/`duedate` = 0).
     * @param int|null $useroverridedate The close date from an override
     *                                    targeting this student by user id, or
     *                                    null if there is none.
     * @param int[] $groupoverridedates The close dates from every override
     *                                   targeting a group this student belongs
     *                                   to. Empty if there are none.
     * @return array{baselineduedate: int, duedatereason: string}|null Null
     *         when the student has not completed the activity — no decision
     *         should exist for them at all.
     */
    public static function calculate(
        ?int $completedat,
        ?int $closedate,
        ?int $useroverridedate,
        array $groupoverridedates = [],
    ): ?array {
        if ($completedat === null) {
            return null;
        }

        if ($useroverridedate !== null) {
            return self::result($useroverridedate, self::REASON_USER_OVERRIDE);
        }

        if (!empty($groupoverridedates)) {
            return self::result(max($groupoverridedates), self::REASON_GROUP_OVERRIDE);
        }

        if ($closedate !== null) {
            return self::result($closedate, self::REASON_DUEDATE);
        }

        return self::result($completedat, self::REASON_COMPLETION);
    }

    /**
     * When a decision becomes due to be graded.
     *
     * @param int $baselineduedate As returned by {@see calculate()}.
     * @param int $delayseconds The module's configured processing delay.
     * @return int
     */
    public static function scheduled_grade_time(int $baselineduedate, int $delayseconds): int {
        return $baselineduedate + $delayseconds;
    }

    /**
     * @param int $baselineduedate
     * @param string $reason
     * @return array{baselineduedate: int, duedatereason: string}
     */
    private static function result(int $baselineduedate, string $reason): array {
        return ['baselineduedate' => $baselineduedate, 'duedatereason' => $reason];
    }
}
