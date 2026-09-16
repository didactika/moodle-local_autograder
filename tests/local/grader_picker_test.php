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
use local_autograder\local\grading\teacher_source;

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

        set_config('teacher_roles', 'editingteacher,teacher', 'local_autograder');
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
     * A denied grading capability does not unchoose the student's teacher.
     *
     * The capability decides nothing about whether the grade can be stored, so
     * consulting it here only ever refused writes that would have worked. What
     * a teacher may really do is settled by the write itself.
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
        $standin = $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');
        $this->choose_roles('editingteacher');
        set_config('fallback_grader', $standin->id, 'local_autograder');

        $this->assertNull(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'The student has no teacher, so there is nobody to pick.'
        );
        $this->assertSame(
            (int) $standin->id,
            grader_picker::fallback_for(),
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
            grader_picker::fallback_for(),
            'Configured or not, the administrator never grades.'
        );
    }

    /**
     * A teacher who asked not to be graded on behalf of is left out, and stays
     * left out even if the site later stops offering the preference.
     *
     * Everybody who may grade is assumed willing; the one thing that changes
     * that is their own answer. Withdrawing the offer must not start posting
     * grades in the name of somebody who asked us not to.
     */
    public function test_the_opt_out_is_honoured_whenever_it_is_set(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        set_user_preference('local_autograder_optout', 1, $teacher);

        set_config('allowoptout', 1, 'local_autograder');
        $this->assertNull(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'While the site offers it, the answer must be honoured.'
        );

        set_config('allowoptout', 0, 'local_autograder');
        \cache_helper::purge_all();
        $this->assertNull(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'And it stays honoured once the site stops offering the preference.'
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
     * An activity prohibition does not move the choice on to somebody else.
     *
     * The fallback is for a student with no associated teacher at all, not for
     * a teacher whose capabilities the activity happens to restrict.
     */
    public function test_a_prohibition_does_not_change_who_is_chosen(): void {
        global $DB;
        $first = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');
        $fallback = $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');
        set_config('fallback_grader', $fallback->id, 'local_autograder');
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher']);
        assign_capability(
            'mod/assign:grade',
            CAP_PROHIBIT,
            $roleid,
            \context_module::instance($this->cm->id)->id
        );
        accesslib_clear_all_caches_for_unit_testing();
        $this->assertSame(
            (int) $first->id,
            grader_picker::resolve_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * The fallback needs no privilege either, only an account that may sign.
     *
     * A site names its stand-in on purpose; refusing it for want of a
     * capability the write never consults would leave decisions failing with
     * a perfectly good grader configured and unused.
     */
    public function test_the_fallback_needs_no_privilege(): void {
        $fallback = $this->getDataGenerator()->create_user();
        set_config('fallback_grader', $fallback->id, 'local_autograder');
        $this->assertSame(
            (int) $fallback->id,
            grader_picker::resolve_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * Deleted and suspended accounts cannot sign a grade.
     */
    public function test_inactive_teachers_are_excluded(): void {
        global $DB;
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $DB->set_field('user', 'suspended', 1, ['id' => $teacher->id]);
        $this->assertNull(grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id));
    }

    /**
     * Groups narrow the choice by the course's rule, not the activity's.
     *
     * Separate groups plus a default grouping is the course's own rule, and
     * the one the student is shown their teachers by. An activity carries
     * its own group mode, but letting that narrow the choice as well
     * produced a second, contradictory answer: a teacher the student sees as
     * theirs, refused on the activity, for a grade the activity would have
     * accepted.
     */
    public function test_the_courses_grouping_is_what_narrows_the_choice(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $mine = $generator->create_and_enrol($this->course, 'editingteacher');
        $theirs = $generator->create_and_enrol($this->course, 'editingteacher');
        $group = $generator->create_group(['courseid' => $this->course->id]);
        $other = $generator->create_group(['courseid' => $this->course->id]);
        $grouping = $generator->create_grouping(['courseid' => $this->course->id]);

        groups_assign_grouping((int) $grouping->id, (int) $group->id);
        groups_assign_grouping((int) $grouping->id, (int) $other->id);
        $this->join($group, $this->student);
        $this->join($other, $theirs);

        $DB->set_field('course', 'groupmode', SEPARATEGROUPS, ['id' => $this->course->id]);
        $DB->set_field('course', 'defaultgroupingid', $grouping->id, ['id' => $this->course->id]);
        $this->prevent('moodle/site:accessallgroups');

        // Nobody shares the student's group yet, so the whole course stands —
        // the same widening the rule does rather than showing no teacher.
        \cache_helper::purge_all();
        $this->assertContains(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            [(int) $mine->id, (int) $theirs->id]
        );

        $this->join($group, $mine);
        \cache_helper::purge_all();

        $this->assertSame(
            (int) $mine->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            'The teacher sharing the student\'s group in the default grouping.'
        );
    }

    /**
     * Programme courses use coordinators, not the union of both role lists.
     */
    public function test_programme_uses_coordinator_roles(): void {
        $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $coordinator = $this->getDataGenerator()->create_and_enrol($this->course, 'teacher');
        $this->choose_roles('editingteacher');
        set_config('coordinator_roles', 'teacher', 'local_autograder');
        set_config('subject_course_category', -1, 'local_autograder');
        set_config('program_course_category', $this->course->category, 'local_autograder');
        $this->assertSame(
            (int) $coordinator->id,
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id)
        );
    }

    /**
     * The choice is the same in every activity of one course.
     */
    public function test_every_activity_of_a_course_picks_the_same_teacher(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $this->course->id]);
        $this->prevent('moodle/grade:edit');

        $this->assertSame(
            (int) $teacher->id,
            grader_picker::pick_for((int) $quiz->cmid, (int) $this->student->id)
        );
        $this->assertSame(
            grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id),
            grader_picker::pick_for((int) $quiz->cmid, (int) $this->student->id)
        );
    }

    /**
     * Repeated students in different activities reuse course association data.
     */
    public function test_warm_selection_does_not_query_per_student(): void {
        global $DB;
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $students = [];
        for ($i = 0; $i < 20; $i++) {
            $students[] = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        }
        grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id);
        $before = $DB->perf_get_reads();
        foreach ($students as $student) {
            $this->assertSame(
                (int) $teacher->id,
                grader_picker::pick_for((int) $this->cm->id, (int) $student->id)
            );
        }
        $this->assertSame($before, $DB->perf_get_reads());
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
    /**
     * Pins the teaching roles by hand, instead of letting them be worked out.
     *
     * @param string $shortnames Comma-separated.
     */
    private function choose_roles(string $shortnames): void {
        set_config('teacher_source_mode', teacher_source::MODE_CHOSEN_ROLES, 'local_autograder');
        set_config('teacher_roles', $shortnames, 'local_autograder');
        \cache_helper::purge_all();
    }

    /**
     * Adds a user to a group.
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
