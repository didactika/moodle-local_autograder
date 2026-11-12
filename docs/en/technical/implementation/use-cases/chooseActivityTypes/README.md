---
id: UC-002
name: chooseActivityTypes
title: Choose the activity types
status: draft
origin: code
sourceOfTruth: code
---

# UC-002 Choose the activity types — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/chooseActivityTypes/README.md) | [Analysis](../../../analysis/use-cases/chooseActivityTypes/README.md) | [Design](../../../design/use-cases/chooseActivityTypes/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/chooseActivityTypes/README.md#tests) |

Design: [README](../../../design/use-cases/chooseActivityTypes/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `set_module_type` | web service | `classes/external/set_module_type.php` | yes |
| Activity types page | React component | `js/esm/src/Modules.tsx` | yes |
| `modules_page` | renderable | `classes/output/modules_page.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Switching a type changes the setting | main flow | `tests/external/set_module_type_test.php` |
| The page of the activity types | main flow | `tests/behat/pages.feature` |
