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
 * The site setting for `fallback_grader`: a single user, chosen
 * from a searchable dropdown that only ever lists users who could plausibly
 * grade something — holders of `local/autograder:gradeonbehalf`, wherever
 * that actually comes from.
 *
 * Almost never at system context: a teacher holds this the way they hold
 * any other teaching capability, through a role assigned in one course (or a
 * category), not a role assigned site-wide. `context_system::instance()`
 * alone would list only genuine site-wide holders — managers, mostly — and
 * leave the picker looking empty on an ordinary site. See
 * {@see eligible_users()} for how the search actually looks past that.
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
    /** The capability {@see eligible_users()} looks for a holder of. */
    private const CAPABILITY = 'local/autograder:gradeonbehalf';

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
     * {@see CAPABILITY}, ordered by name.
     *
     * Found by role rather than by asking each context in turn: every role
     * that grants it — by its own default, or through an override made
     * anywhere, in either direction — names itself in `role_capabilities`
     * regardless of which context the override was made in, so one query
     * against that table finds every such role, and a second finds every
     * user holding one of them, in any context at all. What this does not
     * do is weigh a user's *own* assignment against the specific context it
     * was made in — a role prevented from grading in one particular course
     * still lists a teacher whose only assignment is that course, the same
     * approximation {@see get_users_by_capability()} itself would not make
     * for a single context, but the only one that stays a single, cheap
     * query across the whole site instead of one per course.
     *
     * @return array<int, string> User id => fully-formatted name.
     */
    public static function eligible_users(): array {
        global $DB;

        $roleids = $DB->get_fieldset_select(
            'role_capabilities',
            'DISTINCT roleid',
            'capability = :capability AND permission = :allow',
            ['capability' => self::CAPABILITY, 'allow' => CAP_ALLOW],
        );

        if (empty($roleids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED);

        $fields = 'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename';
        $sql = "SELECT DISTINCT {$fields}"
            . ' FROM {user} u'
            . ' JOIN {role_assignments} ra ON ra.userid = u.id'
            . " WHERE ra.roleid {$insql} AND u.deleted = 0 AND u.suspended = 0";

        $users = $DB->get_records_sql($sql, $params);

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
