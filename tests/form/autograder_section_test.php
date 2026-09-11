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

namespace local_autograder\form;

/**
 * What the activity settings form refuses, and what it lets through.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\form\autograder_section::validate
 */
final class autograder_section_test extends \advanced_testcase {
    /**
     * A grade above what the activity is marked out of is refused, and the
     * message says what the maximum actually is.
     */
    public function test_a_grade_above_the_maximum_is_refused(): void {
        $errors = autograder_section::validate($this->submission(['autograder_grade_point' => 1000]));

        $this->assertArrayHasKey('autograder_grade_point', $errors);
        $this->assertStringContainsString('100', $errors['autograder_grade_point']);
    }

    /**
     * The maximum is read from this very submission, not from what the
     * activity was saved with before: a teacher can lower it and set the
     * autograder grade in one go, and the old maximum must not wave through a
     * grade the activity will refuse a moment later.
     */
    public function test_the_maximum_being_lowered_in_the_same_save_counts(): void {
        $data = $this->submission(['autograder_grade_point' => 70]);
        $data['grade']['modgrade_point'] = 50;

        $errors = autograder_section::validate($data);

        $this->assertArrayHasKey('autograder_grade_point', $errors);
        $this->assertStringContainsString('50', $errors['autograder_grade_point']);
    }

    /**
     * The maximum itself is a legitimate grade.
     *
     * @param mixed $grade
     * @dataProvider acceptable_grade_provider
     */
    public function test_acceptable_grades_are_accepted($grade): void {
        $this->assertSame([], autograder_section::validate(
            $this->submission(['autograder_grade_point' => $grade])
        ));
    }

    /**
     * The grades an activity marked out of 100 should take.
     *
     * @return array<string, array{mixed}>
     */
    public static function acceptable_grade_provider(): array {
        return [
            'the maximum' => [100],
            'below the maximum' => [70],
            'a fraction' => [70.5],
            'zero' => [0],
        ];
    }

    /**
     * A grade that is missing, negative or not a number is refused.
     *
     * @param mixed $grade
     * @dataProvider unacceptable_grade_provider
     */
    public function test_unacceptable_grades_are_refused($grade): void {
        $errors = autograder_section::validate($this->submission(['autograder_grade_point' => $grade]));

        $this->assertArrayHasKey('autograder_grade_point', $errors);
    }

    /**
     * The grades it should refuse.
     *
     * @return array<string, array{mixed}>
     */
    public static function unacceptable_grade_provider(): array {
        return [
            'empty' => [''],
            'negative' => [-5],
            'not a number' => ['abc'],
        ];
    }

    /**
     * Autograder cannot be switched on for an activity that is not graded.
     */
    public function test_an_ungraded_activity_cannot_have_autograder_on(): void {
        $data = $this->submission();
        $data['grade'] = ['modgrade_type' => 'none'];

        $this->assertArrayHasKey('autograder_enabled', autograder_section::validate($data));
    }

    /**
     * The wait has to be whole units, in range, and not negative.
     *
     * @param string $field
     * @param mixed $value
     * @dataProvider unacceptable_delay_provider
     */
    public function test_unacceptable_delays_are_refused(string $field, $value): void {
        $errors = autograder_section::validate($this->submission([$field => $value]));

        $this->assertArrayHasKey($field, $errors);
    }

    /**
     * The waits it should refuse, and which field each belongs to.
     *
     * @return array<string, array{string, mixed}>
     */
    public static function unacceptable_delay_provider(): array {
        return [
            'a fraction of a day' => ['autograder_days', 1.5],
            'negative hours' => ['autograder_hours', -1],
            'hours that belong in days' => ['autograder_hours', 30],
            'minutes that belong in hours' => ['autograder_minutes', 90],
        ];
    }

    /**
     * No wait at all is a legitimate choice — grade the moment the student is
     * due — so it must not be mistaken for an unfilled field.
     */
    public function test_no_delay_is_accepted(): void {
        $this->assertSame([], autograder_section::validate($this->submission([
            'autograder_days' => 0,
            'autograder_hours' => 0,
            'autograder_minutes' => 0,
        ])));
    }

    /**
     * With autograder switched off, nothing in the section is its business.
     */
    public function test_nothing_is_checked_while_autograder_is_off(): void {
        $this->assertSame([], autograder_section::validate($this->submission([
            'autograder_enabled' => 0,
            'autograder_grade_point' => 1000,
            'autograder_hours' => -99,
        ])));
    }

    /**
     * A scale-graded activity has to have an item of that scale chosen.
     */
    public function test_a_scale_graded_activity_needs_an_item_chosen(): void {
        $data = $this->submission();
        $data['grade'] = ['modgrade_type' => 'scale', 'modgrade_scale' => 7];

        $errors = autograder_section::validate($data);
        $this->assertArrayHasKey('autograder_grade_scale_7', $errors);

        $data['autograder_grade_scale_7'] = 2;
        $this->assertSame([], autograder_section::validate($data));
    }

    /**
     * An assignment marked out of 100, with autograder on and a valid grade.
     *
     * @param array $overrides
     * @return array
     */
    private function submission(array $overrides = []): array {
        return $overrides + [
            'modulename' => 'assign',
            'autograder_enabled' => 1,
            'autograder_grade_point' => 70,
            'autograder_days' => 2,
            'autograder_hours' => 0,
            'autograder_minutes' => 0,
            'grade' => ['modgrade_type' => 'point', 'modgrade_point' => 100],
        ];
    }
}
