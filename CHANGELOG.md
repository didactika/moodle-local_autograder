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

## [1.0.0] - 2026-09-29

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
