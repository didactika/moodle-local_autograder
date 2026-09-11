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
 * Quiz.
 *
 * A quiz is never "re-graded": its grade comes from the attempts a student
 * made. What a teacher does when they want a different number there is
 * override it in the gradebook, and that is exactly what autograder does —
 * the same thing the old external service achieved through the grading-panel
 * web service (plan.md §8.1).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_adapter extends module_adapter {
    /**
     * A quiz closes at `timeclose`.
     */
    public function close_date(): ?int {
        $quiz = $this->instance();

        return ($quiz && !empty($quiz->timeclose)) ? (int) $quiz->timeclose : null;
    }

    /**
     * When the student last finished an attempt.
     *
     * A quiz is "handed in" when an attempt is submitted; one still in
     * progress, abandoned, or a teacher's preview is not a hand-in.
     *
     * @param int $userid
     * @return int|null
     */
    public function submitted_at(int $userid): ?int {
        global $DB;

        $finished = $DB->get_field_sql(
            "SELECT timefinish
               FROM {quiz_attempts}
              WHERE quiz = :quiz
                AND userid = :userid
                AND state = :state
                AND preview = 0
                AND timefinish > 0
           ORDER BY timefinish DESC",
            ['quiz' => $this->cm->instance, 'userid' => $userid, 'state' => 'finished'],
            IGNORE_MULTIPLE
        );

        return $finished ? (int) $finished : null;
    }

    /**
     * The `timeclose` a user override grants this student.
     *
     * @param int $userid
     * @return int|null
     */
    public function user_override_date(int $userid): ?int {
        global $DB;

        $timeclose = $DB->get_field(
            'quiz_overrides',
            'timeclose',
            ['quiz' => $this->cm->instance, 'userid' => $userid],
            IGNORE_MULTIPLE,
        );

        return $timeclose ? (int) $timeclose : null;
    }

    /**
     * Every `timeclose` a group override grants these groups.
     *
     * @param int[] $groupids
     * @return int[]
     */
    public function group_override_dates(array $groupids): array {
        global $DB;

        if (empty($groupids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);
        $params['quiz'] = $this->cm->instance;

        $dates = $DB->get_fieldset_sql(
            "SELECT timeclose
               FROM {quiz_overrides}
              WHERE quiz = :quiz AND groupid {$insql} AND timeclose IS NOT NULL AND timeclose > 0",
            $params,
        );

        return array_map('intval', $dates);
    }

    /**
     * Overrides the quiz grade in the gradebook.
     *
     * @param int $userid
     * @param int $graderid
     * @return float
     */
    protected function post_grade(int $userid, int $graderid): float {
        return $this->override_in_gradebook($userid, $graderid);
    }
}
