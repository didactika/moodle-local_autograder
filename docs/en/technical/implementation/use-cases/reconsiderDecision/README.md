---
id: UC-004
name: reconsiderDecision
title: Reconsider a decision when something changes
status: draft
origin: code
sourceOfTruth: code
---

# UC-004 Reconsider a decision when something changes — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/reconsiderDecision/README.md) | [Analysis](../../../analysis/use-cases/reconsiderDecision/README.md) | [Design](../../../design/use-cases/reconsiderDecision/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/reconsiderDecision/README.md#tests) |

Design: [README](../../../design/use-cases/reconsiderDecision/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `observer` | event observers | `classes/observer.php` | yes |
| `decision_repository` | service | `classes/local/decision/decision_repository.php` | yes |
| Observers registered | file | `db/events.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The observers reconsider the right student | main flow | `tests/observer_test.php` |
| Reconsidering creates, moves and cancels | main flow, alternative flows | `tests/local/reconsider_test.php` |
| The decision store | RN-006 | `tests/local/decision_repository_test.php` |
