---
id: UC-010
name: purgeHistory
title: Delete the old history
status: draft
origin: code
sourceOfTruth: code
---

# UC-010 Delete the old history — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/purgeHistory/README.md) | [Analysis](../../../analysis/use-cases/purgeHistory/README.md) | [Design](../../../design/use-cases/purgeHistory/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/purgeHistory/README.md#tests) |

Design: [README](../../../design/use-cases/purgeHistory/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `purge_history` | task | `classes/task/purge_history.php` | yes |
| `db/tasks.php` | file | `db/tasks.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The settled decisions older than the date are deleted and the pending ones are not | main flow, RN-009 | `tests/local/decision_repository_test.php` |
