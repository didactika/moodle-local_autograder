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

use local_autograder\local\config\config_repository;
use local_autograder\local\decision\decision_repository;
use local_autograder\local\grading\grader_picker;

/**
 * Catches up every student who already did the activity when autograder was
 * switched on for it.
 *
 * Without this, turning autograder on would only ever affect students who
 * complete or submit *afterwards* — the ones who finished last week would sit
 * there ungraded forever, which is exactly the case a teacher switching it on
 * is usually trying to solve.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catch_up_module extends \core\task\adhoc_task {
    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:catch_up_module', 'local_autograder');
    }

    /**
     * Makes sure every enrolled student who qualifies has a decision.
     */
    public function execute(): void {
        $data = $this->get_custom_data();
        $cmid = (int) ($data->cmid ?? 0);

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm) {
            return;
        }

        $config = config_repository::get_for_cm($cmid);

        if (!$config || empty($config->enabled)) {
            return;
        }

        $context = \context_module::instance($cmid);
        $enrolled = get_enrolled_users($context, '', 0, 'u.id', null, 0, 0, true);

        foreach ($enrolled as $user) {
            // Whoever grades this activity is not someone it grades.
            if (grader_picker::grades_this_module($cm, (int) $user->id)) {
                continue;
            }

            decision_repository::ensure($cm, $config, (int) $user->id);
        }
    }
}
