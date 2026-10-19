# Changelog

All notable changes to this plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!--
.github/workflows/release.yml reads this file: when $plugin->release changes
in version.php on `main` OR on any MOODLE_XXX_STABLE branch, it looks for a
"## [<that release>]" heading below and uses everything under it, verbatim,
as the GitHub Release body. If no such heading exists yet, the release still
happens but with a generic one-line release note instead.

Keep an "## [Unreleased]" section above the latest release for changes that
have not shipped yet; rename it to "## [x.y.z]" (matching $plugin->release)
when you cut that release, and start a fresh "## [Unreleased]" above it. Each
branch keeps its own CHANGELOG.md history from the point it was cut, same as
its own $plugin->release line -- no need to reconcile entries across branches.
-->

## [Unreleased]

## [1.1.0-m503] - 2026-10-19

First release of the Moodle 5.3 line, on `main`. The Moodle 4.0 to 5.2 line is on `MOODLE_400_STABLE` (1.1.0-m400).

### Changed

- The page of the activity types is a React component written in TypeScript, mounted by Moodle's own `core/react_autoinit`: one row and one switch for each type, a switch that saves the moment it is flipped through the new function `local_autograder_set_module_type`, says so in a toast, and goes back where it was if the server refuses.
- The field of the fallback grader is a React search box that asks the server for users as the administrator types, instead of the form autocomplete of Moodle with its own AMD module; what is chosen is written in the field that the settings page saves.
- The styles are written as nested SCSS and compiled into `styles.css`, with every colour taken from the theme so that a theme can restyle them, and the pipeline checks that the stylesheet is what the sources produce.
- The AMD modules and the Mustache template are gone: nothing of the plugin is drawn by them on Moodle 5.3.

### Added

- Behat scenarios for the page of the activity types and for the fallback grader.

## [1.0.4-m405] - 2026-10-13

### Changed

- The release number now indicates the lowest Moodle version the plugin supports: -m405

### Fixed

- Saving the settings of an activity no longer switches Autograder off, empties its grade or cancels its pending decisions when the person saving may not configure Autograder (`local/autograder:configure`). The form does not show them the section, so nothing they submit says anything about it, and what a teacher configured is left as it was.
- Deleting a whole course now deletes its Autograder decisions, its grading log and its configuration. Core removes the activities of a course without raising an event for each one, so these rows, which hold the ids of students and teachers, used to stay behind with no module context for a privacy request to find them by.

## [1.0.2] - 2026-10-06

### Changed

- Compatibility with Moodle 5.3 (now 4.5 to 5.3).
- Tests duplicate and delete activities through the course format actions on Moodle 5.2+, instead of `duplicate_module()` and `course_delete_module()`, which 5.2 deprecated (MDL-86858, MDL-86856).
- CI runs Moodle 5.3 against PostgreSQL 17 and MariaDB 11.4, the minimum versions it requires.
- CI tests the two ends of the supported range plus any version listed in MOODLE_EXTRA_VERSIONS, so raising the ceiling no longer quietly stops testing the version below it. It currently tests 4.5, 5.2 and 5.3.

### Fixed

- A student a teacher had already graded before Autograder was switched on is settled straight away, instead of reading as pending until the day it would have graded and standing down then.
- A grade taken away again puts the student back in the queue, so long as the moment to grade them has not passed.
- The grade to assign is shown again as the teacher typed it, instead of padded with the decimals its database column stores ("65", not "65.00000").

## [1.0.1] - 2026-09-30

First public release.

### Added

- Automatic grading of students who have done an activity and whose deadline, plus a configurable waiting period, has passed, unless a teacher graded them first. A teacher's grade always takes priority and is never overwritten.
- Support for assignments, forums and quizzes, and for other gradable activity types through activity completion.
- Grading with points, scales, rubrics and marking guides, through each activity's own grading process.
- Completion-aware detection of when a student has done an activity, ignoring completion conditions that only a grade can meet.
- Due dates from the activity's close or cut-off date and from user and group overrides, rescheduled when an override changes.
- Quiz support for questions marked by hand: Autograder grades a quiz while an essay is waiting to be marked, and the quiz's own grade replaces Autograder's once the teacher marks it.
- Grades recorded in the name of the student's closest teacher by group, among those actively enrolled who can grade the activity and see the student's group, with a configurable tie-break and an optional site fallback grader.
- Grading of students who had already done the activity when Autograder is enabled, and of students who are reactivated or enrolled again.
- Site page to choose which activity types can use Autograder.
- Optional student grading notifications, weekly failure summary for teachers, and teacher opt-out preference.
- History retention, Privacy API support, and backup and restore of each activity's Autograder settings.
- Compatibility with Moodle 4.5 and 5.2.
