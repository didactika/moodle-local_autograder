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
 * The page of the activity types, drawn by a React component.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autograder\output;

use core\output\react_component_renderable;
use local_autograder\local\config\eligibility;

/**
 * The activity types that Autograder may be switched on for: the component local_autograder/Modules draws one switch for each.
 */
class modules_page implements react_component_renderable, \renderable {
    /**
     * The React component that draws the page.
     *
     * @return string Name of the component inside the plugin.
     */
    #[\Override]
    public function get_react_component_name(): string {
        return 'local_autograder/Modules';
    }

    /**
     * What the component starts from.
     *
     * @param \renderer_base $renderer Renderer.
     * @return \stdClass Properties of the component.
     */
    #[\Override]
    public function get_react_component_props(\renderer_base $renderer): \stdClass {
        $enabled = eligibility::enabled_module_types();
        $types = [];
        foreach (eligibility::gradeable_module_types() as $type) {
            $types[] = [
                'type' => $type,
                'name' => get_string('modulename', $type),
                'iconurl' => $renderer->image_url('monologo', 'mod_' . $type)->out(false),
                'enabled' => in_array($type, $enabled, true),
            ];
        }
        return (object) ['types' => $types, 'strings' => self::strings()];
    }

    /**
     * What holds the place of the page until its component is there: one grey block for each row.
     *
     * @return string The HTML.
     */
    public static function placeholder(): string {
        return '<div class="lag" aria-hidden="true">' .
            str_repeat('<div class="lag-sk-row"><span class="lag-sk lag-sk-line"></span></div>', 6) . '</div>' .
            '<span class="sr-only visually-hidden" role="status">' . get_string('loading') . '</span>';
    }

    /**
     * Every string of the plugin, in the language of the person, with the places of the values written the way the script fills.
     *
     * A value that stands alone is written {n} and the fields of an object {name}, so the script has one way to put them in.
     *
     * @return string[] Texts by name.
     */
    public static function strings(): array {
        $strings = get_string_manager()->load_component_strings('local_autograder', current_language());
        foreach ($strings as $name => $value) {
            $strings[$name] = preg_replace(['/\{\$a->([a-z]+)\}/', '/\{\$a\}/'], ['{$1}', '{n}'], $value);
        }
        return $strings;
    }
}
