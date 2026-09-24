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
 * The scheduled tasks of this plugin.
 *
 * Grading itself is not here: each decision gets its own adhoc task, queued
 * for the exact moment it comes due. These tidy up after it, and tell the
 * teachers concerned about what it could not do.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_autograder\task\reconcile_pending',
        'blocking' => 0,
        'minute' => '*/30',
        'hour' => '*',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
    [
        'classname' => 'local_autograder\task\purge_history',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
    [
        // Weekly, on Monday morning: often enough to catch a problem while the
        // students it affects are still in the course, seldom enough not to be
        // noise. A site that wants it daily changes it in the task schedule
        // rather than in a setting of this plugin.
        'classname' => 'local_autograder\task\notify_failures',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '7',
        'day' => '*',
        'dayofweek' => '1',
        'month' => '*',
    ],
];
