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
 * What this plugin listens to.
 *
 * Every one of these only makes autograder reconsider sooner than it otherwise
 * would; the task that grades re-reads all the conditions itself, so a missed
 * event costs promptness, never correctness.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    // A student completed the activity, or undid it.
    [
        'eventname' => '\core\event\course_module_completion_updated',
        'callback' => '\local_autograder\observer::completion_changed',
    ],

    // A student handed something in — what autograder counts from where
    // completion is not tracked.
    [
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback' => '\local_autograder\observer::submitted',
    ],
    [
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback' => '\local_autograder\observer::submitted',
    ],
    [
        'eventname' => '\mod_forum\event\post_created',
        'callback' => '\local_autograder\observer::submitted',
    ],
    // Opening a discussion fires this and not post_created, although its first
    // post is a post like any other — so a student who only ever opens
    // discussions would otherwise go unnoticed until the next sweep.
    [
        'eventname' => '\mod_forum\event\discussion_created',
        'callback' => '\local_autograder\observer::submitted',
    ],

    // A teacher marked a quiz question by hand: the quiz's own grade may now
    // take the place of autograder's.
    [
        'eventname' => '\mod_quiz\event\question_manually_graded',
        'callback' => '\local_autograder\observer::question_marked',
    ],

    // Somebody graded by hand: autograder stands down.
    [
        'eventname' => '\core\event\user_graded',
        'callback' => '\local_autograder\observer::graded',
    ],

    // The student is no longer there to be graded.
    [
        'eventname' => '\core\event\user_enrolment_deleted',
        'callback' => '\local_autograder\observer::enrolment_ended',
    ],
    [
        'eventname' => '\core\event\user_enrolment_updated',
        'callback' => '\local_autograder\observer::enrolment_updated',
    ],

    // Somebody became a student here — enrolled for the first time or again —
    // and may already have done the work.
    [
        'eventname' => '\core\event\role_assigned',
        'callback' => '\local_autograder\observer::role_assigned',
    ],

    // Group membership decides which group exception applies to whom.
    [
        'eventname' => '\core\event\group_member_added',
        'callback' => '\local_autograder\observer::group_membership_changed',
    ],
    [
        'eventname' => '\core\event\group_member_removed',
        'callback' => '\local_autograder\observer::group_membership_changed',
    ],

    // An exception moved somebody's deadline.
    [
        'eventname' => '\mod_assign\event\user_override_created',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_assign\event\user_override_updated',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_assign\event\user_override_deleted',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_assign\event\group_override_created',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_assign\event\group_override_updated',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_assign\event\group_override_deleted',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\user_override_created',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\user_override_updated',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\user_override_deleted',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\group_override_created',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\group_override_updated',
        'callback' => '\local_autograder\observer::override_changed',
    ],
    [
        'eventname' => '\mod_quiz\event\group_override_deleted',
        'callback' => '\local_autograder\observer::override_changed',
    ],

    // The activity's own dates may have moved.
    [
        'eventname' => '\core\event\course_module_updated',
        'callback' => '\local_autograder\observer::module_updated',
    ],

    // Nothing left to grade.
    [
        'eventname' => '\core\event\course_module_deleted',
        'callback' => '\local_autograder\observer::module_deleted',
    ],
    [
        'eventname' => '\core\event\course_reset_ended',
        'callback' => '\local_autograder\observer::course_reset',
    ],
];
