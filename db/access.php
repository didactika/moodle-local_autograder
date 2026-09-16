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
 * Capabilities this plugin defines.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// There is deliberately no "may be graded on behalf of" capability here any
// more. Whether somebody may grade is a question Moodle already answers —
// `moodle/grade:edit` — and answering it a second time was worse, not better:
// a user with two roles, one granting it and one not, was judged by whichever
// this plugin happened to look at, where has_capability() resolves the pair
// properly. The only thing left on top of Moodle's answer is the user's own
// preference asking not to be chosen; everybody else is assumed willing.
$capabilities = [
    // Turning autograder on or off for one activity, from its settings form.
    'local/autograder:configure' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],

    // The site-wide "which activity types can have autograder" page, and any
    // other global management surface this plugin adds.
    'local/autograder:manage' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ],
    ],
];
