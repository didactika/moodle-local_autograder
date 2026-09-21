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

/**
 * The safety net, not the normal path.
 *
 * Grading is driven by a task queued for each decision's own moment. This
 * looks only for decisions that are already due and have no task behind them
 * any more — a queue emptied by hand, a site moved between servers, a bug —
 * and queues one again. On a healthy site it finds nothing.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reconcile_pending extends \core\task\scheduled_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:reconcile_pending', 'local_autograder');
    }

    /**
     * Requeues whatever fell through.
     */
    public function execute(): void {
        $orphans = decision_repository::orphaned_pending(time());

        foreach ($orphans as $decision) {
            decision_repository::schedule($decision);
        }

        if (!empty($orphans)) {
            mtrace('local_autograder: requeued ' . count($orphans) . ' decision(s) that had lost their task.');
        }

        if (count($orphans) === decision_repository::RECONCILE_BATCH) {
            // A full batch means there were probably more. Said out loud so
            // that a site rebuilding a large backlog can see it is working
            // through it rather than stuck.
            mtrace('local_autograder: the batch was full; the next run will continue.');
        }
    }
}
