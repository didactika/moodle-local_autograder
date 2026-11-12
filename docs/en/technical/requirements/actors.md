---
id: ACT
status: draft
origin: code
sourceOfTruth: code
---

# Actors

| Actor | Type | Goal | Related use cases |
| --- | --- | --- | --- |
| Teacher | Primary | Have the students who took part graded on time, without grading each one. Holds `local/autograder:configure` in the activity. | [UC-001](./use-cases/configureAutograder/README.md), [UC-005](./use-cases/chooseAdvancedGrade/README.md), [UC-006](./use-cases/optOutOfGrading/README.md), [UC-011](./use-cases/carryConfigurationInBackup/README.md) |
| Administrator | Primary | Decide which kinds of activity may use Autograder and the policy of the site. Holds `local/autograder:manage`. | [UC-002](./use-cases/chooseActivityTypes/README.md), [UC-007](./use-cases/setSitePolicy/README.md), [UC-012](./use-cases/managePersonalData/README.md) |
| System | Secondary | Cron and the observers of Moodle that plan and execute the grades. | [UC-003](./use-cases/gradeStudent/README.md), [UC-004](./use-cases/reconsiderDecision/README.md), [UC-008](./use-cases/notifyFailures/README.md), [UC-009](./use-cases/requeueLostGrades/README.md), [UC-010](./use-cases/purgeHistory/README.md) |
| Moodle | Secondary | Provides completion, the gradebook, grading methods, events, tasks and capabilities that the plugin relies on. | all |
