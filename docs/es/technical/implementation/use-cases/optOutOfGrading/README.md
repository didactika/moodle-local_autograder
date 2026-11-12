---
id: UC-006
name: optOutOfGrading
title: Excluirme de que se registren calificaciones a mi nombre
status: draft
origin: code
sourceOfTruth: code
---

# UC-006 Excluirme de que se registren calificaciones a mi nombre — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/optOutOfGrading/README.md) | [Análisis](../../../analysis/use-cases/optOutOfGrading/README.md) | [Diseño](../../../design/use-cases/optOutOfGrading/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/optOutOfGrading/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/optOutOfGrading/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `optout.php` | página | `optout.php` | sí |
| `optout_form` | formulario | `classes/form/optout_form.php` | sí |
| `grader_picker` | servicio | `classes/local/grading/grader_picker.php` | sí |
| Privacy provider | proveedor | `classes/privacy/provider.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Los usuarios que se excluyeron no son elegidos para firmar | RN-011 | `tests/local/grader_picker_test.php` |
| La preferencia se exporta | flujo principal | `tests/privacy/provider_test.php` |
