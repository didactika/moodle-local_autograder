---
title: Troubleshooting
summary: What to check when something does not work as expected.
order: 80
---

The [Autograder report](page:guides/review-decisions) shows the status of each student and, for a failed grading, the reason. That is usually the quickest way to find out what happened.

| Problem | Possible cause |
|---|---|
| A student who completed the activity is not graded | The deadline plus the waiting period has not passed yet, not all the completion conditions are met, or cron is not running |
| The Autograder section does not appear in the settings of an activity | The activity type is not enabled on the **Activity types** page, the activity is not graded, or you do not have `local/autograder:configure` |
| "Autograder was left switched off" appears after saving | The selection of the rubric or the marking guide has not been made, or, for other activity types, there is no completion condition that the student can meet. The message states which one |
| Grading fails because no grader is found | None of the teachers of the student can grade the activity or see the group of the student, and no fallback grader is set |
| A quiz still shows the grade of Autograder after marking | Another question of the quiz is still waiting to be marked by hand. The grade of the quiz replaces the one from Autograder once every question is marked |
