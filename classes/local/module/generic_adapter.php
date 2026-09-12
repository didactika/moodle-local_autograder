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

namespace local_autograder\local\module;

/**
 * Any gradeable activity type with no adapter of its own.
 *
 * Closing date is found by looking for the field names Moodle's activities
 * conventionally use; a type that has none is simply graded from the
 * completion instant (rule 1). Grades go in as a gradebook override, the one
 * path every gradeable activity supports.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class generic_adapter extends module_adapter {
    /**
     * The field names activities use for "this closes now", most specific
     * first. Checked against the activity's own row.
     */
    private const CLOSE_DATE_FIELDS = ['cutoffdate', 'timeclose', 'duedate', 'timedue', 'deadline'];

    /**
     * The first closing field this activity actually has set.
     */
    public function close_date(): ?int {
        $instance = $this->instance();

        if (!$instance) {
            return null;
        }

        foreach (self::CLOSE_DATE_FIELDS as $field) {
            if (!empty($instance->{$field})) {
                return (int) $instance->{$field};
            }
        }

        return null;
    }

    /**
     * Generic activities have no per-user exception table.
     *
     * @param int $userid
     * @return int|null Always null.
     */
    public function user_override_date(int $userid): ?int {
        return null;
    }

    /**
     * Generic activities have no per-group exception table.
     *
     * @param int[] $groupids
     * @return int[] Always empty.
     */
    public function group_override_dates(array $groupids): array {
        return [];
    }

    /**
     * Posts the grade as a gradebook override.
     *
     * @param int $userid
     * @param int $graderid
     * @return float
     */
    protected function post_grade(int $userid, int $graderid): float {
        return $this->override_in_gradebook($userid, $graderid);
    }
}
