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

use local_autograder\local\config\config_repository;
use local_autograder\local\decision\decision_planner;
use mod_quiz\quiz_attempt;

/**
 * A quiz with an essay: no grade of its own until the essay is marked, which
 * is the one place autograder has anything to give it.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\decision\decision_planner
 */
final class quiz_test extends \advanced_testcase {
    /** @var \stdClass */
    private \stdClass $course;

    /** @var \stdClass The teacher autograder grades as. */
    private \stdClass $teacher;

    /** @var \stdClass */
    private \stdClass $student;

    /**
     * A course with a teacher and a student.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();

        require_once($CFG->dirroot . '/mod/quiz/locallib.php');

        $this->resetAfterTest();
        set_config('enablecompletion', 1);

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['enablecompletion' => 1]);
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');
    }

    /**
     * "Passing grade, or all attempts used" cannot be met with attempts to
     * spare and an essay nobody has marked, so it must not hold the student
     * back: they submitted, and that is what counts.
     */
    public function test_a_passing_grade_or_all_attempts_condition_does_not_hold_the_student_back(): void {
        $quiz = $this->quiz_with_essays(1, [
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionusegrade' => 1,
            'completionpassgrade' => 1,
            'completionattemptsexhausted' => 1,
            'gradepass' => 50,
        ]);
        $this->attempt($quiz, 'An essay.');

        $cm = get_fast_modinfo($this->course)->get_cm((int) $quiz->cmid);

        $this->assertNotNull(decision_planner::plan($cm, $this->configure($quiz), (int) $this->student->id));
    }

    /**
     * A quiz out of 100 made of essays worth a mark each.
     *
     * @param int $essays
     * @param array $settings
     * @return \stdClass The quiz, with `cmid`.
     */
    private function quiz_with_essays(int $essays, array $settings = []): \stdClass {
        $quiz = $this->getDataGenerator()->create_module('quiz', $settings + [
            'course' => $this->course->id,
            'grade' => 100,
            'questionsperpage' => 0,
        ]);

        $questions = $this->getDataGenerator()->get_plugin_generator('core_question');
        $category = $questions->create_question_category();

        for ($i = 0; $i < $essays; $i++) {
            $essay = $questions->create_question('essay', null, ['category' => $category->id]);
            quiz_add_quiz_question($essay->id, $quiz, 0, 1);
        }

        // What the quiz's edit page does once its questions change.
        \mod_quiz\quiz_settings::create($quiz->id)->get_grade_calculator()->recompute_quiz_sumgrades();

        return $quiz;
    }

    /**
     * Autograder switched on for the quiz, giving 70 straight away.
     *
     * @param \stdClass $quiz
     * @return \stdClass The configuration.
     */
    private function configure(\stdClass $quiz): \stdClass {
        config_repository::upsert_for_cm(
            (int) $quiz->cmid,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            0,
            (int) $this->teacher->id,
        );

        return config_repository::get_for_cm((int) $quiz->cmid);
    }

    /**
     * The student makes an attempt and submits it, answering every essay
     * with the same text.
     *
     * @param \stdClass $quiz
     * @param string $answer
     * @return int The attempt's id.
     */
    private function attempt(\stdClass $quiz, string $answer): int {
        $this->setUser($this->student);

        $quizgenerator = $this->getDataGenerator()->get_plugin_generator('mod_quiz');
        $attempt = $quizgenerator->create_attempt($quiz->id, $this->student->id);
        $slots = quiz_attempt::create($attempt->id)->get_slots();
        $responses = array_fill_keys($slots, $answer);
        $quizgenerator->submit_responses($attempt->id, $responses, false, true);

        $this->setAdminUser();

        return (int) $attempt->id;
    }
}
