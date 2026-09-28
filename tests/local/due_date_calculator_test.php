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

/**
 * The grading rules, in isolation from Moodle entirely.
 *
 * @package local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_autograder\local;

use local_autograder\local\decision\due_date_calculator;

/**
 * Exercises the grading rules directly, one behaviour per test.
 *
 * @package local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \local_autograder\local\decision\due_date_calculator
 */
final class due_date_calculator_test extends \basic_testcase {
    /** @var int An arbitrary but fixed instant, for readable fixtures. */
    private const COMPLETED_AT = 1_700_000_000;

    /** @var int A submission a little before that instant. */
    private const SUBMITTED_AT = 1_699_990_000;

    public function test_base_case_grades_off_the_completion_instant(): void {
        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, null, null, []);

        $this->assertSame([
            'baselineduedate' => self::COMPLETED_AT,
            'duedatereason' => due_date_calculator::REASON_COMPLETION,
        ], $result);
    }

    public function test_the_submission_instant_stands_in_where_completion_is_not_tracked(): void {
        $result = due_date_calculator::calculate(null, self::SUBMITTED_AT, null, null, []);

        $this->assertSame([
            'baselineduedate' => self::SUBMITTED_AT,
            'duedatereason' => due_date_calculator::REASON_SUBMISSION,
        ], $result);
    }

    public function test_completion_is_preferred_over_submission_when_both_are_known(): void {
        $result = due_date_calculator::calculate(self::COMPLETED_AT, self::SUBMITTED_AT, null, null, []);

        $this->assertSame(self::COMPLETED_AT, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $result['duedatereason']);
    }

    public function test_close_date_wins_over_the_completion_instant(): void {
        $closedate = self::COMPLETED_AT + 500_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, $closedate, null, []);

        $this->assertSame($closedate, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_DUEDATE, $result['duedatereason']);
    }

    public function test_close_date_wins_over_the_submission_instant_too(): void {
        $closedate = self::SUBMITTED_AT + 500_000;

        $result = due_date_calculator::calculate(null, self::SUBMITTED_AT, $closedate, null, []);

        $this->assertSame($closedate, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_DUEDATE, $result['duedatereason']);
    }

    public function test_a_user_override_wins_over_the_close_date(): void {
        $closedate = self::COMPLETED_AT + 500_000;
        $useroverride = self::COMPLETED_AT + 900_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, $closedate, $useroverride, [$closedate]);

        $this->assertSame($useroverride, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_USER_OVERRIDE, $result['duedatereason']);
    }

    public function test_a_user_override_applies_to_a_submission_only_student_as_well(): void {
        $useroverride = self::SUBMITTED_AT + 900_000;

        $result = due_date_calculator::calculate(null, self::SUBMITTED_AT, null, $useroverride, []);

        $this->assertSame($useroverride, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_USER_OVERRIDE, $result['duedatereason']);
    }

    public function test_a_group_override_wins_over_the_close_date_when_there_is_no_user_override(): void {
        $closedate = self::COMPLETED_AT + 500_000;
        $groupoverride = self::COMPLETED_AT + 700_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, $closedate, null, [$groupoverride]);

        $this->assertSame($groupoverride, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_GROUP_OVERRIDE, $result['duedatereason']);
    }

    public function test_the_most_permissive_group_override_applies_when_several_groups_match(): void {
        $earlier = self::COMPLETED_AT + 400_000;
        $later = self::COMPLETED_AT + 800_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, null, null, [$earlier, $later]);

        $this->assertSame($later, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_GROUP_OVERRIDE, $result['duedatereason']);
    }

    public function test_a_user_override_wins_even_against_a_more_permissive_group_override(): void {
        $useroverride = self::COMPLETED_AT + 100_000;
        $laterthanuseroverride = self::COMPLETED_AT + 999_999;

        $result = due_date_calculator::calculate(
            self::COMPLETED_AT,
            null,
            null,
            $useroverride,
            [$laterthanuseroverride],
        );

        $this->assertSame($useroverride, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_USER_OVERRIDE, $result['duedatereason']);
    }

    public function test_nothing_is_calculated_without_a_completion_or_a_submission(): void {
        $this->assertNull(due_date_calculator::calculate(null, null, time(), time(), [time()]));
    }

    /**
     * An override can take the deadline away rather than move it, and Moodle
     * writes that as a zero. Read as "no override" it would hand the student
     * back the very deadline they were excused from.
     */
    public function test_a_user_override_that_lifts_the_deadline_leaves_no_close_date(): void {
        $closedate = self::COMPLETED_AT + 500_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, $closedate, 0, []);

        $this->assertSame(self::COMPLETED_AT, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $result['duedatereason']);
    }

    /**
     * And it settles the question on its own, group exceptions included —
     * otherwise a group's date would close an activity the student was
     * personally excused from.
     */
    public function test_a_lifted_user_override_wins_over_a_group_override_date(): void {
        $groupoverride = self::COMPLETED_AT + 900_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, null, 0, [$groupoverride]);

        $this->assertSame(self::COMPLETED_AT, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $result['duedatereason']);
    }

    /**
     * One group with no deadline lifts it, however many others name a date.
     * The most permissive answer wins, and no deadline is the most permissive
     * there is — the same way core combines them.
     */
    public function test_one_group_without_a_deadline_lifts_it_for_every_group(): void {
        $closedate = self::COMPLETED_AT + 100_000;
        $othergroup = self::COMPLETED_AT + 200_000;

        $result = due_date_calculator::calculate(self::COMPLETED_AT, null, $closedate, null, [$othergroup, 0]);

        $this->assertSame(self::COMPLETED_AT, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $result['duedatereason']);
    }

    /**
     * A student with no deadline who only submitted is counted from the
     * submission, the one instant left that is about them.
     */
    public function test_a_lifted_deadline_falls_back_to_the_submission_where_there_is_no_completion(): void {
        $result = due_date_calculator::calculate(null, self::SUBMITTED_AT, self::COMPLETED_AT, 0, []);

        $this->assertSame(self::SUBMITTED_AT, $result['baselineduedate']);
        $this->assertSame(due_date_calculator::REASON_SUBMISSION, $result['duedatereason']);
    }

    public function test_scheduled_grade_time_adds_the_delay(): void {
        $this->assertSame(
            self::COMPLETED_AT + 864_000,
            due_date_calculator::scheduled_grade_time(self::COMPLETED_AT, 864_000),
        );
    }

    public function test_zero_is_a_real_delay_not_a_missing_one(): void {
        $this->assertSame(
            self::COMPLETED_AT,
            due_date_calculator::scheduled_grade_time(self::COMPLETED_AT, 0),
        );
    }
}
