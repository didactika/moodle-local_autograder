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

namespace local_autograder\task;

use local_autograder\local\decision\decision_repository;
use local_autograder\local\grading\grade_log_repository;

/**
 * Clears out decisions that are finished with, and grading log older than the
 * site's retention setting.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purge_history extends \core\task\scheduled_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:purge_history', 'local_autograder');
    }

    /**
     * Deletes everything settled longer ago than the retention setting.
     */
    public function execute(): void {
        $days = (int) get_config('local_autograder', 'retentiondays');

        if ($days <= 0) {
            // Keeping everything is a legitimate choice; treat it as one
            // rather than as "delete everything".
            return;
        }

        $before = time() - ($days * DAYSECS);

        $decisions = decision_repository::purge_settled_before($before);
        $logrows = grade_log_repository::purge_before($before);

        if ($decisions || $logrows) {
            mtrace("local_autograder: purged {$decisions} decision(s) and {$logrows} log row(s).");
        }
    }
}
