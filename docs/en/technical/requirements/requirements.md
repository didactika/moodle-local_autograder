---
id: RQ
status: draft
origin: code
sourceOfTruth: code
---

# Requirements catalogue

## Functional

| ID | Requirement | Priority | Origin | Use cases |
| --- | --- | --- | --- | --- |
| RF-001 | A teacher can turn Autograder on in an activity and choose the grade to assign and the time to wait, with points, scale, rubric or marking guide. | Must | `classes/form/autograder_section.php` | UC-001 |
| RF-002 | An administrator can choose which activity types may use Autograder. | Must | `classes/external/set_module_type.php` | UC-002 |
| RF-003 | A student who did an activity is graded when the deadline plus the waiting time has passed, in the name of one of their teachers, unless a teacher graded first. | Must | `classes/task/grade_student.php` | UC-003 |
| RF-004 | The decisions follow what happens in the course: submissions, completion, overrides, enrolments and roles. | Must | `classes/observer.php` | UC-004 |
| RF-005 | Teachers are told weekly which students could not be graded. | Could | `classes/task/notify_failures.php` | UC-008 |
| RF-006 | A teacher can choose the result that Autograder gives with a rubric or a marking guide. | Must | `advanced.php` | UC-005 |
| RF-007 | A user can opt out of having grades recorded in their name, when the site offers it. | Could | `optout.php` | UC-006 |
| RF-008 | An administrator can set the policy of the site: the notices, the retention, the roles that count as teachers, how to choose among them and the fallback grader. | Must | `settings.php` | UC-007 |
| RF-009 | A decision that is due and has lost its task is queued again. | Should | `classes/task/reconcile_pending.php` | UC-009 |
| RF-010 | The finished decisions and the log older than the retention of the site are deleted. | Should | `classes/task/purge_history.php` | UC-010 |
| RF-011 | The configuration of an activity travels in a course backup, and the students are decided again after a restore. | Should | `backup/moodle2/` | UC-011 |
| RF-012 | The data of a user can be exported and erased through the privacy API. | Must | `classes/privacy/provider.php` | UC-012 |

## Non-functional

| ID | Quality attribute | Requirement | Measure | Priority |
| --- | --- | --- | --- | --- |
| RNF-001 | Integrity | A manual grade is never overwritten. | `existing_person_grade` is checked before writing | Must |
| RNF-002 | Correctness | A missed event costs promptness, never correctness: the grading task reads every condition again. | `grade_student::execute` | Must |
| RNF-003 | Privacy | The plugin stores each decision and the grader of each grade, and implements the Privacy API. | `classes/privacy/provider.php` | Must |
| RNF-004 | Recoverability | A grade whose task was lost is queued again. | `reconcile_pending` every 30 minutes | Should |

## Business rules

Rules live in the [domain model dictionary](../domain/README.md#business-rules). Rules that change behaviour are repeated here with their ID.
