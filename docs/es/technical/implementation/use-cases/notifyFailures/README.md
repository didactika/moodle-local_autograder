---
id: UC-008
name: notifyFailures
title: Avisar a los docentes de los estudiantes que no se pudieron calificar
status: draft
origin: code
sourceOfTruth: code
---

# UC-008 Avisar a los docentes de los estudiantes que no se pudieron calificar — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/notifyFailures/README.md) | [Análisis](../../../analysis/use-cases/notifyFailures/README.md) | [Diseño](../../../design/use-cases/notifyFailures/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/notifyFailures/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/notifyFailures/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `notify_failures` | tarea | `classes/task/notify_failures.php` | sí |
| `db/tasks.php` | archivo | `db/tasks.php` | sí |
| `db/messages.php` | archivo | `db/messages.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Los fallos se agrupan, se envían una vez y se recuerda el punto de la ejecución | flujo principal, RN-012 | `tests/task/notify_failures_test.php` |
