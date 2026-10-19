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

/**
 * External function: allow or disallow Autograder for one activity type, site-wide.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autograder\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_autograder\local\config\eligibility;

/**
 * The page of the activity types calls it when a switch is flipped.
 */
final class set_module_type extends external_api {
    /**
     * Describe the parameters.
     *
     * @return external_function_parameters Description.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'modname' => new external_value(PARAM_PLUGIN, 'The activity type, such as assign'),
            'enabled' => new external_value(PARAM_BOOL, 'Whether Autograder may be turned on for it'),
        ]);
    }

    /**
     * Allow or disallow the activity type.
     *
     * @param string $modname The activity type.
     * @param bool $enabled Whether Autograder may be turned on for it.
     * @return array The state it has now.
     */
    public static function execute(string $modname, bool $enabled): array {
        $params = self::validate_parameters(self::execute_parameters(), ['modname' => $modname, 'enabled' => $enabled]);

        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/autograder:manage', $context);

        if (!in_array($params['modname'], eligibility::gradeable_module_types(), true)) {
            throw new \invalid_parameter_exception('Autograder cannot be used with ' . $params['modname'] . '.');
        }
        eligibility::set_module_type_enabled($params['modname'], (bool) $params['enabled']);

        return ['modname' => $params['modname'], 'enabled' => eligibility::is_module_type_enabled($params['modname'])];
    }

    /**
     * Describe the returned structure.
     *
     * @return external_single_structure Description.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'modname' => new external_value(PARAM_PLUGIN, 'The activity type'),
            'enabled' => new external_value(PARAM_BOOL, 'The state it has now'),
        ]);
    }
}
