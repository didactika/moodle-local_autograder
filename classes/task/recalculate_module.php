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

use local_autograder\local\config_repository;
use local_autograder\local\decision_repository;

/**
 * Works out every waiting decision on an activity again, and moves the tasks
 * behind them.
 *
 * Queued whenever something happened that could change *when* a student is
 * due to be graded rather than whether: the activity's own dates changed, an
 * exception was added or lifted, a student joined or left a group that has
 * one.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recalculate_module extends \core\task\adhoc_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:recalculate_module', 'local_autograder');
    }

    /**
     * Re-plans each pending decision, rescheduling the ones whose moment moved.
     */
    public function execute(): void {
        $data = $this->get_custom_data();

        self::run_for_cm((int) ($data->cmid ?? 0));
    }

    /**
     * Works out every waiting decision on one activity again, now.
     *
     * Separate from execute() so that the settings form can do it on the spot
     * rather than only through the queue. A teacher who has just shortened the
     * wait opens the report to see the new dates, and "it will be right after
     * the next cron run" is not an answer they can see.
     *
     * @param int $cmid
     * @return int How many decisions were looked at.
     */
    public static function run_for_cm(int $cmid): int {
        if ($cmid === 0) {
            return 0;
        }

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return 0;
        }

        $config = config_repository::get_for_cm($cmid);

        if (!$config || empty($config->enabled)) {
            return 0;
        }

        $pending = decision_repository::pending_for_cm($cmid);

        foreach ($pending as $decision) {
            decision_repository::ensure($cm, $config, (int) $decision->userid);
        }

        return count($pending);
    }
}
