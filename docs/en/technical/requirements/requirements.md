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
| RF-005 | Teachers can be told weekly which students could not be graded, and can opt out of having grades recorded in their name. | Could | `classes/task/notify_failures.php`, `classes/form/optout_form.php` | UC-003 |

## Non-functional

| ID | Quality attribute | Requirement | Measure | Priority |
| --- | --- | --- | --- | --- |
| RNF-001 | Integrity | A manual grade is never overwritten. | `existing_person_grade` is checked before writing | Must |
| RNF-002 | Correctness | A missed event costs promptness, never correctness: the grading task reads every condition again. | `grade_student::execute` | Must |
| RNF-003 | Privacy | The plugin stores each decision and the grader of each grade, and implements the Privacy API. | `classes/privacy/provider.php` | Must |
| RNF-004 | Recoverability | A grade whose task was lost is queued again. | `reconcile_pending` every 30 minutes | Should |

## Business rules

Rules live in the [domain model dictionary](../domain/README.md#business-rules). Rules that change behaviour are repeated here with their ID.
