---
id: UC-003
name: gradeStudent
title: Calificar a un estudiante
status: draft
origin: code
sourceOfTruth: code
---

# UC-003 Calificar a un estudiante — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/gradeStudent/README.md) | [Análisis](../../../analysis/use-cases/gradeStudent/README.md) | [Diseño](../../../design/use-cases/gradeStudent/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/gradeStudent/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/gradeStudent/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `grade_student` | tarea | `classes/task/grade_student.php` | sí |
| `grader_picker` | servicio | `classes/local/grading/grader_picker.php` | sí |
| Adaptadores de las actividades | servicios | `classes/local/module/` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La tarea califica, cancela, mueve y falla | flujo principal, flujos alternativos | `tests/task/grade_student_test.php` |
| La elección del docente | flujo principal, RN-004 | `tests/local/grader_picker_test.php` |
| Quién cuenta como estudiante | RN-005 | `tests/local/gradable_student_test.php` |
