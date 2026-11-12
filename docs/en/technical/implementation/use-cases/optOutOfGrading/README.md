---
id: UC-006
name: optOutOfGrading
title: Opt out of having grades recorded in my name
status: draft
origin: code
sourceOfTruth: code
---

# UC-006 Opt out of having grades recorded in my name — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/optOutOfGrading/README.md) | [Analysis](../../../analysis/use-cases/optOutOfGrading/README.md) | [Design](../../../design/use-cases/optOutOfGrading/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/optOutOfGrading/README.md#tests) |

Design: [README](../../../design/use-cases/optOutOfGrading/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `optout.php` | page | `optout.php` | yes |
| `optout_form` | form | `classes/form/optout_form.php` | yes |
| `grader_picker` | service | `classes/local/grading/grader_picker.php` | yes |
| Privacy provider | provider | `classes/privacy/provider.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Users who opted out are not chosen to sign | RN-011 | `tests/local/grader_picker_test.php` |
| The preference is exported | main flow | `tests/privacy/provider_test.php` |
