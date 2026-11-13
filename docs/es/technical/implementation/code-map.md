---
id: MAP
status: draft
origin: code
sourceOfTruth: code
---

# Mapa del código

| Elemento del modelo | Tipo | Ubicación en el código | Verificado |
| --- | --- | --- | --- |
| Configuration | tabla | `db/install.xml` (`local_autograder_config`) | sí |
| Decision | tabla | `db/install.xml` (`local_autograder_decision`) | sí |
| Registro de calificación | tabla | `db/install.xml` (`local_autograder_grade_log`) | sí |
| Guardar la configuración | método | `classes/local/config/config_repository.php:101` | sí |
| Sección del formulario de la actividad | clase | `classes/form/autograder_section.php` | sí |
| Tipos de actividad habilitados | método | `classes/local/config/eligibility.php:111` | sí |
| Regla de los tipos de actividad | métodos | `classes/local/config/eligibility.php:182`, `:211`, `:236` | sí |
| Planificar una decisión | método | `classes/local/decision/decision_planner.php:62` | sí |
| Fecha de vencimiento | método | `classes/local/decision/due_date_calculator.php` | sí |
| Ciclo de vida de la decisión | métodos | `classes/local/decision/decision_repository.php:207`, `:413`, `:440` | sí |
| Tarea de calificación | método | `classes/task/grade_student.php:56` | sí |
| Elección del docente | método | `classes/local/grading/grader_picker.php:56` | sí |
| Escribir la calificación | método | `classes/local/module/module_adapter.php:173` | sí |
| Observadores de eventos | clase | `classes/observer.php`, `db/events.php` | sí |
| Capacidades del plugin | archivo | `db/access.php` | sí |
| Tareas programadas | archivo | `db/tasks.php` | sí |
| Eventos del plugin | clases | `classes/event/` | sí |
| Privacidad | clase | `classes/privacy/provider.php` | sí |
