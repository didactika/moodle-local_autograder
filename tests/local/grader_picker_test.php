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
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
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
     * A teacher who asked not to be graded on behalf of is left out — while the
     * site offers the preference, and only then.
     *
     * The preference is the site's to offer. A site that withdraws it is saying
     * it decides who grades, and a stored answer must not go on removing
     * somebody from every course after the question stopped being asked: the
     * setting says the preference is neither shown nor honoured, and a teacher
     * who cannot see it cannot take it back either.
     */
    public function test_the_opt_out_is_honoured_while_the_site_offers_it(): void {
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
            'Once the site stops offering the preference, the stored answer is ignored.'
        );
    }

    /**
     * The grade belongs to the teacher who shares the most groups with the
     * student — here both of theirs — over one who shares only one.
     */
    public function test_the_teacher_sharing_the_most_groups_is_chosen(): void {
        [$lang, $program] = $this->groups(2);

        // Created first, so the lowest user id: what the tie-break would pick
        // if the groups were not being counted.
        $this->teacher_in([$lang]);
        $both = $this->teacher_in([$lang, $program]);
        $this->teacher_in([]);
        $this->student_in([$lang, $program]);

        $this->assertSame((int) $both->id, $this->pick());
    }

    /**
     * One group in common is enough where nobody shares more, and it beats a
     * teacher in no group.
     */
    public function test_one_shared_group_is_enough(): void {
        [$lang, $program, $other] = $this->groups(3);

        $this->teacher_in([]);
        $this->teacher_in([$other]);
        $one = $this->teacher_in([$program]);
        $this->student_in([$lang, $program]);

        $this->assertSame((int) $one->id, $this->pick());
    }

    /**
     * With no group in common, a teacher in no group — who teaches the course
     * as a whole — rather than one who teaches some other group.
     */
    public function test_with_no_group_in_common_a_teacher_in_no_group_is_chosen(): void {
        [$lang, $other] = $this->groups(2);

        $this->teacher_in([$other]);
        $whole = $this->teacher_in([]);
        $this->student_in([$lang]);

        $this->assertSame((int) $whole->id, $this->pick());
    }

    /**
     * A student whose groups no teacher shares, in a course where every
     * teacher has a group of their own, is still graded: the group narrowing
     * is dropped rather than the grade.
     */
    public function test_a_student_with_no_teacher_in_their_group_is_still_graded(): void {
        [$lang, $other] = $this->groups(2);

        $teacher = $this->teacher_in([$other]);
        $this->student_in([$lang]);

        $this->assertSame((int) $teacher->id, $this->pick());
    }

    /**
     * Teachers equally close to the student are settled by the tie-break.
     */
    public function test_equally_close_teachers_go_to_the_tie_break(): void {
        [$lang] = $this->groups(1);

        $first = $this->teacher_in([$lang]);
        $this->teacher_in([$lang]);
        $this->student_in([$lang]);

        $this->assertSame((int) $first->id, $this->pick());
    }

    /**
     * The closest teacher having opted out hands the grade to the next
     * closest, not past all of the student's teachers to the site fallback.
     */
    public function test_an_opted_out_closest_teacher_hands_over_to_the_next_closest(): void {
        [$lang, $program] = $this->groups(2);
        set_config('allowoptout', 1, 'local_autograder');

        $this->teacher_in([]);
        $one = $this->teacher_in([$lang]);
        $both = $this->teacher_in([$lang, $program]);
        set_user_preference('local_autograder_optout', 1, $both);
        $this->student_in([$lang, $program]);

        $this->assertSame((int) $one->id, $this->pick());
    }

    /**
     * A teacher whose enrolment is suspended, or has ended, no longer teaches
     * the course, whatever role is still assigned to them.
     */
    public function test_a_teacher_without_an_active_enrolment_is_never_chosen(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $suspended = $generator->create_and_enrol($this->course, 'editingteacher', null, 'manual', 0, 0, ENROL_USER_SUSPENDED);
        $ended = $generator->create_and_enrol($this->course, 'editingteacher', null, 'manual', 0, time() - DAYSECS);
        $active = $generator->create_and_enrol($this->course, 'editingteacher');

        $this->assertLessThan((int) $active->id, (int) $suspended->id);
        $this->assertLessThan((int) $active->id, (int) $ended->id);
        $this->assertSame((int) $active->id, $this->pick());

        // The same rule decides who can be chosen at all.
        $this->assertNotContains((int) $suspended->id, teacher_source::possible_graders_in((int) $this->course->id));
        $this->assertNotContains((int) $ended->id, teacher_source::possible_graders_in((int) $this->course->id));
        $this->assertTrue($DB->record_exists('role_assignments', ['userid' => $suspended->id]));
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
     * A default grouping on its own divides nothing: without separate groups,
     * every group of the course counts.
     */
    public function test_without_separate_groups_every_group_counts(): void {
        [$lang, $program] = $this->groups(2);
        $this->group_by([$lang], VISIBLEGROUPS);

        $this->teacher_in([$lang]);
        $both = $this->teacher_in([$lang, $program]);
        $this->student_in([$lang, $program]);

        $this->assertSame((int) $both->id, $this->pick());
    }

    /**
     * Where the course separates its groups through a default grouping, only
     * that grouping's groups count: a group shared outside it does not.
     */
    public function test_with_separate_groups_only_the_default_grouping_counts(): void {
        [$lang, $program] = $this->groups(2);
        $this->group_by([$lang], SEPARATEGROUPS);

        // Created first: with every group counting, both share one group with
        // the student and the tie-break would pick this one.
        $this->teacher_in([$program]);
        $separated = $this->teacher_in([$lang]);
        $this->student_in([$lang, $program]);

        $this->assertSame((int) $separated->id, $this->pick());
    }

    /**
     * With separate groups, a teacher whose groups all lie outside the
     * default grouping counts as a teacher in no group, and comes before one
     * teaching another of the grouping's groups.
     */
    public function test_with_separate_groups_a_group_outside_the_grouping_is_no_group(): void {
        [$lang, $german, $program] = $this->groups(3);
        $this->group_by([$lang, $german], SEPARATEGROUPS);

        $this->teacher_in([$german]);
        $outside = $this->teacher_in([$program]);
        $this->student_in([$lang]);

        $this->assertSame((int) $outside->id, $this->pick());
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
     * Some groups in the course.
     *
     * @param int $count
     * @return \stdClass[]
     */
    private function groups(int $count): array {
        $groups = [];

        for ($i = 0; $i < $count; $i++) {
            $groups[] = $this->getDataGenerator()->create_group(['courseid' => $this->course->id]);
        }

        return $groups;
    }

    /**
     * Makes these groups the course's default grouping, in this group mode.
     *
     * @param \stdClass[] $groups
     * @param int $groupmode
     */
    private function group_by(array $groups, int $groupmode): void {
        global $DB;

        $grouping = $this->getDataGenerator()->create_grouping(['courseid' => $this->course->id]);

        foreach ($groups as $group) {
            groups_assign_grouping((int) $grouping->id, (int) $group->id);
        }

        $DB->set_field('course', 'groupmode', $groupmode, ['id' => $this->course->id]);
        $DB->set_field('course', 'defaultgroupingid', $grouping->id, ['id' => $this->course->id]);
    }

    /**
     * A new teacher of the course, in these groups.
     *
     * @param \stdClass[] $groups
     * @return \stdClass
     */
    private function teacher_in(array $groups): \stdClass {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');

        foreach ($groups as $group) {
            $this->join($group, $teacher);
        }

        return $teacher;
    }

    /**
     * Puts the student in these groups.
     *
     * @param \stdClass[] $groups
     */
    private function student_in(array $groups): void {
        foreach ($groups as $group) {
            $this->join($group, $this->student);
        }
    }

    /**
     * Who is chosen to grade the student on the assignment, asked afresh.
     *
     * @return int|null
     */
    private function pick(): ?int {
        \cache_helper::purge_all();

        return grader_picker::pick_for((int) $this->cm->id, (int) $this->student->id);
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
