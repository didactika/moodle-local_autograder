---
title: Capacidades
summary: Cada capacidad del plugin, quién la tiene de forma predeterminada y qué permite.
order: 41
---

| Capacidad | Contexto | Roles predeterminados | Permite al usuario |
|---|---|---|---|
| `local/autograder:configure` | Actividad | Docente con permiso de edición, Gestor | Activar, desactivar y configurar Autograder en una actividad |
| `local/autograder:manage` | Sistema | Gestor | Elegir qué tipos de actividad pueden usar Autograder |

Autograder no añade una capacidad para decidir a nombre de quién puede registrarse una calificación. Usa la capacidad de calificar de la propia actividad: `mod/assign:grade`, `mod/forum:grade`, `mod/quiz:grade`, o `moodle/grade:edit` para los demás tipos de actividad.

Las capacidades para ver lo que Autograder decidió pertenecen al [informe de Autograder](https://github.com/didactika/moodle-report_autograder).
