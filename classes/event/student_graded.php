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

namespace local_autograder\event;

/**
 * Autograder posted a grade for a student.
 *
 * `other` carries `modname`, `graderid`, `grademethod` and `gradevalue`.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class student_graded extends decision_event_base {
    /**
     * The event's own display name.
     */
    public static function get_name(): string {
        return get_string('event:student_graded', 'local_autograder');
    }

    /**
     * A human-readable account of what happened.
     */
    public function get_description(): string {
        $grader = $this->other['graderid'] ?? 0;
        $grade = $this->other['gradevalue'] ?? '';

        return "Autograder gave the user with id '{$this->relateduserid}' a grade of '{$grade}' in the course " .
            "module with id '{$this->contextinstanceid}', on behalf of the user with id '{$grader}'.";
    }
}
