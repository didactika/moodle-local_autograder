---
id: UC-009
name: requeueLostGrades
title: Queue again the grades that lost their task
status: draft
origin: code
sourceOfTruth: code
---

# UC-009 Queue again the grades that lost their task — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/requeueLostGrades/README.md) | [Analysis](../../../analysis/use-cases/requeueLostGrades/README.md) | [Design](../../../design/use-cases/requeueLostGrades/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/requeueLostGrades/README.md#tests) |

Design: [README](../../../design/use-cases/requeueLostGrades/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `reconcile_pending` | task | `classes/task/reconcile_pending.php` | yes |
| `db/tasks.php` | file | `db/tasks.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The orphaned decisions are found and queued again | main flow, RN-013 | `tests/local/decision_repository_test.php` |
