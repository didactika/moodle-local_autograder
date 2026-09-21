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
 * Autograder was configured for a course module for the first time.
 *
 * `other` carries: `grademethod`, `gradevalue`, `delayseconds`, `enabled`.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_created extends config_event_base {
    /**
     * The letter core files this kind of event under.
     */
    protected function crud_letter(): string {
        return 'c';
    }

    /**
     * The event's own display name.
     */
    public static function get_name(): string {
        return get_string('event:config_created', 'local_autograder');
    }

    /**
     * A human-readable account of what happened.
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' configured autograder for the course module " .
            "with id '{$this->contextinstanceid}'.";
    }
}
