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
use local_autograder\local\decision\decision_repository;
use local_autograder\local\grading\grade_log_repository;
use local_autograder\task\grade_student;
use mod_quiz\quiz_attempt;

/**
 * A quiz with an essay: no grade of its own until the essay is marked, which
 * is the one place autograder has anything to give it.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\observer
 * @covers      \local_autograder\local\module\module_adapter
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
     * Once the teacher marks the essay, the quiz's own grade shows again
     * instead of the one autograder gave while it had none.
     */
    public function test_marking_the_essay_gives_the_gradebook_back_to_the_quiz(): void {
        $quiz = $this->quiz_with_essays(1);
        $attemptid = $this->attempt($quiz, 'An essay.');
        $this->autograde($quiz);

        $this->assertEquals(70.0, $this->final_grade($quiz), 'Autograder stands in while the essay waits.');

        $this->mark($attemptid, 1, 0.5);

        $grade = $this->gradebook($quiz);
        $this->assertEquals(50.0, (float) $grade->finalgrade, 'The teacher\'s marking is what shows.');
        $this->assertEmpty($grade->overridden, 'Autograder\'s override is gone.');

        $log = grade_log_repository::for_cm_user((int) $quiz->cmid, (int) $this->student->id);
        $this->assertSame(grade_log_repository::OUTCOME_RELEASED, reset($log)->outcome);
    }

    /**
     * While another essay still waits, the quiz has no total yet, and
     * autograder's grade stays where it is.
     */
    public function test_a_quiz_still_waiting_on_an_essay_keeps_autograders_grade(): void {
        $quiz = $this->quiz_with_essays(2);
        $attemptid = $this->attempt($quiz, 'An essay.');
        $this->autograde($quiz);

        $this->mark($attemptid, 1, 1);

        $this->assertEquals(70.0, $this->final_grade($quiz));
    }

    /**
     * A teacher who overrode the grade themselves after autograder meant it,
     * and the marking does not take it away.
     */
    public function test_a_teachers_own_override_stays(): void {
        global $DB;

        $quiz = $this->quiz_with_essays(1);
        $attemptid = $this->attempt($quiz, 'An essay.');
        $this->autograde($quiz);

        // Autograder settled a while ago; the teacher overrides now.
        $DB->set_field(
            'local_autograder_decision',
            'timemodified',
            time() - HOURSECS,
            ['cmid' => $quiz->cmid, 'userid' => $this->student->id],
        );
        $this->grade_item($quiz)->update_final_grade(
            (int) $this->student->id,
            60,
            'test',
            false,
            FORMAT_MOODLE,
            (int) $this->teacher->id,
        );

        $this->mark($attemptid, 1, 0.5);

        $this->assertEquals(60.0, $this->final_grade($quiz));
    }

    /**
     * A new attempt the quiz can mark on its own gives it a grade too, and
     * that grade replaces autograder's — lower or not.
     */
    public function test_a_new_attempt_the_quiz_marks_itself_gives_the_gradebook_back(): void {
        $quiz = $this->quiz_with_essays(1, ['grademethod' => QUIZ_ATTEMPTLAST]);
        $this->attempt($quiz, 'An essay.');
        $this->autograde($quiz);

        // Left blank, the essay has nothing to mark and scores nothing.
        $this->attempt($quiz, '');

        $grade = $this->gradebook($quiz);
        $this->assertEquals(0.0, (float) $grade->finalgrade);
        $this->assertEmpty($grade->overridden);
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

    /**
     * Switches autograder on and lets it grade the student now.
     *
     * @param \stdClass $quiz
     */
    private function autograde(\stdClass $quiz): void {
        global $DB;

        $config = $this->configure($quiz);
        $cm = get_fast_modinfo($this->course)->get_cm((int) $quiz->cmid);
        $decision = decision_repository::ensure($cm, $config, (int) $this->student->id);
        $this->assertNotNull($decision, 'The student submitted, so autograder has something to plan.');

        $DB->set_field('local_autograder_decision', 'scheduledgradetime', time() - MINSECS, ['id' => $decision->id]);

        $task = new grade_student();
        $task->set_custom_data((object) ['decisionid' => (int) $decision->id]);
        $task->execute();

        $this->assertSame(
            decision_repository::STATUS_GRADED,
            decision_repository::get((int) $decision->id)->status,
        );
    }

    /**
     * The teacher marks one question of an attempt, the way the quiz's own
     * marking screen does it.
     *
     * @param int $attemptid
     * @param int $slot
     * @param float $mark
     */
    private function mark(int $attemptid, int $slot, float $mark): void {
        $this->setUser($this->teacher);

        $attemptobj = quiz_attempt::create($attemptid);
        $attemptobj->get_question_usage()->manual_grade($slot, 'Marked.', $mark, FORMAT_HTML);
        $attemptobj->process_submitted_actions(time(), false, []);

        \mod_quiz\event\question_manually_graded::create([
            'objectid' => $attemptobj->get_question_attempt($slot)->get_question_id(),
            'courseid' => $attemptobj->get_courseid(),
            'context' => $attemptobj->get_quizobj()->get_context(),
            'other' => [
                'quizid' => $attemptobj->get_quizid(),
                'attemptid' => $attemptobj->get_attemptid(),
                'slot' => $slot,
            ],
        ])->trigger();

        $this->setAdminUser();
    }

    /**
     * The quiz's grade item.
     *
     * @param \stdClass $quiz
     * @return \grade_item
     */
    private function grade_item(\stdClass $quiz): \grade_item {
        return \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $quiz->id,
            'itemnumber' => 0,
            'courseid' => $this->course->id,
        ]);
    }

    /**
     * The student's row in the gradebook for the quiz.
     *
     * @param \stdClass $quiz
     * @return \grade_grade
     */
    private function gradebook(\stdClass $quiz): \grade_grade {
        return \grade_grade::fetch(['itemid' => $this->grade_item($quiz)->id, 'userid' => $this->student->id]);
    }

    /**
     * What the gradebook shows for the student on the quiz.
     *
     * @param \stdClass $quiz
     * @return float|null
     */
    private function final_grade(\stdClass $quiz): ?float {
        $grade = $this->gradebook($quiz);

        return $grade->finalgrade === null ? null : (float) $grade->finalgrade;
    }
}
