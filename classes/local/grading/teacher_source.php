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
     * The teachers of one student in one course.
     *
     * @param int $courseid
     * @param int $studentid
     * @return int[] Their user ids.
     */
    public static function teachers_of(int $courseid, int $studentid): array {
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
