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
 * The rubric autograder is configured against, and the guard that stops it
 * grading on one that no longer matches.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\advanced_grading
 */
final class advanced_grading_test extends \advanced_testcase {
    /** @var \stdClass The assignment graded by a rubric. */
    private \stdClass $cm;

    /** @var array The rubric's criteria, as the plugin normalises them. */
    private array $criteria;

    /**
     * An assignment with a two-criterion rubric, ready to be marked.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $this->cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->define_rubric();
        $this->criteria = advanced_grading::criteria($this->cm);
    }

    /**
     * The page hands core's own grading element a working instance, and does
     * not leave a grading_instances row behind for having drawn a form.
     */
    public function test_template_instance_is_usable_and_not_persisted(): void {
        global $DB;

        $before = $DB->count_records('grading_instances');
        $instance = advanced_grading::template_instance($this->cm);

        $this->assertInstanceOf(\gradingform_rubric_instance::class, $instance);
        $this->assertEquals(
            $before,
            $DB->count_records('grading_instances'),
            'Drawing the form is not an act of grading, so it writes nothing.'
        );
        $this->assertTrue($instance->validate_grading_element(['criteria' => $this->full_filling()]));
    }

    /**
     * An activity with no rubric defined has no form to offer.
     */
    public function test_template_instance_is_null_without_a_definition(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $assign = $generator->create_module('assign', ['course' => $course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        $this->assertNull(advanced_grading::template_instance($cm));
    }

    /**
     * What the grading element submits is what gets stored, and what gets
     * stored comes back to the element unchanged.
     */
    public function test_the_filling_round_trips(): void {
        $filling = $this->full_filling();
        $json = advanced_grading::encode($filling);

        $this->assertSame($filling, advanced_grading::decode($json));
        $this->assertTrue(advanced_grading::filling_is_current($this->cm, $json));
    }

    /**
     * A filling that points at a level the rubric no longer has is stale — the
     * case a restore always produces, since restoring a grading form renumbers
     * every criterion.
     */
    public function test_a_filling_pointing_at_a_missing_level_is_stale(): void {
        $filling = $this->full_filling();
        $firstcriterion = array_key_first($filling);
        $filling[$firstcriterion]['levelid'] = 999999;

        $this->assertFalse(
            advanced_grading::filling_is_current($this->cm, advanced_grading::encode($filling))
        );
    }

    /**
     * A filling that leaves a criterion unanswered is stale too — a rubric
     * gains a criterion and what autograder was told no longer covers it.
     */
    public function test_a_filling_missing_a_criterion_is_stale(): void {
        $filling = $this->full_filling();
        array_shift($filling);

        $this->assertFalse(
            advanced_grading::filling_is_current($this->cm, advanced_grading::encode($filling))
        );
    }

    /**
     * Nothing stored at all is not a usable filling either.
     */
    public function test_an_empty_filling_is_not_current(): void {
        $this->assertFalse(advanced_grading::filling_is_current($this->cm, null));
        $this->assertFalse(advanced_grading::filling_is_current($this->cm, '{"criteria":{}}'));
    }

    /**
     * Marks the first level of every criterion.
     *
     * @return array<int, array<string, mixed>>
     */
    private function full_filling(): array {
        $filling = [];

        foreach ($this->criteria as $criterionid => $criterion) {
            $filling[$criterionid] = [
                'levelid' => (int) array_key_first($criterion['levels']),
                'remark' => '',
            ];
        }

        return $filling;
    }

    /**
     * Puts a two-criterion rubric on the assignment and makes it ready.
     */
    private function define_rubric(): void {
        $context = \context_module::instance((int) $this->cm->id);

        // Two steps, as core itself does them: the area is told which method
        // it uses, and then the method's own generator writes the definition.
        $this->getDataGenerator()
            ->get_plugin_generator('core_grading')
            ->create_instance($context, 'mod_assign', 'submissions', 'rubric');

        $this->getDataGenerator()
            ->get_plugin_generator('gradingform_rubric')
            ->create_instance($context, 'mod_assign', 'submissions', 'Test rubric', 'For autograder', [
                'Argument' => ['Absent' => 0, 'Present' => 5],
                'Evidence' => ['Absent' => 0, 'Present' => 5],
            ]);
    }
}
