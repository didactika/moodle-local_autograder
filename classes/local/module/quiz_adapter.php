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
 * override it in the gradebook, and that is exactly what autograder does.
 *
 * Autograder only gets that far while the quiz has no grade of its own — an
 * essay still waiting to be marked leaves it with no total at all. Once the
 * quiz has one, from the marking or from a new attempt, the override is taken
 * away again: see {@see module_adapter::release_override()}.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
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
     * This type says when a student handed it in: {@see submitted_at()}.
     *
     * @return bool
     */
    public static function knows_submissions(): bool {
        return true;
    }

    /**
     * When the student last handed in an attempt.
     *
     * A quiz is "handed in" when an attempt is submitted; one still in
     * progress, abandoned, or a teacher's preview is not a hand-in. From
     * Moodle 5.0 a submitted attempt waits as "submitted" until the quiz
     * grades it, usually on the next cron run: handed in all the same, and
     * counted, or a quiz tracking no completion would only ever notice the
     * student through an event this plugin had no reason to be listening for.
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
                AND state IN (:finished, :submitted)
                AND preview = 0
                AND timefinish > 0
           ORDER BY timefinish DESC",
            [
                'quiz' => $this->cm->instance,
                'userid' => $userid,
                'finished' => 'finished',
                // Moodle 5.0 onwards; no attempt is ever in it before that.
                'submitted' => 'submitted',
            ],
            IGNORE_MULTIPLE
        );

        return $finished ? (int) $finished : null;
    }

    /**
     * The `timeclose` a user override grants this student.
     *
     * Three answers, not two, because the column carries three. No row at all
     * and a row that overrides something else — the time limit, say — leave
     * the close date alone, and both read as null. A row holding 0 is an
     * override that took the deadline away, which is the opposite of having
     * none: read as "no override" it would hand the student back the very
     * deadline they were excused from.
     *
     * @param int $userid
     * @return int|null The close date, 0 where the deadline was lifted, null
     *                  where the close date is not overridden.
     */
    public function user_override_date(int $userid): ?int {
        global $DB;

        $timeclose = $DB->get_field(
            'quiz_overrides',
            'timeclose',
            ['quiz' => $this->cm->instance, 'userid' => $userid],
            IGNORE_MULTIPLE,
        );

        // False is no row; null is a row leaving timeclose alone.
        if ($timeclose === false || $timeclose === null) {
            return null;
        }

        return (int) $timeclose;
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

        // Zeroes are kept, not filtered: a group excused from the deadline
        // says so with one, and dropping it would leave the student closing at
        // whatever date the other groups happen to name.
        $dates = $DB->get_fieldset_sql(
            "SELECT timeclose
               FROM {quiz_overrides}
              WHERE quiz = :quiz AND groupid {$insql} AND timeclose IS NOT NULL",
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
