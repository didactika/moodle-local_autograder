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
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Define this activity\'s rubric or marking guide first, then come back to say which levels autograder should mark.';
$string['advanced:edit_definition'] = 'To change the rubric or marking guide itself, <a href="{$a}">edit its definition</a>.';
$string['advanced:heading'] = 'Levels autograder marks';
$string['advanced:intro'] = 'Fill this in exactly as you would when grading a student of <strong>{$a}</strong> by hand. Autograder marks what you choose here, and Moodle works the grade out from it the same way it always does.';
$string['advanced:marks'] = 'What autograder marks';
$string['advanced:not_advanced'] = 'This activity is not graded by a rubric or a marking guide.';
$string['advanced:save'] = 'Save what autograder marks';
$string['advanced:saved'] = 'Saved what autograder will mark.';
$string['autograder:configure'] = 'Turn autograder on or off for an activity';
$string['autograder:manage'] = 'Manage autograder site-wide settings';
$string['error:advancedgradingstale'] = 'The rubric or marking guide has changed since autograder was told what to mark on it. Open the activity\'s autograder settings and choose the levels again.';
$string['error:gradewritefailed'] = 'Moodle refused the grade autograder tried to post.';
$string['error:nogradeitem'] = 'This activity has no grade item to write to.';
$string['event:config_created'] = 'Autograder configuration created';
$string['event:config_deleted'] = 'Autograder configuration deleted';
$string['event:config_updated'] = 'Autograder configuration updated';
$string['event:decision_cancelled'] = 'Autograder decision cancelled';
$string['event:grading_failed'] = 'Autograder grading failed';
$string['event:student_graded'] = 'Student graded by autograder';
$string['form:advanced_define_first'] = 'This activity is graded by a rubric or marking guide, but none is defined yet. <a href="{$a}">Define it first</a>, then choose what autograder marks.';
$string['form:advanced_set'] = 'Autograder knows what to mark on this rubric or guide. <a href="{$a}">Change it</a>.';
$string['form:advanced_undefined'] = 'This activity is graded by a rubric or marking guide that autograder cannot read.';
$string['form:advanced_unset'] = 'Choose <a href="{$a}">what autograder marks</a> on this rubric or guide — until then it has nothing to grade with.';
$string['form:days_to_complete'] = 'Days';
$string['form:enabled'] = 'Enable autograder';
$string['form:enabled_help'] = 'When enabled, a student who completes this activity is graded automatically, the configured time after it was due, with the grade set below — unless someone grades them by hand first.';
$string['form:error_advanced_unset'] = 'Autograder has not been told what to mark on this activity\'s rubric or marking guide. <a href="{$a}">Choose that first</a>, then switch autograder on.';
$string['form:error_grade_above_max'] = 'This activity is graded out of {$a}, so the grade cannot be higher than that.';
$string['form:error_grade_negative'] = 'The grade cannot be negative.';
$string['form:error_grade_required'] = 'Enter the grade autograder should give.';
$string['form:error_hours_range'] = 'Enter 0 to 23 hours. Use the days field for anything longer.';
$string['form:error_minutes_range'] = 'Enter 0 to 59 minutes. Use the hours field for anything longer.';
$string['form:error_negative_time'] = 'Cannot be negative.';
$string['form:error_not_graded'] = 'Autograder needs this activity to be graded. Choose a grade type other than "None".';
$string['form:error_numeric'] = 'Must be a number.';
$string['form:error_scale_mismatch'] = 'That item does not belong to the scale this activity now uses. Choose one from the scale you just selected.';
$string['form:error_scale_unset'] = 'Choose which scale item autograder should assign.';
$string['form:error_whole_number'] = 'Enter a whole number.';
$string['form:grade'] = 'Grade to assign';
$string['form:grade_range'] = 'This activity is graded out of {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Hours';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:time_to_complete'] = 'Time to wait before grading';
$string['form:time_to_complete_help'] = 'How long to wait, after the due date, before grading.';
$string['modules:intro'] = 'Only activity types enabled here can have autograder configured on one of their instances.';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Autograder configuration';
$string['preference:notoffered'] = 'This site does not let teachers opt out of being graded on behalf of.';
$string['preference:optout'] = 'Do not let autograder grade in my name';
$string['preference:optout_help'] = 'When checked, autograder will never choose you as the teacher who grades a student, even where you would otherwise qualify.';
$string['preference:saved'] = 'Preference saved.';
$string['privacy:metadata'] = 'Autograder keeps, per student, whether and when they are due to be graded automatically, and a log of every grading attempt.';
$string['privacy:metadata:config'] = 'What a teacher configured autograder to do on an activity.';
$string['privacy:metadata:config:cmid'] = 'The activity the configuration belongs to.';
$string['privacy:metadata:config:timemodified'] = 'When the configuration was last saved.';
$string['privacy:metadata:config:usermodified'] = 'The user who last saved the configuration.';
$string['privacy:metadata:decision'] = 'One student\'s pending or settled autograder decision on one activity.';
$string['privacy:metadata:decision:baselineduedate'] = 'The date the delay is counted from.';
$string['privacy:metadata:decision:duedatereason'] = 'Why that date was the one used: completion, submission, close date or an exception.';
$string['privacy:metadata:decision:failurereason'] = 'Why the decision was called off or could not be carried out.';
$string['privacy:metadata:decision:gradedvalue'] = 'The grade autograder posted.';
$string['privacy:metadata:decision:graderid'] = 'The teacher the grade was posted as.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'When the grade is, or was, due to be posted.';
$string['privacy:metadata:decision:status'] = 'Whether the decision is waiting, graded, taken over by hand, cancelled or failed.';
$string['privacy:metadata:decision:timemodified'] = 'When the decision last changed.';
$string['privacy:metadata:decision:userid'] = 'The student the decision is about.';
$string['privacy:metadata:gradelog'] = 'A record of what autograder did, and did not do, and why.';
$string['privacy:metadata:gradelog:graderid'] = 'The teacher the grade was posted as.';
$string['privacy:metadata:gradelog:gradevalue'] = 'The grade posted, where one was.';
$string['privacy:metadata:gradelog:message'] = 'What happened, in words.';
$string['privacy:metadata:gradelog:outcome'] = 'Whether the student was graded, skipped, cancelled or failed.';
$string['privacy:metadata:gradelog:timecreated'] = 'When it happened.';
$string['privacy:metadata:gradelog:userid'] = 'The student the entry is about.';
$string['privacy:metadata:preference:optout'] = 'Whether this user has asked not to be chosen as the teacher autograder grades as.';
$string['privacy:path:config'] = 'Autograder configuration';
$string['privacy:path:decision'] = 'Autograder decisions';
$string['privacy:path:gradelog'] = 'Autograder history';
$string['setting:allowoptout'] = 'Let teachers opt out';
$string['setting:allowoptout_desc'] = 'Offers every user a preference asking autograder never to grade in their name. Off by default: taking teachers out of the rota changes who is graded and when, and on a site with few eligible teachers it can leave an activity with nobody to grade as. While it is off the preference is neither shown nor honoured.';
$string['setting:coordinator_roles'] = 'Coordinator roles';
$string['setting:coordinator_roles_desc'] = 'The same question for a programme course: which roles make somebody one of its coordinators. Ignored unless the two categories below name different categories.';
$string['setting:fallback_grader'] = 'Fallback grader';
$string['setting:fallback_grader_desc'] = 'Used only when no teacher of the course itself can grade a student. Only users Moodle says may grade are offered.';
$string['setting:fallback_grader_ineligible'] = 'That user does not hold a grading capability and cannot be set as the fallback grader.';
$string['setting:fallback_grader_none'] = 'None';
$string['setting:fallback_grader_placeholder'] = 'Search for a user…';
$string['setting:modules_disable'] = 'Disable autograder for {$a}';
$string['setting:modules_enable'] = 'Enable autograder for {$a}';
$string['setting:modules_enabled_column'] = 'Enabled';
$string['setting:modules_heading'] = 'Choose which activity types can have autograder configured on the <a href="{$a->url}">autogradable activities page</a>.';
$string['setting:notifystudent'] = 'Notify the student';
$string['setting:notifystudent_desc'] = 'Uses the assignment module\'s own "notify students" option when autograder posts a grade — the same choice a teacher has when grading by hand. Only assignments support this natively; other activity types are unaffected. Off by default.';
$string['setting:program_course_category'] = 'Programme course category';
$string['setting:program_course_category_desc'] = 'Courses in this category are programmes, and take their teachers from the coordinator roles instead. Leave it equal to the category above if the site does not make this distinction.';
$string['setting:retentiondays'] = 'Retention (days)';
$string['setting:retentiondays_desc'] = 'How long a finished decision and its grading log are kept before being purged.';
$string['setting:subject_course_category'] = 'Subject course category';
$string['setting:subject_course_category_desc'] = 'Courses in this category are subjects and take their teachers from the teacher roles. A course in neither category is read as a subject.';
$string['setting:teacher_roles'] = 'Teacher roles';
$string['setting:teacher_roles_desc'] = 'Which roles make somebody a teacher of a course. A grade is posted in the name of one of them, so a role missing here means its holders are never chosen — which is what leaves a course reporting that nobody can grade it. Only roles assigned in the course itself count; one held over the whole category does not.';
$string['setting:teachers_heading'] = 'Who autograder treats as a teacher, and so whose name it posts a grade in. Where a course separates its groups and names a default grouping, the list is narrowed to the teachers sharing a group with the student; if none do, all of the course\'s teachers stand. No capability is checked — holding one of these roles is what counts.';
$string['setting:tiebreak'] = 'Tie-break rule';
$string['setting:tiebreak_desc'] = 'When more than one teacher qualifies to grade a student, which one is chosen.';
$string['setting:tiebreak_last_course_access'] = 'The one who accessed the course most recently';
$string['setting:tiebreak_lowest_userid'] = 'The one with the lowest user ID';
$string['settings:generaltab'] = 'General';
$string['settings:modulestab'] = 'Autogradable activities';
$string['settings:retentiontab'] = 'Retention';
$string['settings:teacherstab'] = 'Teachers';
$string['task:cancel_module'] = 'Cancel the autograder grading pending on an activity';
$string['task:catch_up_module'] = 'Catch up the students already waiting on an activity';
$string['task:grade_student'] = 'Grade one student';
$string['task:purge_history'] = 'Purge finished autograder decisions and grading log';
$string['task:recalculate_module'] = 'Recalculate an activity\'s autograder due dates';
$string['task:reconcile_pending'] = 'Requeue autograder decisions that lost their task';
