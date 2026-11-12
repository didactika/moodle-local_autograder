---
id: REQ
status: draft
origin: requirements
sourceOfTruth: model
---

# Requirements

## Index

- [Actors](./actors.md)
- [Requirements catalogue](./requirements.md)

## Use cases

<!-- rup-flow:use-cases:start -->

| ID | Use case | Actor | Package | Requirement | Analysis | Design | Implementation | Status |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| UC-001 | [Configure Autograder in an activity](./use-cases/configureAutograder/README.md) | Teacher | Configuration | RF-001 | [card](../analysis/use-cases/configureAutograder/README.md) | [card](../design/use-cases/configureAutograder/README.md) | [card](../implementation/use-cases/configureAutograder/README.md) | draft |
| UC-002 | [Choose the activity types](./use-cases/chooseActivityTypes/README.md) | Administrator | Configuration | RF-002 | [card](../analysis/use-cases/chooseActivityTypes/README.md) | [card](../design/use-cases/chooseActivityTypes/README.md) | [card](../implementation/use-cases/chooseActivityTypes/README.md) | draft |
| UC-003 | [Grade a student](./use-cases/gradeStudent/README.md) | System | Grading | RF-003 | [card](../analysis/use-cases/gradeStudent/README.md) | [card](../design/use-cases/gradeStudent/README.md) | [card](../implementation/use-cases/gradeStudent/README.md) | draft |
| UC-004 | [Reconsider a decision when something changes](./use-cases/reconsiderDecision/README.md) | System | Grading | RF-004 | [card](../analysis/use-cases/reconsiderDecision/README.md) | [card](../design/use-cases/reconsiderDecision/README.md) | [card](../implementation/use-cases/reconsiderDecision/README.md) | draft |
| UC-005 | [Choose the result of a rubric or marking guide](./use-cases/chooseAdvancedGrade/README.md) | Teacher | Configuration | RF-006 | [card](../analysis/use-cases/chooseAdvancedGrade/README.md) | [card](../design/use-cases/chooseAdvancedGrade/README.md) | [card](../implementation/use-cases/chooseAdvancedGrade/README.md) | draft |
| UC-006 | [Opt out of having grades recorded in my name](./use-cases/optOutOfGrading/README.md) | Teacher | Configuration | RF-007 | [card](../analysis/use-cases/optOutOfGrading/README.md) | [card](../design/use-cases/optOutOfGrading/README.md) | [card](../implementation/use-cases/optOutOfGrading/README.md) | draft |
| UC-007 | [Set the policy of the site](./use-cases/setSitePolicy/README.md) | Administrator | Configuration | RF-008 | [card](../analysis/use-cases/setSitePolicy/README.md) | [card](../design/use-cases/setSitePolicy/README.md) | [card](../implementation/use-cases/setSitePolicy/README.md) | draft |
| UC-008 | [Tell the teachers which students could not be graded](./use-cases/notifyFailures/README.md) | System | Notification | RF-005 | [card](../analysis/use-cases/notifyFailures/README.md) | [card](../design/use-cases/notifyFailures/README.md) | [card](../implementation/use-cases/notifyFailures/README.md) | draft |
| UC-009 | [Queue again the grades that lost their task](./use-cases/requeueLostGrades/README.md) | System | Grading | RF-009 | [card](../analysis/use-cases/requeueLostGrades/README.md) | [card](../design/use-cases/requeueLostGrades/README.md) | [card](../implementation/use-cases/requeueLostGrades/README.md) | draft |
| UC-010 | [Delete the old history](./use-cases/purgeHistory/README.md) | System | Maintenance | RF-010 | [card](../analysis/use-cases/purgeHistory/README.md) | [card](../design/use-cases/purgeHistory/README.md) | [card](../implementation/use-cases/purgeHistory/README.md) | draft |
| UC-011 | [Carry the configuration in a course backup](./use-cases/carryConfigurationInBackup/README.md) | Teacher | Maintenance | RF-011 | [card](../analysis/use-cases/carryConfigurationInBackup/README.md) | [card](../design/use-cases/carryConfigurationInBackup/README.md) | [card](../implementation/use-cases/carryConfigurationInBackup/README.md) | draft |
| UC-012 | [Export or erase personal data](./use-cases/managePersonalData/README.md) | Administrator | Privacy | RF-012 | [card](../analysis/use-cases/managePersonalData/README.md) | [card](../design/use-cases/managePersonalData/README.md) | [card](../implementation/use-cases/managePersonalData/README.md) | draft |

### Use-case diagram

![Use cases](./use-case-overview.svg)

### By actor

#### Administrator

![Administrator](./use-cases-administrator.svg)

#### System

![System](./use-cases-system.svg)

#### Teacher

![Teacher](./use-cases-teacher.svg)

<!-- rup-flow:use-cases:end -->

The index and its diagrams are generated from the use-case cards by the `index` command. Edit the cards (front matter: `actor`, `package`, `includes`, `extends`), not this block.

## Conventions

- `specification.puml` is the behavioural source of truth of a use case: a statechart with the Actor/System conversation.
- `wireframes.puml` is the interface prototype, even when it holds a single screen.
- Each use-case folder holds `README.md`, `specification.puml` and `wireframes.puml`.
