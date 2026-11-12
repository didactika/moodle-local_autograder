---
id: UC-012
name: managePersonalData
title: Exportar o eliminar datos personales
status: draft
origin: code
sourceOfTruth: code
---

# UC-012 Exportar o eliminar datos personales — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/managePersonalData/README.md) | [Análisis](../../../analysis/use-cases/managePersonalData/README.md) | [Diseño](../../../design/use-cases/managePersonalData/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/managePersonalData/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/managePersonalData/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `provider` | proveedor | `classes/privacy/provider.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Los datos se enumeran, se exportan y se eliminan | flujo principal, RN-015 | `tests/privacy/provider_test.php` |
