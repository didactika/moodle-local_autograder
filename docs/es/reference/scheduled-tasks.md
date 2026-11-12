---
title: Tareas programadas
summary: Las tareas que se ejecutan solas y cómo se ejecuta cada calificación.
order: 42
---

| Tarea | Programación predeterminada | Propósito |
|---|---|---|
| Volver a encolar las decisiones de Autograder que perdieron su tarea | Cada 30 minutos | Vuelve a encolar toda calificación vencida cuya tarea se ha perdido |
| Depurar las decisiones terminadas de Autograder y el registro de calificación | A diario a las 03:30 | Elimina el historial más antiguo que **Conservar el historial durante** |
| Notificar a los docentes los estudiantes que Autograder no pudo calificar | Los lunes hacia las 07:00 | Envía el resumen de fallos, si está activado |

Cada calificación se ejecuta como su propia tarea ad hoc, programada para el momento en que vence. Como la calificación se realiza con tareas programadas y tareas ad hoc, [cron](https://docs.moodle.org/es/Cron) debe ejecutarse con regularidad para que las calificaciones se asignen a tiempo.

Además de estas, el plugin encola tareas ad hoc cuando algo cambia: calcula las decisiones de una actividad cuando se activa Autograder en ella, las recalcula cuando la actividad cambia y las cancela cuando se desactiva Autograder o se elimina la actividad.
