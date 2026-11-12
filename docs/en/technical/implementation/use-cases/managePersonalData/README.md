---
id: UC-012
name: managePersonalData
title: Export or erase personal data
status: draft
origin: code
sourceOfTruth: code
---

# UC-012 Export or erase personal data — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/managePersonalData/README.md) | [Analysis](../../../analysis/use-cases/managePersonalData/README.md) | [Design](../../../design/use-cases/managePersonalData/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/managePersonalData/README.md#tests) |

Design: [README](../../../design/use-cases/managePersonalData/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `provider` | provider | `classes/privacy/provider.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The data is listed, exported and erased | main flow, RN-015 | `tests/privacy/provider_test.php` |
