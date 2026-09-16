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

namespace local_autograder\local\grading;

/**
 * Who a student's teachers are — the same answer `local_resume` gives.
 *
 * Autograder used to work this out for itself, from who held
 * `local/autograder:gradeonbehalf`. That produced a different list from the
 * one the student is shown as their own teachers, and a grade posted in the
 * name of somebody the student has never been told is their teacher is wrong
 * however defensible the capability behind it was.
 *
 * So the question is handed to `local_resume\local\teachers` rather than
 * answered again here: one implementation, one answer, and no way for the two
 * to drift apart as either changes.
 *
 * It is asked *as the student*, because that is what makes the two identical.
 * `local_resume` finishes by dropping the teachers whose role the viewer may
 * not see, and it reads that viewer off `$USER`. Called from a scheduled task
 * `$USER` is whatever the cron happens to be running as, which would filter
 * the list against a stranger and usually empty it. Run as the student, the
 * list that comes back is precisely the list the student sees on their own
 * page.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_source {
    /** @var string The class that owns this answer, when the site has it. */
    private const RESUME_TEACHERS = '\local_resume\local\teachers';

    /**
     * Whether the site has the plugin that owns this answer.
     *
     * @return bool
     */
    public static function is_available(): bool {
        return \core_component::get_component_directory('local_resume') !== null
            && class_exists(self::RESUME_TEACHERS);
    }

    /**
     * Everybody who could grade in one course.
     *
     * The union of two answers, because either alone leaves out somebody a
     * site would expect to see:
     *
     * - local_resume's teachers of this student, which is the list the
     *   student is shown as their own and so the one a grade should
     *   normally be signed with;
     * - anyone holding `local/autograder:gradeonbehalf` in the course, which
     *   is how a site says "this person may grade here" without going
     *   through a teacher role at all.
     *
     * Ordered so that the student's own teachers come first: they are who
     * should be picked when there is a choice, and the caller takes the
     * first that works.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[] Their user ids.
     */
    public static function teachers_of(int $courseid, int $studentid): array {
        $teachers = self::preferred_teachers_of($courseid, $studentid);

        foreach (self::capable_graders($courseid) as $graderid) {
            if (!in_array($graderid, $teachers, true)) {
                $teachers[] = $graderid;
            }
        }

        return $teachers;
    }

    /**
     * The student's own teachers, and only those.
     *
     * Who the grade should be signed with when there is a choice: these are
     * the people the student is shown as their teachers, already narrowed to
     * the ones sharing a group with them where the course separates groups.
     * Everybody else in {@see self::teachers_of()} merely *could* grade —
     * they are who is left when this list is empty.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    public static function preferred_teachers_of(int $courseid, int $studentid): array {
        return self::resume_teachers($courseid, $studentid);
    }

    /**
     * Everybody Moodle itself says may grade in one course.
     *
     * `moodle/grade:edit` and nothing of this plugin's own. Autograder used to
     * define a `gradeonbehalf` capability and ask for that instead, which was
     * a second answer to a question Moodle already answers — and a worse one:
     * a user with two roles, one granting it and one not, was judged by
     * whichever the plugin happened to look at. `has_capability()` has always
     * resolved that properly, allowing unless something prohibits, so it is
     * what decides here now.
     *
     * Course context rather than each activity's: asking per activity would
     * mean one capability query per module on a campus that has millions.
     *
     * @param int $courseid
     * @return int[]
     */
    private static function capable_graders(int $courseid): array {
        $context = \context_course::instance($courseid, IGNORE_MISSING);

        if (!$context) {
            return [];
        }

        return array_map(
            'intval',
            array_keys(get_users_by_capability($context, 'moodle/grade:edit', 'u.id'))
        );
    }

    /**
     * The student's own teachers, as local_resume names them.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[]
     */
    private static function resume_teachers(int $courseid, int $studentid): array {
        if (!self::is_available()) {
            return [];
        }

        $classname = self::RESUME_TEACHERS;

        try {
            $teachers = acting_as::user(
                $studentid,
                static fn(): array => $classname::get_user_teachers_by_groups($courseid, $studentid)
            );
        } catch (\Throwable $e) {
            // A course local_resume cannot answer for — one outside the
            // categories it is configured with, say — is not a course where
            // autograder should invent an answer of its own instead.
            return [];
        }

        return array_map('intval', array_keys($teachers));
    }
}
