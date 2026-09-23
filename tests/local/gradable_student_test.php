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
use local_autograder\task\grade_student;

/**
 * Who autograder grades: the people on the grader report, and nobody else.
 *
 * A teacher posting in a forum looks, to the forum, exactly like a student
 * posting in it. Every one of these users was enrolled, and enrolment was all
 * autograder asked for, so a teacher's post in an autograded forum ended with
 * the configured grade written against the teacher — a grade the grader
 * report does not list, because it lists the gradebook's graded roles only.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\decision\decision_planner::is_gradable_student
 * @covers      \local_autograder\local\decision\decision_repository::ensure
 * @covers      \local_autograder\task\grade_student
 */
final class gradable_student_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The autograded forum's course module. */
    private \stdClass $cm;

    /** @var \stdClass The forum instance. */
    private \stdClass $forum;

    /** @var \stdClass The teacher the configuration names as grader. */
    private \stdClass $teacher;

    /**
     * A course with a whole-forum grade out of 100, autograded at 70.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');

        $this->forum = $generator->create_module('forum', [
            'course' => $this->course->id,
            'grade_forum' => 100,
        ]);
        $this->cm = get_coursemodule_from_instance('forum', $this->forum->id, $this->course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $this->cm->id,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            0,
            (int) $this->teacher->id
        );
    }

    /**
     * A student who posts is still autograded.
     *
     * @return void
     */
    public function test_a_student_who_posts_is_graded(): void {
        $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $this->post_as($student);

        $this->assertTrue(decision_planner::is_gradable_student($this->cm, (int) $student->id));

        $decision = decision_repository::ensure($this->cm, config_repository::get_for_cm((int) $this->cm->id), (int) $student->id);
        $this->assertNotNull($decision);

        $this->grade_now($decision);

        $this->assertEquals(70.0, $this->forum_grade_of($student));
    }

    /**
     * The reported case: a teacher of the course who posts in the forum.
     *
     * @return void
     */
    public function test_a_teacher_who_posts_is_never_graded(): void {
        foreach (['editingteacher', 'teacher'] as $role) {
            $teacher = $this->getDataGenerator()->create_and_enrol($this->course, $role);
            $this->post_as($teacher);

            $this->assertFalse(decision_planner::is_gradable_student($this->cm, (int) $teacher->id), $role);

            $decision = decision_repository::ensure(
                $this->cm,
                config_repository::get_for_cm((int) $this->cm->id),
                (int) $teacher->id
            );

            $this->assertNull($decision, "No decision is made for a {$role}.");
            $this->assertNull($this->forum_grade_of($teacher), "No grade is written for a {$role}.");
        }
    }

    /**
     * Enrolled with no graded role at all — a custom or non-graded role.
     *
     * @return void
     */
    public function test_someone_enrolled_without_a_graded_role_is_never_graded(): void {
        $guest = $this->getDataGenerator()->create_and_enrol($this->course, 'guest');
        $this->post_as($guest);

        $this->assertFalse(decision_planner::is_gradable_student($this->cm, (int) $guest->id));
        $this->assertNull(
            decision_repository::ensure($this->cm, config_repository::get_for_cm((int) $this->cm->id), (int) $guest->id)
        );
    }

    /**
     * Somebody who is not enrolled at all.
     *
     * @return void
     */
    public function test_someone_not_enrolled_is_never_graded(): void {
        $outsider = $this->getDataGenerator()->create_user();

        $this->assertFalse(decision_planner::is_gradable_student($this->cm, (int) $outsider->id));
    }

    /**
     * A decision made while someone was a student, whose role then changed,
     * is called off at grading time rather than trusted.
     *
     * @return void
     */
    public function test_a_pending_decision_is_called_off_once_the_student_role_is_gone(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $this->post_as($user);

        $decision = decision_repository::ensure($this->cm, config_repository::get_for_cm((int) $this->cm->id), (int) $user->id);
        $this->assertNotNull($decision);

        $studentrole = (int) $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $teacherrole = (int) $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);
        $context = \context_course::instance((int) $this->course->id);
        role_unassign($studentrole, (int) $user->id, $context->id);
        role_assign($teacherrole, (int) $user->id, $context->id);

        $this->grade_now($decision);

        $settled = decision_repository::get((int) $decision->id);
        $this->assertSame(decision_repository::STATUS_CANCELLED, $settled->status);
        $this->assertSame('notastudent', $settled->failurereason);
        $this->assertNull($this->forum_grade_of($user));
    }

    /**
     * Posts a discussion in the forum as a user.
     *
     * @param \stdClass $user
     * @return void
     */
    private function post_as(\stdClass $user): void {
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion([
            'course' => $this->course->id,
            'forum' => $this->forum->id,
            'userid' => $user->id,
        ]);
    }

    /**
     * Runs the grading task for a decision as though it were due now.
     *
     * @param \stdClass $decision
     * @return void
     */
    private function grade_now(\stdClass $decision): void {
        global $DB;

        $DB->set_field('local_autograder_decision', 'scheduledgradetime', time() - MINSECS, ['id' => $decision->id]);

        $task = new grade_student();
        $task->set_custom_data((object) ['decisionid' => (int) $decision->id]);
        $task->execute();
    }

    /**
     * The whole-forum grade a user holds in the gradebook, or null.
     *
     * @param \stdClass $user
     * @return float|null
     */
    private function forum_grade_of(\stdClass $user): ?float {
        global $DB;

        $item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => 'forum',
            'iteminstance' => $this->forum->id,
            'itemnumber' => 1,
            'courseid' => $this->course->id,
        ]);

        if (!$item) {
            return null;
        }

        $finalgrade = $DB->get_field('grade_grades', 'finalgrade', ['itemid' => $item->id, 'userid' => $user->id]);

        return ($finalgrade === false || $finalgrade === null) ? null : (float) $finalgrade;
    }
}
