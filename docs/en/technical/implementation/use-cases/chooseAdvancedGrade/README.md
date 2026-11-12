---
id: UC-005
name: chooseAdvancedGrade
title: Choose the result of a rubric or marking guide
status: draft
origin: code
sourceOfTruth: code
---

# UC-005 Choose the result of a rubric or marking guide — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/chooseAdvancedGrade/README.md) | [Analysis](../../../analysis/use-cases/chooseAdvancedGrade/README.md) | [Design](../../../design/use-cases/chooseAdvancedGrade/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/chooseAdvancedGrade/README.md#tests) |

Design: [README](../../../design/use-cases/chooseAdvancedGrade/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `advanced.php` | page | `advanced.php` | yes |
| `advanced_grading_form` | form | `classes/form/advanced_grading_form.php` | yes |
| `advanced_grading` | service | `classes/local/grading/advanced_grading.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The choice is encoded, decoded and checked against the definition | main flow, RN-017 | `tests/local/advanced_grading_test.php` |
