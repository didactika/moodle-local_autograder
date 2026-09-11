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

namespace local_autograder\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * "Do not let the autograder post grades in my name" (plan.md §5.1, D7).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class optout_form extends \moodleform {
    /**
     * @inheritDoc
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('advcheckbox', 'optout', get_string('preference:optout', 'local_autograder'));
        $mform->addHelpButton('optout', 'preference:optout', 'local_autograder');
        $mform->setType('optout', PARAM_BOOL);

        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);

        $this->add_action_buttons(false, get_string('savechanges'));
    }
}
