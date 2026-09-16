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

use local_autograder\local\grading\grader_picker;

/**
 * Finds the users the site may name as its fallback grader.
 *
 * A class of its own rather than a method on the setting, because the setting
 * extends `admin_setting` — which lives in `adminlib.php` and is not
 * autoloaded, so a web service that touched it died on the class before it
 * reached the query. The search is asked by the settings page and by the
 * picker's own web service, and neither has any business loading the admin
 * library to run a `SELECT`.
 *
 * Every answer here is bounded. Listing every eligible user was a row per
 * role assignment on the campus, a `fullname()` per row and a collation of
 * the lot — work that grew with the site for a control that only ever shows
 * a screenful.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class grader_search {
    /** @var int How many matches one search returns, whatever the campus holds. */
    public const LIMIT = 30;

    /**
     * The users matching a search, capped at a screenful.
     *
     * @param string $query What the administrator has typed; empty for the
     *                      first screenful.
     * @param int $limit
     * @return array<int, string> User id => fully-formatted name.
     */
    public static function search(string $query, int $limit = self::LIMIT): array {
        global $DB;

        $roleids = self::grading_roles();

        if ($roleids === []) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'ra');
        $where = '';
        $query = trim($query);

        if ($query !== '') {
            // Matched on the columns a name is actually made of, each of which
            // the user table indexes, rather than on a concatenation of them
            // that no index can serve.
            $branches = [];

            foreach (['u.firstname', 'u.lastname', 'u.email'] as $index => $column) {
                $key = 'q' . $index;
                $params[$key] = '%' . $DB->sql_like_escape($query) . '%';
                $branches[] = $DB->sql_like($column, ":{$key}", false);
            }

            $where = ' AND (' . implode(' OR ', $branches) . ')';
        }

        // Ordered and capped in SQL, so the cap keeps the same rows every time
        // rather than whatever the join happened to reach first, and so the
        // sort is not a collation of a hundred thousand strings in PHP.
        $users = $DB->get_records_sql(
            'SELECT DISTINCT ' . self::name_fields() . "
               FROM {user} u
               JOIN {role_assignments} ra ON ra.userid = u.id
              WHERE ra.roleid {$insql} AND u.deleted = 0 AND u.suspended = 0{$where}
           ORDER BY u.lastname, u.firstname, u.id",
            $params,
            0,
            max(1, $limit)
        );
        $options = [];

        foreach ($users as $user) {
            $options[(int) $user->id] = fullname($user);
        }

        return $options;
    }

    /**
     * The name of one eligible user, or null when they are not one.
     *
     * Asked of that user alone, rather than by building the whole eligible
     * list and looking in it — which is what made rendering the setting, and
     * saving it, cost the size of the campus.
     *
     * @param int $userid
     * @return string|null
     */
    public static function name_of(int $userid): ?string {
        global $DB;

        $roleids = self::grading_roles();

        if ($userid <= 0 || $roleids === []) {
            return null;
        }

        [$insql, $params] = $DB->get_in_or_equal($roleids, SQL_PARAMS_NAMED, 'ra');
        $params['userid'] = $userid;

        $user = $DB->get_record_sql(
            'SELECT ' . self::name_fields() . "
               FROM {user} u
              WHERE u.id = :userid AND u.deleted = 0 AND u.suspended = 0
                AND EXISTS (SELECT 1
                              FROM {role_assignments} ra
                             WHERE ra.userid = u.id AND ra.roleid {$insql})",
            $params
        );

        return $user ? fullname($user) : null;
    }

    /**
     * Every role allowed to put a grade on something, anywhere.
     *
     * The same set {@see teacher_source} picks a course's teachers from, so a
     * site that invents a corrector role can name one as its stand-in too. It
     * used to be `moodle/grade:edit` alone, which left exactly the roles this
     * plugin will happily choose as a teacher out of the list of who may be
     * chosen as the fallback.
     *
     * A scan of the role definitions rather than a capability check per
     * context: a teacher holds this through a role assigned in one course, and
     * `get_users_by_capability()` at system context only looks *up* the tree,
     * so it would miss them. The trade is that a role prevented from grading
     * in one particular course still counts here — the price of one query
     * instead of one per course on the site.
     *
     * @return int[]
     */
    private static function grading_roles(): array {
        global $DB;

        [$insql, $params] = $DB->get_in_or_equal(grader_picker::grade_capabilities(), SQL_PARAMS_NAMED);
        $params['allow'] = CAP_ALLOW;

        return array_map('intval', $DB->get_fieldset_sql(
            "SELECT DISTINCT rc.roleid
               FROM {role_capabilities} rc
              WHERE rc.capability {$insql} AND rc.permission = :allow",
            $params
        ));
    }

    /**
     * The columns {@see fullname()} needs.
     *
     * @return string
     */
    private static function name_fields(): string {
        return 'u.id, u.firstname, u.lastname, u.firstnamephonetic, '
            . 'u.lastnamephonetic, u.middlename, u.alternatename';
    }
}
