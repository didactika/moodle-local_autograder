---
id: UC-007
name: setSitePolicy
title: Set the policy of the site
status: draft
origin: code
sourceOfTruth: code
---

# UC-007 Set the policy of the site — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/setSitePolicy/README.md) | [Analysis](../../../analysis/use-cases/setSitePolicy/README.md) | [Design](../../../design/use-cases/setSitePolicy/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/setSitePolicy/README.md#tests) |

Design: [README](../../../design/use-cases/setSitePolicy/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `settings.php` | admin setting | `settings.php` | yes |
| `fallback_grader_setting` | admin setting | `classes/local/config/fallback_grader_setting.php` | yes |
| `search_graders` | web service | `classes/external/search_graders.php` | yes |
| `grader_search` | service | `classes/local/config/grader_search.php` | yes |
| `teacher_source` | service | `classes/local/grading/teacher_source.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| Who is chosen to sign, with the roles and the tie-break of the site | RN-004, RN-016 | `tests/local/grader_picker_test.php` |
| The fallback grader is searched as the name is typed and saved | main flow | `tests/behat/pages.feature` |
