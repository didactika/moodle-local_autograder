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

use local_autograder\local\eligibility;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * One switch per gradeable module type, site-wide (plan.md §9, D4).
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enabled_modules_form extends \moodleform {
    /**
     * @inheritDoc
     */
    protected function definition() {
        $mform = $this->_form;
        $enabled = eligibility::enabled_module_types();

        $mform->addElement('header', 'general', get_string('settings:modulestab', 'local_autograder'));
        $mform->addElement('static', 'intro', '', get_string('modules:intro', 'local_autograder'));

        foreach (eligibility::gradeable_module_types() as $modname) {
            $mform->addElement(
                'advcheckbox',
                'modname_' . $modname,
                get_string('modulename', $modname),
            );
            $mform->setDefault('modname_' . $modname, in_array($modname, $enabled, true) ? 1 : 0);
        }

        $this->add_action_buttons(false, get_string('savechanges'));
    }

    /**
     * The module types the submitted form has checked, in canonical order.
     *
     * @param \stdClass $data Result of {@see moodleform::get_data()}.
     * @return string[]
     */
    public static function enabled_from_submission(\stdClass $data): array {
        $enabled = [];

        foreach (eligibility::gradeable_module_types() as $modname) {
            if (!empty($data->{'modname_' . $modname})) {
                $enabled[] = $modname;
            }
        }

        return $enabled;
    }
}
