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
| `set_module_type` | servicio web | `classes/external/set_module_type.php` | sí |
| Página de tipos de actividad | componente React | `js/esm/src/Modules.tsx` | sí |
| `modules_page` | renderable | `classes/output/modules_page.php` | sí |
| `eligibility` | servicio | `classes/local/config/eligibility.php` | sí |
| Paso de actualización | actualización | `db/upgrade.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La regla de los tipos de actividad y la actualización que retira los demás | flujo principal, RN-010 | `tests/local/eligibility_test.php` |
| | Cambiar un tipo modifica el ajuste | flujo principal | `tests/external/set_module_type_test.php` |
| La página de tipos de actividad | flujo principal | `tests/behat/pages.feature` |
