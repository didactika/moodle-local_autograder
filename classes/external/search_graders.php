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

namespace local_autograder\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use local_autograder\local\config\grader_search;

/**
 * Searches for a user who could stand in as the site's fallback grader.
 *
 * Asked from the settings page as the administrator types, rather than the
 * page listing everybody who qualifies. On a campus with a hundred thousand
 * teachers that list was a hundred thousand `<option>` elements, every one of
 * their names formatted and collated in PHP before the settings page could
 * render at all — and it grew with the campus. This answers the same question
 * a screenful at a time, and costs the same whatever the campus holds.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class search_graders extends external_api {
    /**
     * What this function accepts.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'query' => new external_value(PARAM_TEXT, 'What the administrator has typed', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * What this function returns.
     *
     * @return external_multiple_structure
     */
    public static function execute_returns(): external_multiple_structure {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'The user'),
                'name' => new external_value(PARAM_TEXT, 'What to show for them'),
            ])
        );
    }

    /**
     * The users matching a search, capped.
     *
     * @param string $query
     * @return array
     */
    public static function execute(string $query): array {
        $params = self::validate_parameters(self::execute_parameters(), ['query' => $query]);

        // Only somebody who could set this setting may search it.
        self::validate_context(\context_system::instance());
        require_capability('moodle/site:config', \context_system::instance());

        $found = [];

        foreach (grader_search::search($params['query']) as $id => $name) {
            $found[] = ['id' => $id, 'name' => $name];
        }

        return $found;
    }
}
