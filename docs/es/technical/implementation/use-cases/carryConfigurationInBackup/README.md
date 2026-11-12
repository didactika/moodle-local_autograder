---
id: UC-011
name: carryConfigurationInBackup
title: Llevar la configuración en una copia de seguridad del curso
status: draft
origin: code
sourceOfTruth: code
---

# UC-011 Llevar la configuración en una copia de seguridad del curso — implementación

| Resumen | Requisitos | Análisis | Diseño | Implementación | Pruebas |
| --- | --- | --- | --- | --- | --- |
| [Casos de uso](../../../requirements/use-case-overview.svg) | [Requisitos](../../../requirements/use-cases/carryConfigurationInBackup/README.md) | [Análisis](../../../analysis/use-cases/carryConfigurationInBackup/README.md) | [Diseño](../../../design/use-cases/carryConfigurationInBackup/README.md) | **Implementación** | [Pruebas](../../../implementation/use-cases/carryConfigurationInBackup/README.md#pruebas) |

Diseño: [README](../../../design/use-cases/carryConfigurationInBackup/README.md)

## Código

| Elemento | Tipo | Ubicación | Verificado |
| --- | --- | --- | --- |
| `backup_local_autograder_plugin` | copia de seguridad | `backup/moodle2/backup_local_autograder_plugin.class.php` | sí |
| `restore_local_autograder_plugin` | restauración | `backup/moodle2/restore_local_autograder_plugin.class.php` | sí |

## Pruebas

| Prueba | Cubre | Ubicación |
| --- | --- | --- |
| La configuración se copia y se restaura | flujo principal, RN-014 | `tests/backup_restore_test.php` |
