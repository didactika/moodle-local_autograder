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
| `modules.php` | page | `modules.php` | yes |
| Activity types script | AMD | `amd/src/modules.js` | yes |
| Activity types template | template | `templates/modules.mustache` | yes |
| `eligibility` | service | `classes/local/config/eligibility.php` | yes |
| Upgrade step | upgrade | `db/upgrade.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The rule of the activity types and the upgrade that withdraws the others | main flow, RN-010 | `tests/local/eligibility_test.php` |
| | The form of the activity follows the enabled types | main flow, RN-007 | `tests/form/module_form_test.php` |
