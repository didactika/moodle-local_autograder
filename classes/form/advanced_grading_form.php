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
 * Which level of each rubric criterion — or which score on each marking-guide
 * criterion — autograder marks.
 *
 * This is core's own grading element, the same rubric grid or marking guide a
 * teacher fills in when grading a student by hand: same layout, same level
 * buttons, same remark boxes, same running total, same validation messages.
 * That is the whole point — autograder is meant to grade exactly as a person
 * would, so what it is told to mark should be said in exactly the same way,
 * and a teacher should not have to learn a second, lookalike form to say it.
 *
 * It also means the value this form submits is already in the shape
 * `gradingform_instance::submit_and_get_grade()` expects, so there is nothing
 * to translate on the way in or on the way out.
 *
 * The activity's settings form cannot host this: picking "Rubric" there does
 * not create a rubric, it only says one will be used, and the definition is
 * written afterwards on Moodle's own advanced grading page.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advanced_grading_form extends \moodleform {
    /**
     * The real grading form, plus the id of the activity it belongs to.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement(
            'grading',
            'advancedgrading',
            get_string('advanced:marks', 'local_autograder'),
            ['gradinginstance' => $this->_customdata['gradinginstance']],
        );

        $this->add_action_buttons(true, get_string('advanced:save', 'local_autograder'));
    }
}
