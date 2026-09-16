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
     * {@see CAPABILITY}, ordered by name — whether that comes from a role
     * assigned at system context, or one assigned in a single course and
     * nowhere else, and whether the role grants it by its own default or
     * only through an override made somewhere.
     *
     * Two searches, unioned, because neither alone covers both shapes:
     *
     * - {@see get_users_by_capability()} at system context is Moodle's own,
     *   fully correct resolution — it is what actually decides the question
     *   for a role assigned at system context, prohibits and every other
     *   subtlety included — but a context check only ever looks *up* the
     *   context tree, so it cannot see a role assigned down in one course.
     * - a plain scan of `role_capabilities` for a role that grants it
     *   `CAP_ALLOW` anywhere, joined to `role_assignments`, is what finds
     *   that course-only teacher — every role that could ever grant it,
     *   in one cheap query, rather than one call per course on the site.
     *   It cannot weigh a user's own assignment against the specific
     *   context an override was made in, so a role prevented from grading
     *   in one particular course still names a teacher whose only
     *   assignment is that course — the trade a single, site-wide query
     *   makes instead of one per course.
     *
     * @return array<int, string> User id => fully-formatted name.
     */
    public static function eligible_users(): array {
        global $DB;

        $options = [];

        foreach (self::system_context_holders() as $user) {
            $options[(int) $user->id] = fullname($user);
        }

        foreach (self::role_based_holders() as $user) {
            $options[(int) $user->id] = fullname($user);
        }

        \core_collator::asort($options);

        return $options;
    }

    /**
     * Every user {@see get_users_by_capability()} itself says holds
     * {@see CAPABILITY} at system context — the authoritative answer for a
     * role assigned there, but blind to one assigned only in a course.
     *
     * @return \stdClass[]
     */
    private static function system_context_holders(): array {
        return get_users_by_capability(
            \context_system::instance(),
            self::CAPABILITY,
            'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename',
        );
    }

    /**
     * Every user holding a role that grants {@see CAPABILITY} `CAP_ALLOW`
     * somewhere, wherever that role happens to be assigned to them — the
     * only way to reach a teacher whose sole assignment is one course,
     * without a query per course on the whole site.
     *
     * @return \stdClass[]
     */
    private static function role_based_holders(): array {
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

        return $DB->get_records_sql($sql, $params);
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
