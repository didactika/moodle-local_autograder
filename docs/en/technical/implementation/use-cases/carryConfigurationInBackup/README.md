---
id: UC-011
name: carryConfigurationInBackup
title: Carry the configuration in a course backup
status: draft
origin: code
sourceOfTruth: code
---

# UC-011 Carry the configuration in a course backup — implementation

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/carryConfigurationInBackup/README.md) | [Analysis](../../../analysis/use-cases/carryConfigurationInBackup/README.md) | [Design](../../../design/use-cases/carryConfigurationInBackup/README.md) | **Implementation** | [Tests](../../../implementation/use-cases/carryConfigurationInBackup/README.md#tests) |

Design: [README](../../../design/use-cases/carryConfigurationInBackup/README.md)

## Code

| Element | Kind | Location | Verified |
| --- | --- | --- | --- |
| `backup_local_autograder_plugin` | backup | `backup/moodle2/backup_local_autograder_plugin.class.php` | yes |
| `restore_local_autograder_plugin` | restore | `backup/moodle2/restore_local_autograder_plugin.class.php` | yes |

## Tests

| Test | Covers | Location |
| --- | --- | --- |
| The configuration is backed up and restored | main flow, RN-014 | `tests/backup_restore_test.php` |
