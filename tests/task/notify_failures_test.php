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

namespace local_autograder\task;

use local_autograder\local\decision\decision_repository;

/**
 * The summary teachers are sent of the students autograder could not grade.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\task\notify_failures
 */
final class notify_failures_test extends \advanced_testcase {
    /** @var \stdClass The course. */
    private \stdClass $course;

    /** @var \stdClass The assignment where grading failed. */
    private \stdClass $cm;

    /** @var \stdClass A teacher who can configure autograder there. */
    private \stdClass $teacher;

    /**
     * An assignment with a teacher, and the notices switched on with the count
     * started a moment ago.
     */
    protected function setUp(): void {
        parent::setUp();

        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course(['fullname' => 'Art History II']);
        $this->teacher = $generator->create_and_enrol($this->course, 'editingteacher');

        $assign = $generator->create_module('assign', [
            'course' => $this->course->id,
            'name' => 'Final essay',
            'grade' => 100,
        ]);
        $this->cm = get_coursemodule_from_instance('assign', $assign->id, $this->course->id, false, MUST_EXIST);

        set_config('notifyfailures', 1, 'local_autograder');
        set_config('lastnotified', time() - HOURSECS, 'local_autograder');
    }

    /**
     * Nothing is sent while the site has not switched the notices on.
     */
    public function test_nothing_is_sent_while_switched_off(): void {
        set_config('notifyfailures', 0, 'local_autograder');
        $this->fail_students(1);

        $sink = $this->redirectMessages();
        (new notify_failures())->execute();

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * A teacher of the activity hears about it, by this plugin's own message.
     */
    public function test_a_teacher_hears_about_a_failure_in_their_activity(): void {
        $this->fail_students(1);

        $sink = $this->redirectMessages();
        $this->expectOutputRegex('/told 1 teacher/');
        (new notify_failures())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);

        $message = reset($messages);
        $this->assertEquals($this->teacher->id, $message->useridto);
        $this->assertSame('local_autograder', $message->component);
        $this->assertSame('failuredigest', $message->eventtype);
        $this->assertStringContainsString('Final essay', $message->fullmessage);
        $this->assertStringContainsString('Art History II', $message->fullmessage);
    }

    /**
     * A class that failed together is one line with a count, not a line per
     * student — and one message, however many students it covers.
     */
    public function test_one_message_covers_every_student_of_an_activity(): void {
        $this->fail_students(3);

        $sink = $this->redirectMessages();
        $this->expectOutputRegex('/told 1 teacher/');
        (new notify_failures())->execute();

        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Students affected: 3', reset($messages)->fullmessage);
    }

    /**
     * The students themselves are not told: the summary is for whoever can act
     * on it, and a student cannot switch autograder off.
     */
    public function test_the_students_are_not_sent_it(): void {
        $students = $this->fail_students(2);

        $sink = $this->redirectMessages();
        $this->expectOutputRegex('/told 1 teacher/');
        (new notify_failures())->execute();

        $recipients = array_map(static fn($message): int => (int) $message->useridto, $sink->get_messages());

        foreach ($students as $student) {
            $this->assertNotContains((int) $student->id, $recipients);
        }
    }

    /**
     * A failure is reported once. The next run starts where the last stopped,
     * so the same broken rubric does not arrive again every week.
     */
    public function test_each_failure_is_reported_once(): void {
        $this->fail_students(1);

        $sink = $this->redirectMessages();
        $this->expectOutputRegex('/told 1 teacher/');
        (new notify_failures())->execute();
        (new notify_failures())->execute();

        $this->assertCount(1, $sink->get_messages(), 'The second run finds nothing new.');
    }

    /**
     * Switching the notices on starts the count from that moment, so the first
     * summary is not every failure the site has ever had.
     */
    public function test_switching_it_on_leaves_earlier_failures_out(): void {
        $this->fail_students(1, time() - WEEKSECS);

        set_config('lastnotified', 0, 'local_autograder');
        notify_failures::setting_changed('s_local_autograder_notifyfailures');

        $this->assertGreaterThanOrEqual(time() - MINSECS, (int) get_config('local_autograder', 'lastnotified'));

        $sink = $this->redirectMessages();
        (new notify_failures())->execute();

        $this->assertCount(0, $sink->get_messages());
    }

    /**
     * A site that switched the notices on without going through the settings
     * page — forced in config.php, say — never ran the callback. The task
     * starts the count itself rather than reporting everything that ever
     * failed.
     */
    public function test_a_first_run_without_a_count_starts_one(): void {
        $this->fail_students(1, time() - WEEKSECS);
        unset_config('lastnotified', 'local_autograder');

        $sink = $this->redirectMessages();
        (new notify_failures())->execute();

        $this->assertCount(0, $sink->get_messages());
        $this->assertNotFalse(get_config('local_autograder', 'lastnotified'), 'The count has started.');
    }

    /**
     * A manager of the whole site or of the category holds the capability in
     * every course beneath them. Told about every one, they would get every
     * failure on the campus each week — so unless enrolled, they are not.
     */
    public function test_managers_above_the_course_are_not_told(): void {
        global $DB;

        $managerrole = $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST);

        $sitemanager = $this->getDataGenerator()->create_user();
        role_assign($managerrole, $sitemanager->id, \context_system::instance()->id);

        $categorymanager = $this->getDataGenerator()->create_user();
        role_assign($managerrole, $categorymanager->id, \context_coursecat::instance($this->course->category)->id);

        $this->fail_students(1);

        $this->expectOutputRegex('/told 1 teacher/');
        $recipients = $this->recipients();

        $this->assertNotContains((int) $sitemanager->id, $recipients);
        $this->assertNotContains((int) $categorymanager->id, $recipients);
        $this->assertContains((int) $this->teacher->id, $recipients, 'The course\'s own teacher still is.');
    }

    /**
     * Enrolled, a site manager is one of the course's people, and is told like
     * any other.
     */
    public function test_a_site_manager_enrolled_in_the_course_is_told(): void {
        global $DB;

        $manager = $this->getDataGenerator()->create_user();
        role_assign(
            $DB->get_field('role', 'id', ['shortname' => 'manager'], MUST_EXIST),
            $manager->id,
            \context_system::instance()->id
        );
        $this->getDataGenerator()->enrol_user($manager->id, $this->course->id, 'student');
        $this->fail_students(1);

        $this->expectOutputRegex('/told 2 teacher/');
        $this->assertContains((int) $manager->id, $this->recipients());
    }

    /**
     * Runs the task and says who was sent something.
     *
     * @return int[]
     */
    private function recipients(): array {
        $sink = $this->redirectMessages();
        (new notify_failures())->execute();

        return array_map(static fn($message): int => (int) $message->useridto, $sink->get_messages());
    }

    /**
     * Failed decisions for new students of the assignment.
     *
     * @param int $count
     * @param int|null $when When they failed; now by default.
     * @return \stdClass[] The students.
     */
    private function fail_students(int $count, ?int $when = null): array {
        global $DB;

        $when = $when ?? time();
        $students = [];

        for ($i = 0; $i < $count; $i++) {
            $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
            $students[] = $student;

            $DB->insert_record('local_autograder_decision', (object) [
                'cmid' => $this->cm->id,
                'courseid' => $this->course->id,
                'userid' => $student->id,
                'status' => decision_repository::STATUS_FAILED,
                'failurereason' => 'no_grader',
                'baselineduedate' => $when,
                'duedatereason' => 'submission',
                'scheduledgradetime' => $when,
                'timecreated' => $when,
                'timemodified' => $when,
            ]);
        }

        return $students;
    }
}
