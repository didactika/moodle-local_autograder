---
id: UC-004
name: reconsiderDecision
title: Reconsiderar una decisión cuando algo cambia
status: draft
origin: code
sourceOfTruth: code
---

# UC-004 Reconsiderar una decisión cuando algo cambia — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/reconsiderDecision/README.md) | [Análisis](../../../analysis/use-cases/reconsiderDecision/README.md) | [Diseño](../../../design/use-cases/reconsiderDecision/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/reconsiderDecision/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/reconsiderDecision/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `observer` | observadores de eventos | `classes/observer.php` | sí |
| `decision_repository` | servicio | `classes/local/decision/decision_repository.php` | sí |
| Observadores registrados | archivo | `db/events.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Los observadores reconsideran al estudiante correcto | flujo principal | `tests/observer_test.php` |
| Reconsiderar crea, mueve y cancela | flujo principal, flujos alternativos | `tests/local/reconsider_test.php` |
| El almacén de decisiones | RN-006 | `tests/local/decision_repository_test.php` |
