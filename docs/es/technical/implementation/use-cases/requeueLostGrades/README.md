---
id: UC-009
name: requeueLostGrades
title: Poner de nuevo en cola las calificaciones que perdieron su tarea
status: draft
origin: code
sourceOfTruth: code
---

# UC-009 Poner de nuevo en cola las calificaciones que perdieron su tarea — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/requeueLostGrades/README.md) | [Análisis](../../../analysis/use-cases/requeueLostGrades/README.md) | [Diseño](../../../design/use-cases/requeueLostGrades/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/requeueLostGrades/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/requeueLostGrades/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `reconcile_pending` | tarea | `classes/task/reconcile_pending.php` | sí |
| `db/tasks.php` | archivo | `db/tasks.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Las decisiones huérfanas se encuentran y se ponen de nuevo en cola | flujo principal, RN-013 | `tests/local/decision_repository_test.php` |
