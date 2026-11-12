---
id: ANA
status: draft
origin: code
sourceOfTruth: code
---

# Analysis

## Analysis classes

![Analysis classes](./class-diagram.svg)

Stereotypes: `<<boundary>>` interface, `<<control>>` coordination, `<<entity>>` domain information.

| Class | Stereotype | Responsibility |
| --- | --- | --- |
| Activity form, Activity types page, Teacher preference page | boundary | The screens through which teachers and administrators work. |
| Configuration store, Type policy | control | Save the configuration of an activity and the enabled types. |
| Decision planner | control | Decides, from live data, whether a student did the activity and when the grade is due. |
| Decision store | control | Creates, moves, settles and cancels decisions and their tasks. |
| Event observer | control | Makes Autograder reconsider a student when the course changes. |
| Grading task | control | Checks again and gives the grade at the time it is due. |
| Grader picker | control | Chooses the teacher in whose name a grade is recorded. |
| Activity adapter | control | Reads submissions and dates, and writes the grade through each kind of activity. |
| Configuration, Decision, Grade log | entity | The stored information. |

## Use-case realizations

| ID | Use case | Collaboration |
| --- | --- | --- |
| UC-001 | [Configure Autograder in an activity](./use-cases/configureAutograder/README.md) | [collaboration](./use-cases/configureAutograder/collaboration.svg) |
| UC-002 | [Choose the activity types](./use-cases/chooseActivityTypes/README.md) | [collaboration](./use-cases/chooseActivityTypes/collaboration.svg) |
| UC-003 | [Grade a student](./use-cases/gradeStudent/README.md) | [collaboration](./use-cases/gradeStudent/collaboration.svg) |
| UC-004 | [Reconsider a decision when something changes](./use-cases/reconsiderDecision/README.md) | [collaboration](./use-cases/reconsiderDecision/collaboration.svg) |
