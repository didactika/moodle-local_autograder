<div align="center">

# Autograder for Moodle

*Automatically grade students whose work a teacher hasn't graded yet*

[![Release](https://img.shields.io/github/v/release/didactika/moodle-local_autograder?style=flat-square)](https://github.com/didactika/moodle-local_autograder/releases)
[![Moodle](https://img.shields.io/badge/Moodle-4.5+-f98012?style=flat-square&logo=moodle&logoColor=white)](https://moodle.org)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777bb4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![License](https://img.shields.io/badge/License-GPL_v3-blue?style=flat-square)](LICENSE)

[Overview](#overview) • [Installation](#installation) • [Usage](#usage) • [Configuration](#configuration) • [Troubleshooting](#troubleshooting)

</div>

Autograder (`local_autograder`) is a Moodle local plugin that grades students automatically. Once a student has completed an activity and its deadline has passed, Autograder waits for a configured period and then assigns the grade the teacher configured, unless a teacher has already graded the student. The grade is recorded in the name of one of the student's own teachers and goes through the activity's normal grading process, including rubrics and marking guides.

> [!NOTE]
> Autograder never changes a grade given by a teacher. A manual grade always takes priority, and a student who has already been graded by hand is never graded again.

## Overview

Some activities are assessed on whether students take part rather than on the quality of their work: a forum contribution, a reflection, a practice quiz. Grading each one by hand takes time, and students who did the work may wait weeks for a grade. Autograder assigns these grades on time.

Assignments, forums and quizzes are supported out of the box. Administrators can enable any other gradable activity type.

### When a student is graded

Autograder only grades students who have **done** the activity:

- **If the activity tracks completion**, the student must meet the completion conditions set by the teacher. For example, a forum that requires three replies is not done after one. Conditions that depend on a grade ("Receive a grade", "Receive a passing grade") are ignored, because Autograder is the one that would provide that grade.
- **If the activity does not track completion**, the student must hand something in: submit an assignment, post in a forum, or finish a quiz attempt.

Autograder then schedules the grade as follows:

| If the activity… | The student is graded at… |
|---|---|
| Has a close date | The close date (the cut-off date, or the due date if there is none), plus the waiting period |
| Has a user override for the student | The override date, plus the waiting period |
| Has a group override for the student's group | The latest of the group override dates, plus the waiting period |
| Has no close date, or an override removes it | The time the student finished, plus the waiting period |

If the student finishes after the scheduled time, they are graded as soon as they finish. If an override is added or changed later, the grade is rescheduled accordingly.

### Who the grade is attributed to

Autograder never records a grade as itself or as a site administrator. It chooses one of the student's teachers, and the grade appears under that teacher's name in the activity, the gradebook and the grade history:

1. **Course teachers:** users with a teaching role assigned in the course and an active enrolment in it.
2. **Permission to grade:** only teachers who can grade the activity and can see the student in it. In an activity with separate groups, a teacher without access to all groups can only grade students in their own groups.
3. **Group match:** the teacher who shares the most groups with the student is preferred. If no teacher shares a group, a teacher with no group is chosen, and failing that, any remaining teacher. In a course with separate groups and a default grouping, only the groups in that grouping are considered.
4. **Tie-break:** if several teachers are equally suitable, the one with the lowest user ID or the one who most recently accessed the course, depending on the site setting.

If no teacher qualifies, the grade is attributed to the site's **fallback grader**. If no fallback grader is set, the student is not graded and the failure is logged.

### When Autograder does not grade

- **A teacher grades the student first.** The scheduled grade is cancelled permanently.
- **The student undoes their work,** for example by unticking completion, withdrawing a submission or leaving the course. The scheduled grade is cancelled, and it is scheduled again if the student completes the activity later.
- **The activity grades itself.** A quiz that is marked automatically keeps its own grade. Autograder only grades a quiz while a question, such as an essay, is waiting to be marked by hand. As soon as the teacher marks it, the quiz's own grade replaces Autograder's.

### Features

- **Per-activity setup:** enable Autograder, and set its grade and waiting period, in each activity's settings.
- **All grade types:** points, scales, rubrics and marking guides.
- **Native grading:** grades are saved through the activity's own grading process, so submission status, feedback and the gradebook update as they would for a manual grade.
- **Deadline handling:** close dates, cut-off dates, and user and group overrides.
- **Teacher attribution:** each grade is recorded in the name of the student's closest teacher who is allowed to grade them.
- **Failure summary:** an optional weekly message to teachers listing students who could not be graded.
- **Teacher opt-out:** an optional preference that lets teachers keep grades from being recorded in their name.

## Installation

**Requirements:** Moodle 4.5 or later and PHP 8.1 or later.

> [!IMPORTANT]
> Grading is done by scheduled and ad hoc tasks, so [cron](https://docs.moodle.org/en/Cron) must run regularly for grades to be assigned on time.

**From a release:** download the latest release ZIP file, go to **Site administration → Plugins → Install plugins**, upload the file and follow the prompts.

**From Git:**

```bash
cd /path/to/moodle/local
git clone https://github.com/didactika/moodle-local_autograder.git autograder
php /path/to/moodle/admin/cli/upgrade.php
```

## Usage

### 1. Choose the activity types (administrators)

Go to **Site administration → Plugins → Local plugins → Autograder** and select the **Activity types** link on the *General* tab.

Assignments, forums and quizzes are enabled by default. Disabling a type hides the Autograder settings on activities of that type and cancels their scheduled grades. Each activity keeps its own configuration, so re-enabling the type restores it.

### 2. Enable Autograder on an activity (teachers)

In the activity's settings, expand the **Autograder** section and:

1. Select **Enable Autograder**.
2. Enter the **grade to assign**: a number or a scale item, depending on how the activity is graded.
3. Set the **time to wait** after the due date before grading, in days, hours and minutes.
4. Save the settings.

Students who have already completed the activity are processed on the next cron run.

> [!TIP]
> **Rubrics and marking guides:** Autograder needs to know which level to select for each criterion. After saving, follow the link in the Autograder section and fill in the rubric or marking guide as you would for a student. Autograder remains disabled until you complete this step.

**Other activity types:** for activity types other than assignments, forums and quizzes, Autograder relies on activity completion to know when a student has finished. The activity needs a completion condition the student can meet on their own, such as marking it as done, viewing it, or one of the activity's own conditions, and must not include a grade condition. Otherwise, Autograder is saved as disabled and the form explains why.

### 3. Teacher opt-out (optional)

If the site allows it, teachers can open **Autograder preferences** from the **Preferences** page of their profile and choose never to have grades recorded in their name.

## Configuration

Settings are located at **Site administration → Plugins → Local plugins → Autograder**.

### General

| Setting | Default | Description |
|---|---|---|
| Notify students | Off | Sends students Moodle's grading notification when an assignment or forum is graded by Autograder, as a teacher can when grading manually |
| Notify teachers of grading failures | Off | Sends a weekly summary to the teachers who configure Autograder, listing the students who could not be graded |
| Keep history for (days) | 120 | Completed decisions and log entries older than this are deleted daily. Enter `0` to keep everything |

### Teachers

| Setting | Default | Description |
|---|---|---|
| Teaching roles | Automatic | *Automatic* includes every role that can grade. *Selected roles only* uses the roles chosen in the next setting |
| Selected teaching roles | None | Only shown when *Selected roles only* is chosen |
| Teacher selection | Lowest user ID | How to choose between equally suitable teachers: *Lowest user ID* or *Most recent course access* |
| Fallback grader | None | The user grades are attributed to when no teacher qualifies |
| Allow teachers to opt out | Off | Lets teachers choose not to have grades recorded in their name. While disabled, existing opt-outs are ignored |

> [!WARNING]
> Unlike the teachers, the fallback grader is not checked against each activity. Choose someone who is allowed to grade wherever Autograder is used.

### Capabilities

| Capability | Context | Default roles | Allows the user to |
|---|---|---|---|
| `local/autograder:configure` | Activity | Editing teacher, Manager | Enable, disable and configure Autograder on an activity |
| `local/autograder:manage` | System | Manager | Choose which activity types can use Autograder |

Autograder does not add a capability to decide who a grade can be attributed to. It uses the activity's own grading capability: `mod/assign:grade`, `mod/forum:grade`, `mod/quiz:grade`, or `moodle/grade:edit` for other activity types.

### Scheduled tasks

| Task | Default schedule | Purpose |
|---|---|---|
| Requeue Autograder decisions that lost their task | Every 30 minutes | Re-queues any due grade whose task has been lost |
| Purge finished Autograder decisions and grading log | Daily at 03:30 | Deletes history older than *Keep history for* |
| Notify teachers of students Autograder could not grade | Mondays at about 07:00 | Sends the failure summary, if enabled |

Each grade runs as its own ad hoc task, scheduled for the time it is due.

### Privacy and backup

- **Privacy:** Autograder implements the Privacy API. For each student it stores the scheduled or completed grading decision and a log of grading attempts, including the teacher each grade was attributed to. It also stores each teacher's opt-out preference. All of this data can be exported and deleted.
- **Backup and restore:** an activity's Autograder settings, including rubric and marking guide selections, are included in its backup. Scheduled grades are not backed up; they are recalculated in the restored course.

## Troubleshooting

| Problem | Possible cause |
|---|---|
| A student who completed the activity is not graded | The deadline plus the waiting period has not passed yet, not all completion conditions are met, or cron is not running |
| The Autograder section does not appear in an activity's settings | The activity type is not enabled on the *Activity types* page, the activity is not graded, or you do not have `local/autograder:configure` |
| "Autograder was left switched off" appears after saving | The rubric or marking guide selection has not been made, or, for other activity types, there is no completion condition the student can meet. The message states which |
| Grading fails because no grader is found | None of the student's teachers can grade the activity or see the student's group, and no fallback grader is set |
| A quiz still shows Autograder's grade after marking | Another question in the quiz is still waiting to be marked by hand. The quiz's own grade replaces Autograder's once every question is marked |

## Getting help

To report a bug or request a feature, please open an [issue](https://github.com/didactika/moodle-local_autograder/issues). Include your Moodle and PHP versions, the activity type and its settings, and a description of the expected and actual behaviour.
