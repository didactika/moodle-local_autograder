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
 * Upgrade steps are defined here.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Runs every upgrade step this plugin has needed.
 *
 * @param int $oldversion The version being upgraded from.
 * @return bool Always true.
 */
function xmldb_local_autograder_upgrade($oldversion) {
    if ($oldversion < 2026101902) {
        // Autograder is only for the activities that take the work of students and have a grade that a teacher gives, which
        // the site no longer chooses from a list of every gradeable type: take the other types off the ones it allows and
        // call off what was waiting for them. The configuration of their activities stays saved, and so does every grade.
        \local_autograder\local\config\eligibility::withdraw_unsupported_types();

        upgrade_plugin_savepoint(true, 2026101902, 'local', 'autograder');
    }

    return true;
}
