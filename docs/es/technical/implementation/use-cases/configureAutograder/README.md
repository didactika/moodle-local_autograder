---
id: UC-001
name: configureAutograder
title: Configurar Autograder en una actividad
status: draft
origin: code
sourceOfTruth: code
---

# UC-001 Configurar Autograder en una actividad — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/configureAutograder/README.md) | [Análisis](../../../analysis/use-cases/configureAutograder/README.md) | [Diseño](../../../design/use-cases/configureAutograder/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/configureAutograder/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/configureAutograder/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `autograder_section` | sección de formulario | `classes/form/autograder_section.php` | sí |
| `local_autograder_coursemodule_edit_post_actions` | gancho | `lib.php:61` | sí |
| `config_repository` | servicio | `classes/local/config/config_repository.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La sección valida la calificación y el tiempo | flujo alternativo A1 | `tests/form/autograder_section_test.php` |
| La sección aparece en el formulario de la actividad | flujo principal | `tests/form/module_form_test.php` |
