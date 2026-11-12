---
id: UC-007
name: setSitePolicy
title: Establecer la política del sitio
status: draft
origin: code
sourceOfTruth: code
---

# UC-007 Establecer la política del sitio — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/setSitePolicy/README.md) | [Análisis](../../../analysis/use-cases/setSitePolicy/README.md) | [Diseño](../../../design/use-cases/setSitePolicy/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/setSitePolicy/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/setSitePolicy/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `settings.php` | ajuste de administración | `settings.php` | sí |
| `fallback_grader_setting` | ajuste de administración | `classes/local/config/fallback_grader_setting.php` | sí |
| `search_graders` | servicio web | `classes/external/search_graders.php` | sí |
| `grader_search` | servicio | `classes/local/config/grader_search.php` | sí |
| `teacher_source` | servicio | `classes/local/grading/teacher_source.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| Quién es elegido para firmar, con los roles y el desempate del sitio | RN-004, RN-016 | `tests/local/grader_picker_test.php` |
| El calificador de reserva se busca mientras se escribe el nombre y se guarda | flujo principal | `tests/behat/pages.feature` |
