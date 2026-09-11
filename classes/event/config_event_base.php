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

namespace local_autograder\event;

/**
 * What every announcement about a module's autograder configuration has in
 * common (plan.md §10.1, D10). Nothing inside this plugin listens to these —
 * they exist for `report_autograder`, auditing, and any future integration.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class config_event_base extends \core\event\base {
    /**
     * Describes what kind of event this is.
     */
    protected function init(): void {
        $this->data['crud'] = $this->crud_letter();
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'local_autograder_config';
    }

    /**
     * The letter core files this kind of event under.
     *
     * @return string One of `c`, `u` or `d`.
     */
    abstract protected function crud_letter(): string;

    /**
     * Where the module this configuration belongs to is edited.
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/course/modedit.php', ['update' => $this->contextinstanceid]);
    }

    /**
     * Checks the event carries what it must before it is fired.
     *
     * @throws \coding_exception When the context is not a module context.
     */
    protected function validate_data(): void {
        parent::validate_data();

        if ($this->contextlevel !== CONTEXT_MODULE) {
            throw new \coding_exception('A config event must carry a module context.');
        }
    }
}
