---
title: Scheduled tasks
summary: The tasks that run by themselves, and how each grade is executed.
order: 42
---

| Task | Default schedule | Purpose |
|---|---|---|
| Requeue Autograder decisions that lost their task | Every 30 minutes | Re-queues any due grade whose task has been lost |
| Purge finished Autograder decisions and grading log | Daily at 03:30 | Deletes the history older than **Keep history for** |
| Notify teachers of students Autograder could not grade | Mondays at about 07:00 | Sends the failure summary, if it is enabled |

Each grade runs as its own ad hoc task, scheduled for the time it is due. Because grading is done by scheduled and ad hoc tasks, [cron](https://docs.moodle.org/en/Cron) must run regularly for grades to be assigned on time.

Besides these, the plugin queues ad hoc tasks when something changes: it calculates the decisions of an activity when Autograder is enabled in it, recalculates them when the activity changes and cancels them when Autograder is turned off or the activity is deleted.
