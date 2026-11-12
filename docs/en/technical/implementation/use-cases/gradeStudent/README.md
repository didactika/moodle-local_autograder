---
id: UC-003
name: gradeStudent
title: Grade a student
status: draft
origin: code
sourceOfTruth: code
---

# UC-003 Grade a student — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/gradeStudent/README.md) | [Analysis](../../../analysis/use-cases/gradeStudent/README.md) | [Design](../../../design/use-cases/gradeStudent/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/gradeStudent/README.md#tests) |

Design: [README](../../../design/use-cases/gradeStudent/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `grade_student` | task | `classes/task/grade_student.php` | yes |
| `grader_picker` | service | `classes/local/grading/grader_picker.php` | yes |
| Adapters of the activities | services | `classes/local/module/` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The task grades, cancels, moves and fails | main flow, alternative flows | `tests/task/grade_student_test.php` |
| The choice of the teacher | main flow, RN-004 | `tests/local/grader_picker_test.php` |
| Who counts as a student | RN-005 | `tests/local/gradable_student_test.php` |
