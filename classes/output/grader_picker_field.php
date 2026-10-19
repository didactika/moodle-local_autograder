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
 * The field of the fallback grader, drawn by a React component.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autograder\output;

use core\output\react_component_renderable;

/**
 * A search box for the user that grades when no teacher can: the component local_autograder/GraderPicker asks the server as the
 * administrator types, and writes the user it has chosen into the field that the settings page saves.
 */
class grader_picker_field implements react_component_renderable, \renderable {
    /**
     * @var string The name of the field that the form submits.
     */
    private string $name;

    /**
     * @var int The user that is chosen now, or 0.
     */
    private int $current;

    /**
     * @var string The name of that user, or an empty text.
     */
    private string $currentname;

    /**
     * Keep what the field starts from.
     *
     * @param string $name The name of the field that the form submits.
     * @param int $current The user that is chosen now, or 0.
     * @param string $currentname The name of that user.
     */
    public function __construct(string $name, int $current, string $currentname) {
        $this->name = $name;
        $this->current = $current;
        $this->currentname = $currentname;
    }

    /**
     * The React component that draws the field.
     *
     * @return string Name of the component inside the plugin.
     */
    #[\Override]
    public function get_react_component_name(): string {
        return 'local_autograder/GraderPicker';
    }

    /**
     * What the component starts from.
     *
     * @param \renderer_base $renderer Renderer.
     * @return \stdClass Properties of the component.
     */
    #[\Override]
    public function get_react_component_props(\renderer_base $renderer): \stdClass {
        unset($renderer);
        return (object) [
            'name' => $this->name,
            'current' => $this->current,
            'currentname' => $this->currentname,
            'strings' => [
                'none' => get_string('setting:fallback_grader_none', 'local_autograder'),
                'placeholder' => get_string('setting:fallback_grader_placeholder', 'local_autograder'),
                'loading' => get_string('setting:fallback_grader_searching', 'local_autograder'),
                'nomatches' => get_string('setting:fallback_grader_nomatches', 'local_autograder'),
                'clear' => get_string('clear'),
            ],
        ];
    }
}
