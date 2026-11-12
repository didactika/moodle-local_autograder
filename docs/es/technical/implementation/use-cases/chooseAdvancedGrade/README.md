---
id: UC-005
name: chooseAdvancedGrade
title: Elegir el resultado de una rúbrica o pauta de calificación
status: draft
origin: code
sourceOfTruth: code
---

# UC-005 Elegir el resultado de una rúbrica o pauta de calificación — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/chooseAdvancedGrade/README.md) | [Análisis](../../../analysis/use-cases/chooseAdvancedGrade/README.md) | [Diseño](../../../design/use-cases/chooseAdvancedGrade/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/chooseAdvancedGrade/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/chooseAdvancedGrade/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `advanced.php` | página | `advanced.php` | sí |
| `advanced_grading_form` | formulario | `classes/form/advanced_grading_form.php` | sí |
| `advanced_grading` | servicio | `classes/local/grading/advanced_grading.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La elección se codifica, se decodifica y se comprueba con la definición | flujo principal, RN-017 | `tests/local/advanced_grading_test.php` |
