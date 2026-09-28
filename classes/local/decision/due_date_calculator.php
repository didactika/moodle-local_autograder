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

/**
 * The one rule this whole plugin exists to apply: when a student
 * should be graded, and why.
 *
 * Pure domain logic — no `$DB`, no `$CFG`, no Moodle API calls. Every caller
 * (the form's live preview, `grade_student`, `recalculate_module`) reads the
 * same live data and hands it here; this class never reads anything itself,
 * which is exactly what makes it recalculable from scratch on every run
 * instead of trusted stale, which is the whole point of it.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class due_date_calculator {
    /** @var string Graded from the completion instant: no close date, no exception. */
    public const REASON_COMPLETION = 'completion';

    /** @var string Graded from the submission instant: completion is not tracked here. */
    public const REASON_SUBMISSION = 'submission';

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
     * Two things have to be true for a grade to be due. The student must have
     * *done* the activity — completed it where completion is tracked, or
     * simply handed it in where it is not — and there has to be an instant to
     * count the wait from. The first is the gate; the second is what this
     * returns.
     *
     * Priority for that instant: a user override always outranks a group
     * override; either outranks the module's own close date; the close date
     * outranks the bare instant the student engaged. An exception changes
     * *when* to grade — it never waives the requirement that they did the
     * activity at all.
     *
     * An override can also take the deadline away rather than move it, which
     * Moodle writes as a zero and this reads the same way. A student with no
     * deadline is counted from the moment they engaged, because that is the
     * only instant left that is about them.
     *
     * @param int|null $completedat When the student completed the activity,
     *                              or null when completion is not tracked here
     *                              or they have not completed it.
     * @param int|null $submittedat When the student handed the activity in, or
     *                              null when they have not, or when the
     *                              activity has no notion of submitting.
     * @param int|null $closedate The module's own close date, or null if it
     *                            has none (e.g. `cutoffdate`/`duedate` = 0).
     * @param int|null $useroverridedate The close date from an override
     *                                    targeting this student by user id;
     *                                    0 where the override lifts the
     *                                    deadline, null where there is none.
     * @param int[] $groupoverridedates The close dates from every override
     *                                   targeting a group this student belongs
     *                                   to, a 0 among them meaning one of
     *                                   those groups has no deadline. Empty if
     *                                   there are none.
     * @return array{baselineduedate: int, duedatereason: string}|null Null
     *         when the student has neither completed nor submitted — no
     *         decision should exist for them at all.
     */
    public static function calculate(
        ?int $completedat,
        ?int $submittedat,
        ?int $closedate,
        ?int $useroverridedate,
        array $groupoverridedates = [],
    ): ?array {
        // Completion is the stronger signal where it is tracked; a submission
        // stands in where it is not.
        $engagedat = $completedat ?? $submittedat;

        if ($engagedat === null) {
            return null;
        }

        $closes = self::effective_close_date($closedate, $useroverridedate, $groupoverridedates);

        if ($closes !== null) {
            return self::result($closes['date'], $closes['reason']);
        }

        return self::result(
            $engagedat,
            $completedat !== null ? self::REASON_COMPLETION : self::REASON_SUBMISSION,
        );
    }

    /**
     * The instant this activity closes for this student, or null when nothing
     * closes it.
     *
     * Null here is not "no override": it is the answer both to an activity
     * with no deadline at all and to a student whose deadline was taken away.
     * The two are the same thing once the exceptions have been read, which is
     * why they are settled together and in one place.
     *
     * @param int|null $closedate
     * @param int|null $useroverridedate
     * @param int[] $groupoverridedates
     * @return array{date: int, reason: string}|null
     */
    private static function effective_close_date(
        ?int $closedate,
        ?int $useroverridedate,
        array $groupoverridedates,
    ): ?array {
        // A user override settles it on its own, group overrides included —
        // whether it names a date or takes the deadline away.
        if ($useroverridedate !== null) {
            return $useroverridedate > 0
                ? ['date' => $useroverridedate, 'reason' => self::REASON_USER_OVERRIDE]
                : null;
        }

        if ($groupoverridedates !== []) {
            // One group with no deadline lifts it, however many others name a
            // date — the same way core combines them, and the only way round
            // that does not take back what a group was granted.
            return in_array(0, $groupoverridedates, true)
                ? null
                : ['date' => max($groupoverridedates), 'reason' => self::REASON_GROUP_OVERRIDE];
        }

        return $closedate !== null && $closedate > 0
            ? ['date' => $closedate, 'reason' => self::REASON_DUEDATE]
            : null;
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
     * Packs a due date and its reason into the shape callers expect.
     *
     * @param int $baselineduedate
     * @param string $reason
     * @return array{baselineduedate: int, duedatereason: string}
     */
    private static function result(int $baselineduedate, string $reason): array {
        return ['baselineduedate' => $baselineduedate, 'duedatereason' => $reason];
    }
}
