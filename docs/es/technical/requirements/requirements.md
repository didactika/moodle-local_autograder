---
id: RQ
status: draft
origin: code
sourceOfTruth: code
---

# Catálogo de requisitos

## Funcionales

| ID | Requisito | Prioridad | Origen | Casos de uso |
| --- | --- | --- | --- | --- |
| RF-001 | Un docente puede activar Autograder en una actividad y elegir la calificación a asignar y el tiempo de espera, con puntos, escala, rúbrica o guía de evaluación. | Debe | `classes/form/autograder_section.php` | UC-001 |
| RF-002 | Un administrador puede elegir qué tipos de actividad pueden usar Autograder. | Debe | `modules.php` | UC-002 |
| RF-003 | Un estudiante que hizo una actividad se califica cuando han pasado la fecha límite y el tiempo de espera, a nombre de uno de sus docentes, salvo que un docente calificara antes. | Debe | `classes/task/grade_student.php` | UC-003 |
| RF-004 | Las decisiones siguen lo que ocurre en el curso: entregas, finalización, excepciones, matriculaciones y roles. | Debe | `classes/observer.php` | UC-004 |
| RF-005 | Se avisa semanalmente a los docentes de qué estudiantes no pudieron calificarse. | Podría | `classes/task/notify_failures.php` | UC-008 |
| RF-006 | Un docente puede elegir el resultado que da Autograder con una rúbrica o una pauta de calificación. | Debe | `advanced.php` | UC-005 |
| RF-007 | Un usuario puede excluirse de que se registren calificaciones a su nombre, cuando el sitio lo ofrece. | Podría | `optout.php` | UC-006 |
| RF-008 | Un administrador puede establecer la política del sitio: los avisos, la retención, los roles que cuentan como docentes, cómo elegir entre ellos y el calificador de reserva. | Debe | `settings.php` | UC-007 |
| RF-009 | Una decisión que venció y perdió su tarea vuelve a ponerse en cola. | Debería | `classes/task/reconcile_pending.php` | UC-009 |
| RF-010 | Las decisiones terminadas y el registro más antiguos que la retención del sitio se eliminan. | Debería | `classes/task/purge_history.php` | UC-010 |
| RF-011 | La configuración de una actividad viaja en una copia de seguridad del curso y los estudiantes se deciden de nuevo tras una restauración. | Debería | `backup/moodle2/` | UC-011 |
| RF-012 | Los datos de un usuario pueden exportarse y eliminarse mediante la API de privacidad. | Debe | `classes/privacy/provider.php` | UC-012 |

## No funcionales

| ID | Atributo de calidad | Requisito | Medida | Prioridad |
| --- | --- | --- | --- | --- |
| RNF-001 | Integridad | Una calificación manual nunca se sobrescribe. | `existing_person_grade` se comprueba antes de escribir | Debe |
| RNF-002 | Corrección | Un evento perdido cuesta rapidez, nunca corrección: la tarea de calificación vuelve a leer todas las condiciones. | `grade_student::execute` | Debe |
| RNF-003 | Privacidad | El plugin almacena cada decisión y el calificador de cada calificación, e implementa la API de privacidad. | `classes/privacy/provider.php` | Debe |
| RNF-004 | Recuperabilidad | Una calificación cuya tarea se perdió vuelve a encolarse. | `reconcile_pending` cada 30 minutos | Debería |

## Reglas de negocio

Las reglas están en el [diccionario del modelo de dominio](../domain/README.md#reglas-de-negocio). Las que cambian el comportamiento se repiten aquí con su ID.
