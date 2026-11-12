---
id: UC-010
name: purgeHistory
title: Eliminar el historial antiguo
status: draft
origin: code
sourceOfTruth: code
---

# UC-010 Eliminar el historial antiguo — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/purgeHistory/README.md) | [Análisis](../../../analysis/use-cases/purgeHistory/README.md) | [Diseño](../../../design/use-cases/purgeHistory/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/purgeHistory/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/purgeHistory/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `purge_history` | tarea | `classes/task/purge_history.php` | sí |
| `db/tasks.php` | archivo | `db/tasks.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Las decisiones terminadas anteriores a la fecha se eliminan y las pendientes no | flujo principal, RN-009 | `tests/local/decision_repository_test.php` |
