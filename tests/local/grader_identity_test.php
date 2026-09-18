<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_autograder\local;

use local_autograder\local\module\module_adapter;

/**
 * Who a grade is written as.
 *
 * Run as the site administrator on purpose, because that is who cron runs as.
 * The picker choosing the right teacher was never the problem: this plugin's
 * own records named the right one. What went wrong is that core fills in
 * whoever it is not told from the current user, so a grade written from a task
 * read in the gradebook as the administrator's. These tests pin down that the
 * gradebook names the teacher for every kind of activity, and that nothing is
 * ever written as an administrator.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\module\module_adapter::write_grade
 */
final class grader_identity_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The teacher the grade must read as. */
    private \stdClass $teacher;

    /** @var \stdClass The student being graded. */
    private \stdClass $student;

    /**
     * A course with a teacher and a student, graded from where cron grades.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');

        // What cron is: the site administrator.
        $this->setAdminUser();
    }

    /**
     * Writes the configured grade for the student as the teacher.
     *
     * @param \stdClass $cm
     * @return float
     */
    private function write(\stdClass $cm): float {
        $config = (object) [
            'gradevalue' => 70.0,
            'grademethod' => 'point',
            'advancedgrading' => null,
        ];

        return module_adapter::for_cm($cm, $config)->write_grade((int) $this->student->id, (int) $this->teacher->id);
    }

    /**
     * The gradebook row for one activity's grade item.
     *
     * @param string $modname
     * @param int $instance
     * @param int $itemnumber
     * @return \stdClass
     */
    private function gradebook(string $modname, int $instance, int $itemnumber = 0): \stdClass {
        global $DB;

        $item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $modname,
            'iteminstance' => $instance,
            'itemnumber' => $itemnumber,
            'courseid' => $this->course->id,
        ]);
        $this->assertNotFalse($item, "The {$modname} has a grade item.");

        return $DB->get_record('grade_grades', ['itemid' => $item->id, 'userid' => $this->student->id], '*', MUST_EXIST);
    }

    /**
     * Asserts the grade reads as the teacher's and not the administrator's.
     *
     * @param \stdClass $grade
     * @param string $what
     * @return void
     */
    private function assert_graded_by_the_teacher(\stdClass $grade, string $what): void {
        $this->assertEquals(70.0, (float) $grade->finalgrade, "{$what}: the grade is there.");
        $this->assertNotEquals(get_admin()->id, (int) $grade->usermodified, "{$what}: never the administrator.");
        $this->assertEquals($this->teacher->id, (int) $grade->usermodified, "{$what}: the teacher graded it.");
    }

    /**
     * A whole-forum grade reads as the teacher's in the gradebook.
     *
     * The case that was wrong. The forum keeps no record of who graded — its
     * grade table has no such column — so the gradebook is the only place
     * that says, and core fills it in from the current user because the forum
     * does not pass it on. From a task that was the administrator, every time.
     *
     * @return void
     */
    public function test_a_forum_grade_reads_as_the_teacher_in_the_gradebook(): void {
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $this->course->id,
            'grade_forum' => 100,
        ]);
        $cm = get_coursemodule_from_instance('forum', $forum->id, $this->course->id, false, MUST_EXIST);

        $this->write($cm);

        // The whole-forum grade is the forum's second grade item; the first is ratings.
        $this->assert_graded_by_the_teacher($this->gradebook('forum', (int) $forum->id, 1), 'Forum');
    }

    /**
     * An assignment grade reads as the teacher's too.
     *
     * @return void
     */
    public function test_an_assignment_grade_reads_as_the_teacher(): void {
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $this->course->id,
            'grade' => 100,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);

        $this->write($cm);

        $this->assert_graded_by_the_teacher($this->gradebook('assign', (int) $assign->id), 'Assignment');
    }

    /**
     * A grade written straight into the gradebook reads as the teacher's, in
     * the grade and in its history.
     *
     * @return void
     */
    public function test_a_gradebook_override_reads_as_the_teacher_in_the_history_too(): void {
        global $DB;

        $quiz = $this->getDataGenerator()->create_module('quiz', [
            'course' => $this->course->id,
            'grade' => 100,
        ]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id, $this->course->id, false, MUST_EXIST);

        $this->write($cm);

        $grade = $this->gradebook('quiz', (int) $quiz->id);
        $this->assert_graded_by_the_teacher($grade, 'Quiz');

        // The history records who was logged in when the grade was written,
        // which is the other place an administrator would have shown up.
        $history = $DB->get_records('grade_grades_history', ['oldid' => $grade->id], 'id DESC', '*', 0, 1);
        $this->assertNotEmpty($history);
        $this->assertEquals($this->teacher->id, (int) reset($history)->loggeduser, 'Quiz: the history names the teacher.');
    }

    /**
     * Grading as somebody leaves the session as it found it.
     *
     * @return void
     */
    public function test_the_task_is_itself_again_afterwards(): void {
        global $USER;

        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $this->course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);

        $this->write($cm);

        $this->assertEquals(get_admin()->id, (int) $USER->id);
    }

    /**
     * A site administrator is refused at the last door, whoever asked.
     *
     * @return void
     */
    public function test_an_administrator_is_never_written_as(): void {
        $assign = $this->getDataGenerator()->create_module('assign', ['course' => $this->course->id, 'grade' => 100]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);
        $config = (object) ['gradevalue' => 70.0, 'grademethod' => 'point', 'advancedgrading' => null];

        $this->expectException(\moodle_exception::class);

        module_adapter::for_cm($cm, $config)->write_grade((int) $this->student->id, (int) get_admin()->id);
    }
}
