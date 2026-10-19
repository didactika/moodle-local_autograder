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

namespace local_autograder\local\config;

/**
 * The site setting for `fallback_grader`: a single user, chosen from a
 * searchable dropdown that only ever lists users who could plausibly grade
 * something — holders of a role granting one of the capabilities a grade is
 * written through, wherever that role was assigned. Site administrators are
 * not among them, here or at grading time.
 *
 * Such a capability is almost never held at system context: a teacher holds it
 * the way they hold any other teaching capability, through a role assigned in
 * one course (or a category), not a role assigned site-wide.
 * `context_system::instance()` alone would list only genuine site-wide holders
 * — managers, mostly — and leave the picker looking empty on an ordinary site.
 * See {@see grader_search} for how the search actually looks past that.
 *
 * Renders as a plain `<select>` holding only the user already chosen, so the
 * setting still submits with JavaScript off, enhanced into a type-ahead search
 * by `core/form-autocomplete` reading `local_autograder/grader_search`. The
 * list it offers comes from the server a screenful at a time: it used to hold
 * every eligible user on the site, which on a large one meant a settings page
 * carrying a hundred thousand options, each one's name formatted and collated
 * in PHP first.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fallback_grader_setting extends \admin_setting {
    /**
     * Whether the setting has ever been given a value.
     *
     * @return bool
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Stores the chosen user id, refusing one without the capability.
     *
     * @param mixed $data The submitted user id.
     * @return string Empty string on success, an error message otherwise.
     */
    public function write_setting($data) {
        $userid = (int) $data;

        if ($userid !== 0 && !$this->is_eligible($userid)) {
            return get_string('setting:fallback_grader_ineligible', 'local_autograder');
        }

        return ($this->config_write($this->name, $userid) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Whether a user may still be pointed at, checked again at save time.
     *
     * @param int $userid
     * @return bool
     */
    private function is_eligible(int $userid): bool {
        return grader_search::name_of($userid) !== null;
    }

    /**
     * The field: a search box that asks the server for users as the administrator types.
     *
     * @param mixed $data The user that is chosen now.
     * @param string $query What the settings search was asked for.
     * @return string The HTML of the setting.
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $current = (int) $data;
        $field = new \local_autograder\output\grader_picker_field(
            $this->get_full_name(),
            $current,
            (string) (grader_search::name_of($current) ?? '')
        );

        return format_admin_setting(
            $this,
            $this->visiblename,
            \html_writer::div($OUTPUT->render($field), 'form-select-autocomplete'),
            $this->description,
            true,
            '',
            null,
            $query,
        );
    }
}
