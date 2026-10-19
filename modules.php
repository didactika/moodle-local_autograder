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
 * Which activity types Autograder may be switched on for.
 *
 * Deliberately shaped like core's own "Manage activities" (`admin/modules.php`): one row per gradeable activity type, name and a
 * switch, nothing else, because nothing else means anything for this page's one job. The page is drawn by the React component
 * local_autograder/Modules, which saves each switch as it is flipped.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_autograder\output\modules_page;

admin_externalpage_setup('local_autograder_modules_page');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings:modulestab', 'local_autograder'));
// The element that the component fills in is empty, so the placeholder goes right before its closing tag.
echo preg_replace('~</div>\s*$~', modules_page::placeholder() . '</div>', $OUTPUT->render(new modules_page()), 1);
echo $OUTPUT->footer();
