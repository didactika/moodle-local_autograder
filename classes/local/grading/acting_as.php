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
 * Needed only where a module's own grading API reads the grader off the
 * global `$USER` instead of taking it as an argument. `mod_assign` is the
 * case in point: `assign::save_grade()` both checks `mod/assign:grade`
 * against `$USER` and stamps `$grade->grader = $USER->id`, so there is no way
 * to grade "as" someone else without becoming them for the duration. The
 * gradebook path (`grade_item::update_final_grade()`) and the modern
 * component_gradeitem path both take the grader explicitly and must NOT use
 * this.
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
