---
id: UC-002
name: chooseActivityTypes
title: Choose the activity types
status: draft
origin: code
sourceOfTruth: code
---

# UC-002 Choose the activity types — design realization

| Overview | Requirements | Analysis | Design | Implementation | Tests |
| --- | --- | --- | --- | --- | --- |
| [Use cases](../../../requirements/use-case-overview.svg) | [Requirements](../../../requirements/use-cases/chooseActivityTypes/README.md) | [Analysis](../../../analysis/use-cases/chooseActivityTypes/README.md) | **Design** | [Implementation](../../../implementation/use-cases/chooseActivityTypes/README.md) | [Tests](../../../implementation/use-cases/chooseActivityTypes/README.md#tests) |

Analysis: [README](../../../analysis/use-cases/chooseActivityTypes/README.md)

## Elements

| Design element | Kind | Code target |
| --- | --- | --- |
| `modules.php` | page | `modules.php` |
| `eligibility::set_module_type_enabled` | service | `classes/local/config/eligibility.php:108` |
| `cancel_module` task | task | `classes/task/cancel_module.php:41` |

## Sequence

![Sequence](./sequence.svg)

## Contracts

| Operation | Input | Output | Errors |
| --- | --- | --- | --- |
| `modules.php?modname=&enabled=&sesskey=` | the type, the new state and the session key | a redirect with a message | invalid session key, no capability |

## Data touched

| Table or entity | Operation |
| --- | --- |
| `config_plugins` | update (enabled types) |
| `local_autograder_decision` | update (cancelled) |
