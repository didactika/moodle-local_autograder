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
 * English language strings.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Define this activity\'s rubric or marking guide first, then come back to say which levels Autograder should mark.';
$string['advanced:edit_definition'] = 'To change the rubric or marking guide itself, <a href="{$a}">edit its definition</a>.';
$string['advanced:heading'] = 'Levels Autograder marks';
$string['advanced:intro'] = 'Fill this in exactly as you would when grading a student of <strong>{$a}</strong> by hand. Autograder marks what you choose here, and Moodle works the grade out from it the same way it always does.';
$string['advanced:marks'] = 'What Autograder marks';
$string['advanced:not_advanced'] = 'This activity is not graded by a rubric or a marking guide.';
$string['advanced:save'] = 'Save what Autograder marks';
$string['advanced:saved'] = 'Saved what Autograder will mark.';
$string['autograder:configure'] = 'Turn Autograder on or off for an activity';
$string['autograder:manage'] = 'Manage Autograder site-wide settings';
$string['error:advancedgradingstale'] = 'The rubric or marking guide has changed since Autograder was told what to mark on it. Open the activity\'s Autograder settings and choose the levels again.';
$string['error:gradewritefailed'] = 'Moodle refused the grade Autograder tried to post.';
$string['error:nogradeitem'] = 'This activity has no grade item to write to.';
$string['event:config_created'] = 'Autograder configuration created';
$string['event:config_deleted'] = 'Autograder configuration deleted';
$string['event:config_updated'] = 'Autograder configuration updated';
$string['event:decision_cancelled'] = 'Autograder decision cancelled';
$string['event:grading_failed'] = 'Autograder grading failed';
$string['event:student_graded'] = 'Student graded by Autograder';
$string['form:advanced_define_first'] = 'This activity is graded by a rubric or marking guide, but none is defined yet. <a href="{$a}">Define it first</a>, then choose what Autograder marks.';
$string['form:advanced_set'] = 'Autograder knows what to mark on this rubric or guide. <a href="{$a}">Change it</a>.';
$string['form:advanced_undefined'] = 'This activity is graded by a rubric or marking guide that Autograder cannot read.';
$string['form:advanced_unset'] = 'Choose <a href="{$a}">what Autograder marks</a> on this rubric or guide — until then it has nothing to grade with.';
$string['form:days_to_complete'] = 'Days';
$string['form:enabled'] = 'Enable Autograder';
$string['form:enabled_help'] = 'When enabled, a student who completes this activity is graded automatically, the configured time after it was due, with the grade set below — unless someone grades them by hand first.';
$string['form:error_advanced_unset'] = 'Autograder has not been told what to mark on this activity\'s rubric or marking guide. <a href="{$a}">Choose that first</a>, then switch Autograder on.';
$string['form:error_grade_above_max'] = 'This activity is graded out of {$a}, so the grade cannot be higher than that.';
$string['form:error_grade_negative'] = 'The grade cannot be negative.';
$string['form:error_grade_required'] = 'Enter the grade Autograder should give.';
$string['form:error_hours_range'] = 'Enter 0 to 23 hours. Use the days field for anything longer.';
$string['form:error_minutes_range'] = 'Enter 0 to 59 minutes. Use the hours field for anything longer.';
$string['form:error_negative_time'] = 'Cannot be negative.';
$string['form:error_not_graded'] = 'Autograder needs this activity to be graded. Choose a grade type other than "None".';
$string['form:error_numeric'] = 'Must be a number.';
$string['form:error_scale_mismatch'] = 'That item does not belong to the scale this activity now uses. Choose one from the scale you just selected.';
$string['form:error_scale_unset'] = 'Choose which scale item Autograder should assign.';
$string['form:error_whole_number'] = 'Enter a whole number.';
$string['form:grade'] = 'Grade to assign';
$string['form:grade_range'] = 'This activity is graded out of {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Hours';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:notenabled_advanced'] = 'Autograder was left switched off: it has not been told what to mark on this activity\'s rubric or marking guide. <a href="{$a}">Choose that first</a>, then switch it on.';
$string['form:time_to_complete'] = 'Time to wait before grading';
$string['form:time_to_complete_help'] = 'How long to wait, after the due date, before grading.';
$string['messageprovider:failuredigest'] = 'Students Autograder could not grade';
$string['modules:intro'] = 'Autograder can only be turned on for activities of the types enabled here. Disabling a type hides the Autograder settings on those activities and cancels the grading still pending for them. Each activity keeps its own Autograder setting, so enabling the type again resumes them — except where a grade was already recorded.';
$string['notify:footer'] = 'You are receiving this because you can configure Autograder in these activities.';
$string['notify:intro'] = 'Since the last summary, Autograder could not grade some students in the activities below. Those students have not been graded.';
$string['notify:more'] = 'Activities not listed here: {$a}.';
$string['notify:reason_grade_write_failed'] = 'Moodle refused the grade. The usual causes are a rubric or marking guide that changed since Autograder was told what to mark on it, or a teacher who is not allowed to grade the activity.';
$string['notify:reason_line'] = 'Students affected: {$a->count}. {$a->reason}';
$string['notify:reason_no_grader'] = 'No teacher could be found to record the grade as, and there is no fallback grader to use instead.';
$string['notify:reason_other'] = 'The grade could not be recorded.';
$string['notify:small'] = 'Autograder could not grade some students. Activities affected: {$a}.';
$string['notify:subject'] = 'Autograder could not grade some students';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Autograder preferences';
$string['preference:notoffered'] = 'This site does not allow teachers to opt out of Autograder.';
$string['preference:optout'] = 'Never record automatic grades in my name';
$string['preference:optout_help'] = 'Autograder will never record grades as you, even in courses you teach.';
$string['preference:saved'] = 'Preference saved.';
$string['privacy:metadata'] = 'Autograder keeps, per student, whether and when they are due to be graded automatically, and a log of every grading attempt.';
$string['privacy:metadata:config'] = 'What a teacher configured Autograder to do on an activity.';
$string['privacy:metadata:config:cmid'] = 'The activity the configuration belongs to.';
$string['privacy:metadata:config:timemodified'] = 'When the configuration was last saved.';
$string['privacy:metadata:config:usermodified'] = 'The user who last saved the configuration.';
$string['privacy:metadata:core_message'] = 'Teachers can be sent a summary of the students Autograder could not grade, through the messaging system.';
$string['privacy:metadata:decision'] = 'One student\'s pending or settled Autograder decision on one activity.';
$string['privacy:metadata:decision:baselineduedate'] = 'The date the delay is counted from.';
$string['privacy:metadata:decision:duedatereason'] = 'Why that date was the one used: completion, submission, close date or an exception.';
$string['privacy:metadata:decision:failurereason'] = 'Why the decision was called off or could not be carried out.';
$string['privacy:metadata:decision:gradedvalue'] = 'The grade Autograder posted.';
$string['privacy:metadata:decision:graderid'] = 'The teacher the grade was posted as.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'When the grade is, or was, due to be posted.';
$string['privacy:metadata:decision:status'] = 'Whether the decision is waiting, graded, taken over by hand, cancelled or failed.';
$string['privacy:metadata:decision:timemodified'] = 'When the decision last changed.';
$string['privacy:metadata:decision:userid'] = 'The student the decision is about.';
$string['privacy:metadata:gradelog'] = 'A record of what Autograder did, and did not do, and why.';
$string['privacy:metadata:gradelog:graderid'] = 'The teacher the grade was posted as.';
$string['privacy:metadata:gradelog:gradevalue'] = 'The grade posted, where one was.';
$string['privacy:metadata:gradelog:message'] = 'What happened, in words.';
$string['privacy:metadata:gradelog:outcome'] = 'Whether the student was graded, skipped, cancelled or failed.';
$string['privacy:metadata:gradelog:timecreated'] = 'When it happened.';
$string['privacy:metadata:gradelog:userid'] = 'The student the entry is about.';
$string['privacy:metadata:preference:optout'] = 'Whether this user has asked not to be chosen as the teacher Autograder grades as.';
$string['privacy:path:config'] = 'Autograder configuration';
$string['privacy:path:decision'] = 'Autograder decisions';
$string['privacy:path:gradelog'] = 'Autograder history';
$string['setting:allowoptout'] = 'Allow teachers to opt out';
$string['setting:allowoptout_desc'] = 'Adds a preference page where users can ask Autograder never to record grades as them. On sites with few teachers, opt-outs can leave students with nobody to grade as. When off, the page is hidden and existing opt-outs are ignored.';
$string['setting:fallback_grader'] = 'Fallback grader';
$string['setting:fallback_grader_desc'] = 'Grades are recorded as this user when a teacher can\'t be selected based on automatic rules, or when grading as the chosen teacher fails. Only users with a grading role are listed; site administrators are never used. For assignments, this user must also be allowed to grade in that course. If None, those students are not graded and the failure is logged.';
$string['setting:fallback_grader_ineligible'] = 'This user can\'t be the fallback grader. Site administrators, and users with no role that can grade, are never used.';
$string['setting:fallback_grader_none'] = 'None';
$string['setting:fallback_grader_placeholder'] = 'Search for a user…';
$string['setting:grading_heading'] = 'Grading and history';
$string['setting:modules_disable'] = 'Disable Autograder for {$a}';
$string['setting:modules_disabled'] = 'Autograder disabled for {$a}.';
$string['setting:modules_enable'] = 'Enable Autograder for {$a}';
$string['setting:modules_enabled'] = 'Autograder enabled for {$a}.';
$string['setting:modules_enabled_column'] = 'Enabled';
$string['setting:modules_heading'] = 'Choose which activity types can use Autograder on the <a href="{$a->url}">Activity types</a> page.';
$string['setting:notifyfailures'] = 'Notify teachers of grading failures';
$string['setting:notifyfailures_desc'] = 'Sends everyone enrolled in the course who can configure Autograder on an activity a summary of the students it could not grade there, grouped by activity. Sent weekly by default; the schedule can be changed in Scheduled tasks. Each failure is reported once, starting from when this is switched on.';
$string['setting:notifystudent'] = 'Notify students';
$string['setting:notifystudent_desc'] = 'Sends the student Moodle\'s grading notification when Autograder grades an assignment or a forum, as if a teacher had chosen to notify while grading. Only assignments and forums are affected.';
$string['setting:retentiondays'] = 'Keep history for (days)';
$string['setting:retentiondays_desc'] = 'Finished decisions (graded, cancelled, failed…) and grading log entries older than this are deleted daily by a scheduled task. Pending decisions are never deleted. Enter 0 to keep everything.';
$string['setting:teacher_roles'] = 'Selected teaching roles';
$string['setting:teacher_roles_desc'] = 'Roles that count as teachers. Users whose roles are not selected here are never chosen. The selected roles must be allowed to grade the activities Autograder is used on.';
$string['setting:teacher_source_mode'] = 'Teaching roles';
$string['setting:teacher_source_mode_chosen'] = 'Selected roles only';
$string['setting:teacher_source_mode_desc'] = 'Automatic includes every role with moodle/grade:edit or an activity grading capability (such as mod/assign:grade), so new custom roles work without extra setup.';
$string['setting:teacher_source_mode_grading'] = 'Automatic: any role that can grade';
$string['setting:teachers_heading'] = 'Grades are recorded as one of the student\'s teachers: users with a teaching role assigned in the course itself, not in its category or site-wide. Site administrators and suspended users are never used. In courses with separate groups and a default grouping, teachers who share a group with the student are preferred.';
$string['setting:tiebreak'] = 'Teacher selection';
$string['setting:tiebreak_desc'] = 'When more than one teacher qualifies to grade a student, which one is chosen.';
$string['setting:tiebreak_last_course_access'] = 'Most recent course access';
$string['setting:tiebreak_lowest_userid'] = 'Lowest user ID';
$string['settings:generaltab'] = 'General';
$string['settings:modulestab'] = 'Activity types';
$string['settings:teacherstab'] = 'Teachers';
$string['task:cancel_module'] = 'Cancel the Autograder grading pending on an activity';
$string['task:catch_up_module'] = 'Catch up the students already waiting on an activity';
$string['task:grade_student'] = 'Grade one student';
$string['task:notify_failures'] = 'Notify teachers of students Autograder could not grade';
$string['task:purge_history'] = 'Purge finished Autograder decisions and grading log';
$string['task:recalculate_module'] = 'Recalculate an activity\'s Autograder due dates';
$string['task:reconcile_pending'] = 'Requeue Autograder decisions that lost their task';
