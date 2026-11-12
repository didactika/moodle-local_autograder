---
title: Privacy and backup
summary: What the plugin stores about people and how backup and restore treat it.
order: 43
---

## Privacy

Autograder implements the Privacy API. For each student it stores the scheduled or completed grading decision and a log of the grading attempts, including the teacher each grade was attributed to. It also stores the opt-out preference of each teacher. All of this data can be exported and deleted.

## Backup and restore

The Autograder settings of an activity, including the selections of the rubric and of the marking guide, are included in the backup of the activity. Scheduled grades are not backed up: they are calculated again in the restored course.
