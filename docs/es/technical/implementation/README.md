---
id: IMP
status: draft
origin: code
sourceOfTruth: code
---

# Implementación

Esta carpeta vincula el modelo con el código. El [mapa del código](./code-map.md) enumera cada elemento del modelo y dónde vive; cada caso de uso tiene una ficha con su código y sus pruebas.

| ID | Caso de uso | Ficha |
| --- | --- | --- |
| UC-001 | Configurar Autograder en una actividad | [implementación](./use-cases/configureAutograder/README.md) |
| UC-002 | Elegir los tipos de actividad | [implementación](./use-cases/chooseActivityTypes/README.md) |
| UC-003 | Calificar a un estudiante | [implementación](./use-cases/gradeStudent/README.md) |
| UC-004 | Reconsiderar una decisión cuando algo cambia | [implementación](./use-cases/reconsiderDecision/README.md) |
| UC-005 | Elegir el resultado de una rúbrica o pauta de calificación | [implementación](./use-cases/chooseAdvancedGrade/README.md) |
| UC-006 | Excluirme de que se registren calificaciones a mi nombre | [implementación](./use-cases/optOutOfGrading/README.md) |
| UC-007 | Establecer la política del sitio | [implementación](./use-cases/setSitePolicy/README.md) |
| UC-008 | Avisar a los docentes de los estudiantes que no se pudieron calificar | [implementación](./use-cases/notifyFailures/README.md) |
| UC-009 | Poner de nuevo en cola las calificaciones que perdieron su tarea | [implementación](./use-cases/requeueLostGrades/README.md) |
| UC-010 | Eliminar el historial antiguo | [implementación](./use-cases/purgeHistory/README.md) |
| UC-011 | Llevar la configuración en una copia de seguridad del curso | [implementación](./use-cases/carryConfigurationInBackup/README.md) |
| UC-012 | Exportar o eliminar datos personales | [implementación](./use-cases/managePersonalData/README.md) |
