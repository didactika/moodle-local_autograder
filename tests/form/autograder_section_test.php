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

use local_autograder\local\config\config_repository;

/**
 * What the activity settings form refuses, and what it lets through.
 *
 * The submissions here carry the grade the way the `modgrade` element really
 * sends it — **one number**, positive for the maximum of a point-graded
 * activity, negative for minus the id of a scale — which is what
 * `moodleform_mod::validation()` itself reads when it checks the grade to
 * pass. Getting that shape wrong is how this section once validated nothing
 * at all while appearing to pass its tests, so
 * {@see test_the_grade_field_really_is_one_number} pins it to core rather
 * than to an assumption.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\form\autograder_section::validate
 */
final class autograder_section_test extends \advanced_testcase {
    /**
     * The assumption every other test here rests on, checked against the
     * element core actually builds rather than against this test's own idea
     * of it.
     */
    public function test_the_grade_field_really_is_one_number(): void {
        global $CFG, $COURSE;

        require_once($CFG->libdir . '/formslib.php');

        $this->resetAfterTest();

        $COURSE = $this->getDataGenerator()->create_course();
        $scale = $this->getDataGenerator()->create_scale(['scale' => 'Poor,Fair,Good']);

        $mform = new \MoodleQuickForm('probe', 'post', '');
        $element = $mform->addElement('modgrade', 'grade', 'Grade');

        // The exportValue() call takes its submission by reference, so it
        // needs a variable rather than a literal.
        $submitted = ['grade' => ['modgrade_type' => 'scale', 'modgrade_scale' => $scale->id]];
        $exported = $element->exportValue($submitted, true);

        $this->assertIsNotArray($exported['grade'], 'The three controls are collapsed into one value.');
        $this->assertEquals(-$scale->id, $exported['grade'], 'A scale travels as minus its id.');
    }

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
        $errors = autograder_section::validate($this->submission([
            'autograder_grade_point' => 70,
            'grade' => 50,
        ]));

