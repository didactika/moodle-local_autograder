---
title: Privacidad y copia de seguridad
summary: Qué almacena el plugin sobre las personas y cómo lo tratan la copia de seguridad y la restauración.
order: 43
---

## Privacidad

Autograder implementa la API de privacidad. Para cada estudiante almacena la decisión de calificación programada o completada y un registro de los intentos de calificación, incluido el docente a cuyo nombre se registró cada calificación. Almacena también la preferencia de exclusión de cada docente. Todos estos datos pueden exportarse y eliminarse.

## Copia de seguridad y restauración

Los ajustes de Autograder de una actividad, incluidas las selecciones de la rúbrica y de la guía de evaluación, se incluyen en la copia de seguridad de la actividad. Las calificaciones programadas no se copian: se calculan de nuevo en el curso restaurado.
