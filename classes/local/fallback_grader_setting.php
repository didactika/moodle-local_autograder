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

namespace local_autograder\local;

/**
 * The site setting for `fallback_grader`: a single user, chosen
 * from a searchable dropdown that only ever lists users who could plausibly
 * grade something — holders of `moodle/grade:edit` at system context.
 *
 * Renders as a plain `<select>` (so the setting works with JavaScript off)
 * progressively enhanced into a type-ahead search by `core/form-autocomplete`,
 * the same module Moodle's own pickers use, filtering client-side over the
 * options already in the list. If a site's pool of graders grows large enough
 * that a static list stops being practical, replace the plain option list with
 * an AJAX-backed instance of the same module — nothing else here would change.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
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
     * Every user this setting may ever point to: holders of
     * `moodle/grade:edit` at system context, ordered by name.
     *
     * @return array<int, string> User id => fully-formatted name.
     */
    public static function eligible_users(): array {
        $users = get_users_by_capability(
            \context_system::instance(),
            'moodle/grade:edit',
            'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename',
        );

        $options = [];

        foreach ($users as $user) {
            $options[(int) $user->id] = fullname($user);
        }

        \core_collator::asort($options);

        return $options;
    }

    /**
     * Whether a user is still one of {@see eligible_users()} — checked again
     * at save time, since the list rendered to the admin may be stale by the
     * time the form is submitted.
     *
     * @param int $userid
     * @return bool
     */
    private function is_eligible(int $userid): bool {
        return array_key_exists($userid, self::eligible_users());
    }

    /**
     * Renders the `<select>` and enhances it into a search box.
     *
     * @param mixed $data The current value.
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $current = (int) $data;
        $options = ['0' => get_string('setting:fallback_grader_none', 'local_autograder')] + self::eligible_users();
        $elementid = 'id_s_' . $this->name;

        $select = \html_writer::select($options, $this->get_full_name(), (string) $current, false, [
            'id' => $elementid,
            'class' => 'form-select',
        ]);

        // Progressive enhancement only — the plain select above already works
        // without it. See the class docblock for what to do once a static
        // option list stops being the right shape for this site.
        $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', [
            '#' . $elementid,
            false,
            '',
            get_string('setting:fallback_grader_placeholder', 'local_autograder'),
            false,
            false,
        ]);

        return format_admin_setting(
            $this,
            $this->visiblename,
            \html_writer::div($select, 'form-select-autocomplete'),
            $this->description,
            true,
            '',
            null,
            $query,
        );
    }
}
