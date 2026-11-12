---
id: REQ
status: draft
origin: requirements
sourceOfTruth: model
---

# Requisitos

## Índice

- [Actores](./actors.md)
- [Catálogo de requisitos](./requirements.md)

## Casos de uso

| ID | Caso de uso | Actor | Paquete | Requisito | Análisis | Diseño | Implementación |
| --- | --- | --- | --- | --- | --- | --- | --- |
| UC-001 | [Configurar Autograder en una actividad](./use-cases/configureAutograder/README.md) | Docente | Configuración | RF-001 | [ficha](../analysis/use-cases/configureAutograder/README.md) | [ficha](../design/use-cases/configureAutograder/README.md) | [ficha](../implementation/use-cases/configureAutograder/README.md) |
| UC-002 | [Elegir los tipos de actividad](./use-cases/chooseActivityTypes/README.md) | Administrador | Configuración | RF-002 | [ficha](../analysis/use-cases/chooseActivityTypes/README.md) | [ficha](../design/use-cases/chooseActivityTypes/README.md) | [ficha](../implementation/use-cases/chooseActivityTypes/README.md) |
| UC-003 | [Calificar a un estudiante](./use-cases/gradeStudent/README.md) | Sistema | Calificación | RF-003 | [ficha](../analysis/use-cases/gradeStudent/README.md) | [ficha](../design/use-cases/gradeStudent/README.md) | [ficha](../implementation/use-cases/gradeStudent/README.md) |
| UC-004 | [Reconsiderar una decisión cuando algo cambia](./use-cases/reconsiderDecision/README.md) | Sistema | Calificación | RF-004 | [ficha](../analysis/use-cases/reconsiderDecision/README.md) | [ficha](../design/use-cases/reconsiderDecision/README.md) | [ficha](../implementation/use-cases/reconsiderDecision/README.md) |
| UC-005 | [Elegir el resultado de una rúbrica o pauta de calificación](./use-cases/chooseAdvancedGrade/README.md) | Docente | Configuración | RF-006 | [ficha](../analysis/use-cases/chooseAdvancedGrade/README.md) | [ficha](../design/use-cases/chooseAdvancedGrade/README.md) | [ficha](../implementation/use-cases/chooseAdvancedGrade/README.md) |
| UC-006 | [Excluirme de que se registren calificaciones a mi nombre](./use-cases/optOutOfGrading/README.md) | Docente | Configuración | RF-007 | [ficha](../analysis/use-cases/optOutOfGrading/README.md) | [ficha](../design/use-cases/optOutOfGrading/README.md) | [ficha](../implementation/use-cases/optOutOfGrading/README.md) |
| UC-007 | [Establecer la política del sitio](./use-cases/setSitePolicy/README.md) | Administrador | Configuración | RF-008 | [ficha](../analysis/use-cases/setSitePolicy/README.md) | [ficha](../design/use-cases/setSitePolicy/README.md) | [ficha](../implementation/use-cases/setSitePolicy/README.md) |
| UC-008 | [Avisar a los docentes de los estudiantes que no se pudieron calificar](./use-cases/notifyFailures/README.md) | Sistema | Notificación | RF-005 | [ficha](../analysis/use-cases/notifyFailures/README.md) | [ficha](../design/use-cases/notifyFailures/README.md) | [ficha](../implementation/use-cases/notifyFailures/README.md) |
| UC-009 | [Poner de nuevo en cola las calificaciones que perdieron su tarea](./use-cases/requeueLostGrades/README.md) | Sistema | Calificación | RF-009 | [ficha](../analysis/use-cases/requeueLostGrades/README.md) | [ficha](../design/use-cases/requeueLostGrades/README.md) | [ficha](../implementation/use-cases/requeueLostGrades/README.md) |
| UC-010 | [Eliminar el historial antiguo](./use-cases/purgeHistory/README.md) | Sistema | Mantenimiento | RF-010 | [ficha](../analysis/use-cases/purgeHistory/README.md) | [ficha](../design/use-cases/purgeHistory/README.md) | [ficha](../implementation/use-cases/purgeHistory/README.md) |
| UC-011 | [Llevar la configuración en una copia de seguridad del curso](./use-cases/carryConfigurationInBackup/README.md) | Docente | Mantenimiento | RF-011 | [ficha](../analysis/use-cases/carryConfigurationInBackup/README.md) | [ficha](../design/use-cases/carryConfigurationInBackup/README.md) | [ficha](../implementation/use-cases/carryConfigurationInBackup/README.md) |
| UC-012 | [Exportar o eliminar datos personales](./use-cases/managePersonalData/README.md) | Administrador | Privacidad | RF-012 | [ficha](../analysis/use-cases/managePersonalData/README.md) | [ficha](../design/use-cases/managePersonalData/README.md) | [ficha](../implementation/use-cases/managePersonalData/README.md) |

### Diagrama de casos de uso

![Casos de uso](./use-case-overview.svg)

### Por actor

#### Docente

![Docente](./use-cases-teacher.svg)

#### Administrador

![Administrador](./use-cases-administrator.svg)

#### Sistema

![Sistema](./use-cases-system.svg)
