---
title: Solución de problemas
summary: Qué comprobar cuando algo no funciona como se espera.
order: 80
---

El [informe de Autograder](page:guides/review-decisions) muestra el estado de cada estudiante y, en una calificación fallida, el motivo. Suele ser la forma más rápida de saber qué ocurrió.

| Problema | Causa posible |
|---|---|
| Un estudiante que completó la actividad no se califica | La fecha límite más el tiempo de espera todavía no han pasado, no se cumplen todas las condiciones de finalización, o cron no se ejecuta |
| La sección Autograder no aparece en los ajustes de una actividad | El tipo de actividad no está habilitado en la página **Tipos de actividad**, la actividad no se califica, o no tiene `local/autograder:configure` |
| Tras guardar aparece «Autograder se dejó desactivado» | No se ha hecho la selección de la rúbrica o de la guía de evaluación, o, en otros tipos de actividad, no hay una condición de finalización que el estudiante pueda cumplir. El mensaje indica cuál |
| La calificación falla porque no se encuentra calificador | Ninguno de los docentes del estudiante puede calificar la actividad o ver el grupo del estudiante, y no hay calificador de reserva |
| Un cuestionario sigue mostrando la calificación de Autograder tras corregirlo | Otra pregunta del cuestionario sigue esperando corrección manual. La calificación del cuestionario sustituye a la de Autograder cuando todas las preguntas están corregidas |
