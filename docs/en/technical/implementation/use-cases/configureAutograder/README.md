---
id: UC-001
name: configureAutograder
title: Configure Autograder in an activity
status: draft
origin: code
sourceOfTruth: code
---

# UC-001 Configure Autograder in an activity — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/configureAutograder/README.md) | [Analysis](../../../analysis/use-cases/configureAutograder/README.md) | [Design](../../../design/use-cases/configureAutograder/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/configureAutograder/README.md#tests) |

Design: [README](../../../design/use-cases/configureAutograder/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `autograder_section` | form section | `classes/form/autograder_section.php` | yes |
| `local_autograder_coursemodule_edit_post_actions` | hook | `lib.php:61` | yes |
| `config_repository` | service | `classes/local/config/config_repository.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The section validates the grade and the time | alternative flow A1 | `tests/form/autograder_section_test.php` |
| The section appears in the form of the activity | main flow | `tests/form/module_form_test.php` |
