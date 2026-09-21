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
 * Runs a piece of work as if a given teacher were the logged-in user, then
 * puts the previous user back.
 *
 * Every grade autograder writes goes through this — see
 * {@see \local_autograder\local\module\module_adapter::write_grade()} — and
 * not only the ones whose API reads `$USER`. Telling core who graded is not
 * enough on its own, because core fills in from the current user every place
 * it was not told about:
 *
 * - `assign::save_grade()` is never told at all. It checks `mod/assign:grade`
 *   against `$USER` and stamps `$grade->grader` with it, so there is no way to
 *   grade as somebody else without becoming them for the duration.
 * - The forum takes its grader as an argument, but core then copies the grade
 *   into the gradebook without carrying it, and `update_raw_grade()` stamps
 *   `usermodified` from `$USER`.
 * - `grade_item::update_final_grade()` does take the grader, and still writes
 *   `grade_grades_history.loggeduser` from `$USER` — so the grade names the
 *   teacher while its history names whoever cron ran as.
 *
 * That last one is the trap: an explicit grader argument is not evidence that
 * this wrapper is unnecessary. `grader_identity_test` pins all three.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class acting_as {
    /**
     * Runs `$callback` with `$USER` set to the given user.
     *
     * The previous user is restored even when the callback throws. Uses
     * `\core\cron::setup_user()` — the supported way to become a user in a
     * task — with `$leavepagealone` set, both to keep `$PAGE` intact and
     * because that flag is what lets it run outside a CLI script (the report
     * plugin's "grade now" button reaches this from a web request).
     *
     * @param int $userid The user to act as.
     * @param callable $callback
     * @return mixed Whatever the callback returns.
     */
    public static function user(int $userid, callable $callback) {
        global $USER, $DB;

        $previous = $USER;
        $target = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

        \core\cron::setup_user($target, null, true);

        try {
            return $callback();
        } finally {
            // A previous user with no id is the empty/not-logged-in session a
            // task starts from; null puts the default cron user back, which is
            // what the task runner would have set up anyway.
            \core\cron::setup_user(empty($previous->id) ? null : $previous, null, true);
        }
    }
}
