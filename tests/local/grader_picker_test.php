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

use local_autograder\local\grading\grader_picker;

/**
 * Whose name a grade is posted in.
 *
 * Autograder never grades as itself: every grade it writes belongs to a real
 * teacher, and this is what chooses them. Which makes the choice worth pinning
 * down — a grade in the wrong teacher's name is not a cosmetic mistake, and a
 * teacher who asked not to be chosen must not be.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\grading\grader_picker
 */
final class grader_picker_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass An assignment in it. */
    private \stdClass $cm;

    /** @var \stdClass The student to be graded. */
    private \stdClass $student;

    /**
     * A course with an assignment and one student.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->student = $generator->create_and_enrol($this->course, 'student');

        $assign = $generator->create_module('assign', [
            'course' => $this->course->id,
            'grade' => 100,
        ]);
        $this->cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);
    }

    /**
     * The teacher of the course is the one the grade belongs to.
     */
    public function test_the_teacher_of_the_course_is_chosen(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        $this->assertSame(
            (int) $teacher->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * Whether the teacher can really post the grade is not asked while
     * choosing them — it is found out by posting it. So a teacher whose
     * grading capability has been taken away is still the one chosen; it is
     * grade_student that then falls back, and fails if that does not work
     * either.
     */
    public function test_the_capability_is_not_checked_while_choosing(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->prevent('mod/assign:grade');

        $this->assertSame(
            (int) $teacher->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * A course with no teacher of the student's own falls back to the site's
     * configured grader.
     */
    public function test_the_site_fallback_stands_in_for_a_course_with_nobody(): void {
        $standin = $this->getDataGenerator()->create_user();
        set_config('fallback_grader', $standin->id, 'local_autograder');

        $this->assertNull(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'The student has no teacher, so there is nobody to pick.'
        );
        $this->assertSame(
            (int) $standin->id,
            grader_picker::fallback_for((int) $this->cm->id),
            'And the fallback is who grade_student turns to next.'
        );
    }

    /**
     * An administrator is never chosen, as a teacher or as the fallback.
     *
     * They hold every capability in every course, so any rule phrased in
     * capabilities reaches them everywhere — and a grade signed by the
     * administrator account says nothing true about who taught the student.
     */
    public function test_an_administrator_is_never_chosen(): void {
        $admin = get_admin();
        set_config('fallback_grader', $admin->id, 'local_autograder');

        $this->assertTrue(grader_picker::must_never_grade((int) $admin->id));
        $this->assertNull(
            grader_picker::fallback_for((int) $this->cm->id),
            'Configured or not, the administrator never grades.'
        );
    }

    /**
     * A teacher who asked not to be graded on behalf of is left out — but only
     * where the site offers that preference at all. Somewhere it was offered,
     * answered and then switched off, the old answer must stop counting.
     */
    public function test_the_opt_out_counts_only_while_the_site_offers_it(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        set_user_preference('local_autograder_optout', 1, $teacher);

        set_config('allowoptout', 1, 'local_autograder');
        $this->assertNull(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'While the site offers it, the answer must be honoured.'
        );

        set_config('allowoptout', 0, 'local_autograder');
        \cache_helper::purge_all();
        $this->assertSame(
            (int) $teacher->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'With the preference switched off site-wide, an old answer cannot keep taking them out.'
        );
    }

    /**
     * In a course that separates its groups, the grade belongs to a teacher of
     * the student's own group — not to whoever happens to be first.
     */
    public function test_a_course_that_separates_groups_picks_a_teacher_of_the_students_group(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $groupa = $generator->create_group(['courseid' => $this->course->id]);
        $groupb = $generator->create_group(['courseid' => $this->course->id]);
        $grouping = $generator->create_grouping(['courseid' => $this->course->id]);
        groups_assign_grouping((int) $grouping->id, (int) $groupa->id);
        groups_assign_grouping((int) $grouping->id, (int) $groupb->id);

        // Created first, so the lowest user id — which is what the tie-break
        // would pick if the groups were not being looked at.
        $elsewhere = $generator->create_and_enrol($this->course, 'editingteacher');
        $theirs = $generator->create_and_enrol($this->course, 'editingteacher');

        $this->join($groupb, $elsewhere);
        $this->join($groupa, $theirs);
        $this->join($groupa, $this->student);

        $DB->set_field('course', 'groupmode', SEPARATEGROUPS, ['id' => $this->course->id]);
        $DB->set_field('course', 'defaultgroupingid', (int) $grouping->id, ['id' => $this->course->id]);
        $this->prevent('moodle/site:accessallgroups');

        $this->assertLessThan((int) $theirs->id, (int) $elsewhere->id);
        $this->assertSame(
            (int) $theirs->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * A course whose groups leave a student with no teacher of their own is not
     * a reason to grade nobody: the group narrowing is dropped rather than the
     * grade.
     */
    public function test_a_student_with_no_teacher_in_their_group_is_still_graded(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $groupa = $generator->create_group(['courseid' => $this->course->id]);
        $groupb = $generator->create_group(['courseid' => $this->course->id]);
        $grouping = $generator->create_grouping(['courseid' => $this->course->id]);
        groups_assign_grouping((int) $grouping->id, (int) $groupa->id);
        groups_assign_grouping((int) $grouping->id, (int) $groupb->id);

        $teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->join($groupb, $teacher);
        $this->join($groupa, $this->student);

        $DB->set_field('course', 'groupmode', SEPARATEGROUPS, ['id' => $this->course->id]);
        $DB->set_field('course', 'defaultgroupingid', (int) $grouping->id, ['id' => $this->course->id]);
        $this->prevent('moodle/site:accessallgroups');

        $this->assertSame(
            (int) $teacher->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * Between two equally eligible teachers the choice is the same every time,
     * so that one activity's grades do not end up split between them for no
     * reason.
     */
    public function test_two_eligible_teachers_are_chosen_between_predictably(): void {
        $generator = $this->getDataGenerator();
        $first = $generator->create_and_enrol($this->course, 'editingteacher');
        $generator->create_and_enrol($this->course, 'editingteacher');

        $this->assertSame(
            (int) $first->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
        $this->assertSame(
            (int) $first->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'Asked twice, answered the same.'
        );
    }

    /**
     * Whether somebody is one of the people who grade this activity rather than
     * one of the people graded on it.
     */
    public function test_a_teacher_is_not_somebody_to_be_graded(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        $this->assertTrue(grader_picker::grades_this_module($this->cm, (int) $teacher->id));
        $this->assertFalse(grader_picker::grades_this_module($this->cm, (int) $this->student->id));
    }

    /**
     * Takes a capability away from teachers of this course.
     *
     * @param string $capability
     */
    private function prevent(string $capability): void {
        global $DB;

        $roleid = (int) $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability(
            $capability,
            CAP_PREVENT,
            $roleid,
            \context_course::instance($this->course->id)->id,
            true
        );
        accesslib_clear_all_caches_for_unit_testing();
    }

    /**
     * Puts a user in a group.
     *
     * @param \stdClass $group
     * @param \stdClass $user
     */
    private function join(\stdClass $group, \stdClass $user): void {
        $this->getDataGenerator()->create_group_member([
            'groupid' => $group->id,
            'userid' => $user->id,
        ]);
    }
}
