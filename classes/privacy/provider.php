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

namespace local_autograder\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use local_autograder\local\decision\decision_repository;

/**
 * What this plugin holds about people, and how to hand it over or remove it.
 *
 * Three kinds of person appear in these tables and all three are covered: the
 * student a decision is about, the teacher a grade was posted as, and the
 * teacher who configured autograder on an activity. A teacher who never asked
 * to grade on anyone's behalf still shows up as the second of those, so
 * leaving them out would have made an export incomplete.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\user_preference_provider {
    /**
     * Describes the tables and the preference this plugin keeps.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_autograder_config',
            [
                'cmid' => 'privacy:metadata:config:cmid',
                'usermodified' => 'privacy:metadata:config:usermodified',
                'timemodified' => 'privacy:metadata:config:timemodified',
            ],
            'privacy:metadata:config'
        );

        $collection->add_database_table(
            'local_autograder_decision',
            [
                'userid' => 'privacy:metadata:decision:userid',
                'status' => 'privacy:metadata:decision:status',
                'baselineduedate' => 'privacy:metadata:decision:baselineduedate',
                'duedatereason' => 'privacy:metadata:decision:duedatereason',
                'scheduledgradetime' => 'privacy:metadata:decision:scheduledgradetime',
                'graderid' => 'privacy:metadata:decision:graderid',
                'gradedvalue' => 'privacy:metadata:decision:gradedvalue',
                'failurereason' => 'privacy:metadata:decision:failurereason',
                'timemodified' => 'privacy:metadata:decision:timemodified',
            ],
            'privacy:metadata:decision'
        );

        $collection->add_database_table(
            'local_autograder_grade_log',
            [
                'userid' => 'privacy:metadata:gradelog:userid',
                'outcome' => 'privacy:metadata:gradelog:outcome',
                'graderid' => 'privacy:metadata:gradelog:graderid',
                'gradevalue' => 'privacy:metadata:gradelog:gradevalue',
                'message' => 'privacy:metadata:gradelog:message',
                'timecreated' => 'privacy:metadata:gradelog:timecreated',
            ],
            'privacy:metadata:gradelog'
        );

        $collection->add_user_preference(
            'local_autograder_optout',
            'privacy:metadata:preference:optout'
        );

        return $collection;
    }

    /**
     * The activities where this user appears in any of the three roles.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_autograder_decision} d ON d.cmid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND (d.userid = :userid OR d.graderid = :graderid)";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
            'graderid' => $userid,
        ]);

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_autograder_grade_log} l ON l.cmid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND (l.userid = :userid OR l.graderid = :graderid)";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
            'graderid' => $userid,
        ]);

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_autograder_config} c ON c.cmid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND c.usermodified = :userid";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Everyone this plugin holds something about in one activity.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $params = ['cmid' => $context->instanceid];

        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {local_autograder_decision} WHERE cmid = :cmid',
            $params
        );
        $userlist->add_from_sql(
            'graderid',
            'SELECT graderid FROM {local_autograder_decision} WHERE cmid = :cmid AND graderid IS NOT NULL',
            $params
        );
        $userlist->add_from_sql(
            'userid',
            'SELECT userid FROM {local_autograder_grade_log} WHERE cmid = :cmid',
            $params
        );
        $userlist->add_from_sql(
            'graderid',
            'SELECT graderid FROM {local_autograder_grade_log} WHERE cmid = :cmid AND graderid IS NOT NULL',
            $params
        );
        $userlist->add_from_sql(
            'usermodified',
            'SELECT usermodified FROM {local_autograder_config} WHERE cmid = :cmid',
            $params
        );
    }

    /**
     * Writes out what this plugin holds about one person.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = (int) $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            $cmid = (int) $context->instanceid;
            $subcontext = [get_string('pluginname', 'local_autograder')];

            $decisionrows = $DB->get_records_select(
                'local_autograder_decision',
                'cmid = :cmid AND (userid = :userid OR graderid = :graderid)',
                ['cmid' => $cmid, 'userid' => $userid, 'graderid' => $userid],
                'timemodified ASC'
            );
            $decisions = [];

            foreach ($decisionrows as $decision) {
                $decisions[] = (object) [
                    'status' => $decision->status,
                    'duedatereason' => $decision->duedatereason,
                    'baselineduedate' => transform::datetime($decision->baselineduedate),
                    'scheduledgradetime' => transform::datetime($decision->scheduledgradetime),
                    'gradedvalue' => $decision->gradedvalue,
                    'failurereason' => $decision->failurereason,
                    'wasgradedas' => transform::yesno((int) $decision->graderid === $userid),
                    'timemodified' => transform::datetime($decision->timemodified),
                ];
            }

            // Written once, as a list, rather than once per row. A subcontext
            // is a path to a `data.json`, so exporting each decision to the
            // same path overwrites the one before it — and a teacher who
            // graded thirty students here is on thirty of these rows.
            if (!empty($decisions)) {
                writer::with_context($context)->export_data(
                    array_merge($subcontext, [get_string('privacy:path:decision', 'local_autograder')]),
                    (object) ['decisions' => $decisions]
                );
            }

            $logrows = $DB->get_records_select(
                'local_autograder_grade_log',
                'cmid = :cmid AND (userid = :userid OR graderid = :graderid)',
                ['cmid' => $cmid, 'userid' => $userid, 'graderid' => $userid],
                'timecreated ASC'
            );
            $log = [];

            foreach ($logrows as $row) {
                $log[] = (object) [
                    'outcome' => $row->outcome,
                    'gradevalue' => $row->gradevalue,
                    'message' => $row->message,
                    'wasgradedas' => transform::yesno((int) $row->graderid === $userid),
                    'timecreated' => transform::datetime($row->timecreated),
                ];
            }

            if (!empty($log)) {
                writer::with_context($context)->export_data(
                    array_merge($subcontext, [get_string('privacy:path:gradelog', 'local_autograder')]),
                    (object) ['entries' => $log]
                );
            }

            $config = $DB->get_record('local_autograder_config', ['cmid' => $cmid, 'usermodified' => $userid]);

            if ($config) {
                writer::with_context($context)->export_data(
                    array_merge($subcontext, [get_string('privacy:path:config', 'local_autograder')]),
                    (object) [
                        'enabled' => transform::yesno($config->enabled),
                        'grademethod' => $config->grademethod,
                        'timemodified' => transform::datetime($config->timemodified),
                    ]
                );
            }
        }
    }

    /**
     * Writes out the one preference this plugin keeps.
     *
     * @param int $userid
     */
    public static function export_user_preferences(int $userid): void {
        $optout = get_user_preferences('local_autograder_optout', null, $userid);

        if ($optout === null) {
            return;
        }

        writer::export_user_preference(
            'local_autograder',
            'local_autograder_optout',
            transform::yesno($optout),
            get_string('privacy:metadata:preference:optout', 'local_autograder')
        );
    }

    /**
     * Removes everything this plugin holds about everyone in one activity.
     *
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        if (!$context instanceof \context_module) {
            return;
        }

        self::purge_cm((int) $context->instanceid);
    }

    /**
     * Removes what this plugin holds about one person, wherever they approved.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $userid = (int) $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof \context_module) {
                self::purge_users_in_cm((int) $context->instanceid, [$userid]);
            }
        }
    }

    /**
     * Removes what this plugin holds about a named set of people in one
     * activity.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        self::purge_users_in_cm((int) $context->instanceid, $userlist->get_userids());
    }

    /**
     * Everything about one activity, decisions, log and configuration alike.
     *
     * @param int $cmid
     */
    private static function purge_cm(int $cmid): void {
        global $DB;

        // Anything queued for a decision that is about to stop existing has
        // to go with it, or the task would fire on a row that is not there.
        foreach ($DB->get_records('local_autograder_decision', ['cmid' => $cmid]) as $decision) {
            decision_repository::unschedule($decision);
        }

        $DB->delete_records('local_autograder_decision', ['cmid' => $cmid]);
        $DB->delete_records('local_autograder_grade_log', ['cmid' => $cmid]);
        $DB->delete_records('local_autograder_config', ['cmid' => $cmid]);
    }

    /**
     * Everything about named people in one activity.
     *
     * The configuration is not deleted with them, only unattributed: it is a
     * setting of the activity, not of the teacher who happened to save it, and
     * removing it would switch autograder off for every other student there.
     *
     * @param int $cmid
     * @param int[] $userids
     */
    private static function purge_users_in_cm(int $cmid, array $userids): void {
        global $DB;

        if (empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['cmid'] = $cmid;

        $decisions = $DB->get_records_select(
            'local_autograder_decision',
            "cmid = :cmid AND userid {$insql}",
            $params
        );

        foreach ($decisions as $decision) {
            decision_repository::unschedule($decision);
        }

        $DB->delete_records_select('local_autograder_decision', "cmid = :cmid AND userid {$insql}", $params);
        $DB->delete_records_select('local_autograder_grade_log', "cmid = :cmid AND userid {$insql}", $params);

        // Somebody else's decision that names this person as its grader keeps
        // the decision but loses the name.
        $DB->set_field_select(
            'local_autograder_decision',
            'graderid',
            null,
            "cmid = :cmid AND graderid {$insql}",
            $params
        );
        $DB->set_field_select(
            'local_autograder_grade_log',
            'graderid',
            null,
            "cmid = :cmid AND graderid {$insql}",
            $params
        );
        $DB->set_field_select(
            'local_autograder_config',
            'usermodified',
            0,
            "cmid = :cmid AND usermodified {$insql}",
            $params
        );
    }
}
