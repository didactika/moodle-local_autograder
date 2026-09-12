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

use local_autograder\local\decision_repository;

/**
 * Calls off everything autograder still had queued for an activity, because
 * it has been switched off for it.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cancel_module extends \core\task\adhoc_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:cancel_module', 'local_autograder');
    }

    /**
     * Cancels every pending decision on the activity.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $cmid = (int) ($data->cmid ?? 0);
        $reason = (string) ($data->reason ?? 'autograderoff');

        if ($cmid === 0) {
            return;
        }

        decision_repository::cancel_all_for_cm($cmid, $reason);
    }
}
