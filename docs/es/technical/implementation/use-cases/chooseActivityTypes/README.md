---
id: UC-002
name: chooseActivityTypes
title: Elegir los tipos de actividad
status: draft
origin: code
sourceOfTruth: code
---

# UC-002 Elegir los tipos de actividad — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/chooseActivityTypes/README.md) | [Análisis](../../../analysis/use-cases/chooseActivityTypes/README.md) | [Diseño](../../../design/use-cases/chooseActivityTypes/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/chooseActivityTypes/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/chooseActivityTypes/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `modules.php` | página | `modules.php` | sí |
| Script de tipos de actividad | AMD | `amd/src/modules.js` | sí |
| Plantilla de tipos de actividad | plantilla | `templates/modules.mustache` | sí |
| `eligibility` | servicio | `classes/local/config/eligibility.php` | sí |
| Paso de actualización | actualización | `db/upgrade.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La regla de los tipos de actividad y la actualización que retira los demás | flujo principal, RN-010 | `tests/local/eligibility_test.php` |
| | El formulario de la actividad sigue a los tipos habilitados | flujo principal, RN-007 | `tests/form/module_form_test.php` |
