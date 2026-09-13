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
 * What every announcement about one student's autograder decision has in
 * common.
 *
 * Nothing inside this plugin listens to these — they exist for
 * `report_autograder`, auditing, and any integration that wants to know what
 * autograder did and to whom.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class decision_event_base extends \core\event\base {
    /**
     * Describes what kind of event this is.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'local_autograder_decision';
    }

    /**
     * Where the activity this decision belongs to lives.
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/' . $this->other['modname'] . '/view.php', [
            'id' => $this->contextinstanceid,
        ]);
    }

    /**
     * Checks the event carries what it must before it is fired.
     *
     * @throws \coding_exception When the student or the module type is missing.
     */
    protected function validate_data(): void {
        parent::validate_data();

        if ($this->contextlevel !== CONTEXT_MODULE) {
            throw new \coding_exception('A decision event must carry a module context.');
        }

        if (empty($this->relateduserid)) {
            throw new \coding_exception('A decision event must name the student it is about.');
        }

        if (empty($this->other['modname'])) {
            throw new \coding_exception('A decision event must carry other[modname].');
        }
    }

    /**
     * Which fields of `other` hold ids a restore would have to remap.
     *
     * @return bool False: they are a module name and plain values.
     */
    public static function get_other_mapping() {
        return false;
    }
}
