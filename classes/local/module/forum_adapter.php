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

use core_grades\component_gradeitem;

/**
 * Forum (whole-activity grading, not post ratings).
 *
 * Forum is the one activity type here that implements Moodle's modern
 * `component_gradeitem` API, which takes the grader as an argument instead of
 * reading `$USER` — so no impersonation is needed, and rubric/guide fillings
 * are handled by the same call.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class forum_adapter extends module_adapter {
    /**
     * The hard close if there is one, otherwise the due date.
     */
    public function close_date(): ?int {
        $forum = $this->instance();

        if (!$forum) {
            return null;
        }

        if (!empty($forum->cutoffdate)) {
            return (int) $forum->cutoffdate;
        }

        return !empty($forum->duedate) ? (int) $forum->duedate : null;
    }

    /**
     * When the student last posted in this forum.
     *
     * A forum has no submit button; contributing to it *is* the hand-in, and
     * the last post is the point at which their contribution stands as it is.
     *
     * @param int $userid
     * @return int|null
     */
    public function submitted_at(int $userid): ?int {
        global $DB;

        $posted = $DB->get_field_sql(
            "SELECT p.created
               FROM {forum_posts} p
               JOIN {forum_discussions} d ON d.id = p.discussion
              WHERE d.forum = :forum AND p.userid = :userid
           ORDER BY p.created DESC",
            ['forum' => $this->cm->instance, 'userid' => $userid],
            IGNORE_MULTIPLE
        );

        return $posted ? (int) $posted : null;
    }

    /**
     * Forum has no per-user exception table.
     *
     * @param int $userid
     * @return int|null Always null.
     */
    public function user_override_date(int $userid): ?int {
        return null;
    }

    /**
     * Forum has no per-group exception table.
     *
     * @param int[] $groupids
     * @return int[] Always empty.
     */
    public function group_override_dates(array $groupids): array {
        return [];
    }

    /**
     * Grades the forum through its component_gradeitem.
     *
     * @param int $userid
     * @param int $graderid
     * @return float
     * @throws \moodle_exception
     */
    protected function post_grade(int $userid, int $graderid): float {
        global $DB;

        $context = \context_module::instance($this->cm->id);
        $gradeduser = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        $grader = $DB->get_record('user', ['id' => $graderid], '*', MUST_EXIST);

        $gradeitem = component_gradeitem::instance('mod_forum', $context, 'forum');
        $advancedgrading = $this->configured_advanced_grading();
        $grade = $this->configured_grade();

        $formdata = new \stdClass();

        if ($advancedgrading !== null) {
            $formdata->instanceid = $gradeitem->get_grade_instance_id();
            $formdata->advancedgrading = $advancedgrading;
        } else {
            $formdata->grade = $grade;
        }

        if (!$gradeitem->store_grade_from_formdata($gradeduser, $grader, $formdata)) {
            throw new \moodle_exception('error:gradewritefailed', 'local_autograder');
        }

        return $grade;
    }
}
