---
id: ACT
status: draft
origin: code
sourceOfTruth: code
---

# Actores

| Actor | Tipo | Objetivo | Casos de uso relacionados |
| --- | --- | --- | --- |
| Docente | Primario | Que los estudiantes que participaron se califiquen a tiempo, sin calificar a cada uno. Tiene `local/autograder:configure` en la actividad. | [UC-001](./use-cases/configureAutograder/README.md), [UC-005](./use-cases/chooseAdvancedGrade/README.md), [UC-006](./use-cases/optOutOfGrading/README.md), [UC-011](./use-cases/carryConfigurationInBackup/README.md) |
| Administrador | Primario | Decidir qué clases de actividad pueden usar Autograder y la política del sitio. Tiene `local/autograder:manage`. | [UC-002](./use-cases/chooseActivityTypes/README.md), [UC-007](./use-cases/setSitePolicy/README.md), [UC-012](./use-cases/managePersonalData/README.md) |
| Sistema | Secundario | Cron y los observadores de Moodle que planifican y ejecutan las calificaciones. | [UC-003](./use-cases/gradeStudent/README.md), [UC-004](./use-cases/reconsiderDecision/README.md), [UC-008](./use-cases/notifyFailures/README.md), [UC-009](./use-cases/requeueLostGrades/README.md), [UC-010](./use-cases/purgeHistory/README.md) |
| Moodle | Secundario | Aporta la finalización, el libro de calificaciones, los métodos de calificación, los eventos, las tareas y las capacidades en que se apoya el plugin. | todos |
