---
id: UC-008
name: notifyFailures
title: Tell the teachers which students could not be graded
status: draft
origin: code
sourceOfTruth: code
---

# UC-008 Tell the teachers which students could not be graded — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/notifyFailures/README.md) | [Analysis](../../../analysis/use-cases/notifyFailures/README.md) | [Design](../../../design/use-cases/notifyFailures/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/notifyFailures/README.md#tests) |

Design: [README](../../../design/use-cases/notifyFailures/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `notify_failures` | task | `classes/task/notify_failures.php` | yes |
| `db/tasks.php` | file | `db/tasks.php` | yes |
| `db/messages.php` | file | `db/messages.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The failures are grouped, sent once and the point of the run is remembered | main flow, RN-012 | `tests/task/notify_failures_test.php` |