        $this->assertArrayHasKey('autograder_grade_point', $errors);
        $this->assertStringContainsString('50', $errors['autograder_grade_point']);
    }

    /**
     * Grades the activity will accept are accepted.
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
            'barely over' => [100.001],
        ];
    }

    /**
     * Autograder cannot be switched on for an activity that is not graded.
     */
    public function test_an_ungraded_activity_cannot_have_autograder_on(): void {
        $errors = autograder_section::validate($this->submission(['grade' => 0]));

        $this->assertArrayHasKey('autograder_enabled', $errors);
    }

    /**
     * A scale-graded activity has to have an item of that scale chosen.
     */
    public function test_a_scale_graded_activity_needs_an_item_chosen(): void {
        $this->resetAfterTest();

        $scale = $this->getDataGenerator()->create_scale(['scale' => 'Poor,Fair,Good']);
        $data = $this->submission(['grade' => -$scale->id]);
        $field = "autograder_grade_scale_{$scale->id}";

        $this->assertArrayHasKey($field, autograder_section::validate($data));

        $data[$field] = 2;
        $this->assertSame([], autograder_section::validate($data));
    }

    /**
     * An item that belongs to a different scale is refused — the teacher
     * changed which scale the activity uses in this same save.
     */
    public function test_an_item_beyond_the_scale_is_refused(): void {
        $this->resetAfterTest();

        $scale = $this->getDataGenerator()->create_scale(['scale' => 'Poor,Fair,Good']);
        $data = $this->submission([
            'grade' => -$scale->id,
            "autograder_grade_scale_{$scale->id}" => 9,
        ]);

        $this->assertArrayHasKey("autograder_grade_scale_{$scale->id}", autograder_section::validate($data));
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
            'negative days' => ['autograder_days', -1],
            'negative hours' => ['autograder_hours', -1],
            'hours that belong in days' => ['autograder_hours', 30],
            'minutes that belong in hours' => ['autograder_minutes', 90],
            'a delay that is not a number' => ['autograder_days', 'soon'],
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
     * Autograder cannot be switched on for a rubric it has not been told what
     * to mark on: that configuration says "enabled" and can never post a
     * grade.
     */
    public function test_a_rubric_with_nothing_chosen_cannot_have_autograder_on(): void {
        $cm = $this->rubric_activity();
        $data = $this->submission([
            'coursemodule' => $cm->id,
            'advancedgradingmethod_submissions' => 'rubric',
        ]);

        $errors = autograder_section::validate($data);

        $this->assertArrayHasKey('autograder_enabled', $errors);
        $this->assertStringContainsString('advanced.php', $errors['autograder_enabled']);
    }

    /**
     * Once the levels are chosen, it is allowed — and the plain grade field
     * is not held against it, because a rubric replaces it.
     */
    public function test_a_rubric_with_levels_chosen_is_accepted(): void {
        $cm = $this->rubric_activity();
        $this->store_rubric_filling($cm);

        $data = $this->submission([
            'coursemodule' => $cm->id,
            'advancedgradingmethod_submissions' => 'rubric',
            'autograder_grade_point' => 1000,
        ]);

        $this->assertSame([], autograder_section::validate($data));
    }

    /**
     * A rubric that does not exist yet is not held against the teacher: they
     * cannot have chosen levels on a definition they have not written.
     */
    public function test_switching_to_a_rubric_that_is_not_defined_yet_is_allowed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->assertSame([], autograder_section::validate($this->submission([
            'coursemodule' => $cm->id,
            'advancedgradingmethod_submissions' => 'rubric',
        ])));
    }

    /**
     * Ticking the box is not enough on its own: saved with nothing chosen on
     * the rubric, autograder comes back switched off.
     *
     * Validation lets this through on purpose — a rubric the teacher has not
     * written yet is not held against them, and on a brand new activity there
     * is nothing to check at all. It is the save that has the module, its
     * grading method and its definition all in front of it, and so it is the
     * save that refuses to store a configuration that could only ever fail.
     */
    public function test_a_rubric_with_nothing_chosen_is_saved_switched_off(): void {
        $cm = $this->rubric_activity();

        autograder_section::save((object) [
            'modulename' => 'assign',
            'coursemodule' => (int) $cm->id,
            'course' => (int) $cm->course,
            'autograder_enabled' => 1,
            'autograder_days' => 0,
            'autograder_hours' => 0,
            'autograder_minutes' => 0,
        ]);

        $config = config_repository::get_for_cm((int) $cm->id);

        $this->assertNotFalse($config, 'The configuration is still saved, so the teacher finds it.');
        $this->assertSame('rubric', $config->grademethod);
        $this->assertEquals(0, (int) $config->enabled, 'But switched off: there is nothing to mark.');
    }

    /**
     * And once the levels are chosen, the same save switches it on and keeps
     * what it was told to mark.
     */
    public function test_a_rubric_with_levels_chosen_is_saved_switched_on(): void {
        $cm = $this->rubric_activity();
        $this->store_rubric_filling($cm);

        $stored = config_repository::get_for_cm((int) $cm->id)->advancedgrading;

        autograder_section::save((object) [
            'modulename' => 'assign',
            'coursemodule' => (int) $cm->id,
            'course' => (int) $cm->course,
            'autograder_enabled' => 1,
            'autograder_days' => 0,
            'autograder_hours' => 0,
            'autograder_minutes' => 0,
        ]);

        $config = config_repository::get_for_cm((int) $cm->id);

        $this->assertEquals(1, (int) $config->enabled);
        $this->assertSame($stored, $config->advancedgrading, 'A save elsewhere on the form does not wipe it.');
    }

    /**
     * An assignment with a rubric defined but nothing chosen on it yet.
     *
     * @return \stdClass The course module.
     */
    private function rubric_activity(): \stdClass {
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);
        $context = \context_module::instance((int) $cm->id);

        $generator->get_plugin_generator('core_grading')
            ->create_instance($context, 'mod_assign', 'submissions', 'rubric');
        $generator->get_plugin_generator('gradingform_rubric')
            ->create_instance($context, 'mod_assign', 'submissions', 'Test rubric', 'For autograder', [
                'Argument' => ['Absent' => 0, 'Present' => 5],
            ]);

        return $cm;
    }

    /**
     * Marks the first level of every criterion and stores it as autograder's
     * configuration for the activity.
     *
     * @param \stdClass $cm
     */
    private function store_rubric_filling(\stdClass $cm): void {
        $filling = [];

        foreach (\local_autograder\local\grading\advanced_grading::criteria($cm) as $criterionid => $criterion) {
            $filling[$criterionid] = [
                'levelid' => (int) array_key_first($criterion['levels']),
                'remark' => '',
            ];
        }

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $cm->course,
            true,
            'rubric',
            null,
            \local_autograder\local\grading\advanced_grading::encode($filling),
            0,
            2
        );
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
            'coursemodule' => 0,
            'autograder_enabled' => 1,
            'autograder_grade_point' => 70,
            'autograder_days' => 2,
            'autograder_hours' => 0,
            'autograder_minutes' => 0,
            // One number, exactly as modgrade submits it.
            'grade' => 100,
        ];
    }
}
