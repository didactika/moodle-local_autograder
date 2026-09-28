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
use local_autograder\local\decision\due_date_calculator;

/**
 * When a student has done an activity that tracks completion.
 *
 * Where completion is tracked, it is what "done" means — not whatever the
 * student happened to post first. Except for the conditions only a grade can
 * meet, which autograder itself would have to meet by grading.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\decision\decision_planner
 * @covers      \local_autograder\observer
 */
final class decision_planner_test extends \advanced_testcase {
    /** @var \stdClass A course that tracks completion. */
    private \stdClass $course;

    /** @var \stdClass The teacher autograder grades as. */
    private \stdClass $teacher;

    /** @var \stdClass The student. */
    private \stdClass $student;

    /**
     * A course with completion switched on, a teacher and a student.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();
        set_config('enablecompletion', 1);

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['enablecompletion' => 1]);
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');
        $this->student = $generator->create_and_enrol($this->course, 'student');
    }

    /**
     * A forum that asks for three replies is not done by opening one
     * discussion — although the discussion's first post is a post like any
     * other.
     */
    public function test_opening_a_discussion_does_not_meet_a_replies_condition(): void {
        $cm = $this->forum([
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionreplies' => 3,
        ]);

        $this->open_discussion($cm);

        $this->assertNull($this->plan($cm), 'No reply yet, so nothing is due.');
    }

    /**
     * Once the replies the forum asks for are there, the student is due.
     */
    public function test_the_replies_asked_for_are_enough(): void {
        $cm = $this->forum([
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionreplies' => 1,
        ]);

        $this->reply_to($this->open_discussion($cm));

        $plan = $this->plan($cm);
        $this->assertNotNull($plan);
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $plan['duedatereason']);
    }

    /**
     * "Receive a grade" can only be met by a grade, and the grade is
     * autograder's to give: it must not hold back the grade that meets it.
     */
    public function test_a_grade_condition_does_not_hold_the_grade_back(): void {
        $cm = $this->forum([
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionreplies' => 1,
            'completionusegrade' => 1,
            'completiongradeitemnumber' => 1,
        ]);

        $this->reply_to($this->open_discussion($cm));

        $plan = $this->plan($cm);
        $this->assertNotNull($plan, 'Everything the student can do is done; only the grade is left.');
        $this->assertSame(due_date_calculator::REASON_COMPLETION, $plan['duedatereason']);
    }

    /**
     * A completion that asks for nothing but a grade is judged as though the
     * activity tracked none, so the post is what counts.
     */
    public function test_a_completion_that_only_asks_for_a_grade_counts_the_post(): void {
        $cm = $this->forum([
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionusegrade' => 1,
            'completiongradeitemnumber' => 1,
        ]);

        $this->open_discussion($cm);

        $plan = $this->plan($cm);
        $this->assertNotNull($plan);
        $this->assertSame(due_date_calculator::REASON_SUBMISSION, $plan['duedatereason']);
    }

    /**
     * Without completion, any post — a new discussion included — is the
     * student handing the forum in.
     */
    public function test_without_completion_opening_a_discussion_counts(): void {
        $cm = $this->forum([]);

        $this->open_discussion($cm);

        $plan = $this->plan($cm);
        $this->assertNotNull($plan);
        $this->assertSame(due_date_calculator::REASON_SUBMISSION, $plan['duedatereason']);
    }

    /**
     * Opening a discussion fires discussion_created rather than post_created,
     * and is noticed as it happens rather than at the next sweep.
     */
    public function test_opening_a_discussion_is_noticed_as_it_happens(): void {
        $cm = $this->forum([]);
        $discussion = $this->open_discussion($cm);

        $this->setUser($this->student);
        \mod_forum\event\discussion_created::create([
            'context' => \context_module::instance((int) $cm->id),
            'objectid' => $discussion->id,
            'other' => ['forumid' => $cm->instance],
        ])->trigger();

        $this->assertNotFalse(
            decision_repository::for_cm_user((int) $cm->id, (int) $this->student->id),
            'The student has a decision.'
        );
    }

    /**
     * A forum graded as a whole, with autograder switched on.
     *
     * @param array $options Its settings, completion included.
     * @return \stdClass The course module.
     */
    private function forum(array $options): \stdClass {
        $forum = $this->getDataGenerator()->create_module('forum', $options + [
            'course' => $this->course->id,
            'grade_forum' => 100,
        ]);
        $cm = get_coursemodule_from_instance('forum', $forum->id, $this->course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $this->course->id,
            true,
            'point',
            70.0,
            null,
            0,
            (int) $this->teacher->id
        );

        return $cm;
    }

    /**
     * The student opens a discussion.
     *
     * @param \stdClass $cm
     * @return \stdClass The discussion.
     */
    private function open_discussion(\stdClass $cm): \stdClass {
        return $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_discussion([
            'course' => $this->course->id,
            'forum' => $cm->instance,
            'userid' => $this->student->id,
        ]);
    }

    /**
     * The student replies to a discussion.
     *
     * @param \stdClass $discussion
     */
    private function reply_to(\stdClass $discussion): void {
        $this->getDataGenerator()->get_plugin_generator('mod_forum')->create_post([
            'discussion' => $discussion->id,
            'userid' => $this->student->id,
            'parent' => $discussion->firstpost,
        ]);
    }

    /**
     * What autograder would plan for the student.
     *
     * @param \stdClass $cm
     * @return array|null
     */
    private function plan(\stdClass $cm): ?array {
        return decision_planner::plan($cm, config_repository::get_for_cm((int) $cm->id), (int) $this->student->id);
    }
}
