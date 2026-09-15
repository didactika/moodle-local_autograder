<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Spanish language strings.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Define primero la rúbrica o guía de evaluación de esta actividad y vuelve para indicar qué niveles debe marcar autograder.';
$string['advanced:edit_definition'] = 'Para cambiar la rúbrica o guía de evaluación en sí, <a href="{$a}">edita su definición</a>.';
$string['advanced:heading'] = 'Niveles que marca autograder';
$string['advanced:intro'] = 'Rellena esto igual que lo harías al calificar a mano a un estudiante de <strong>{$a}</strong>. Autograder marca lo que elijas aquí, y Moodle calcula la nota a partir de ello como lo hace siempre.';
$string['advanced:marks'] = 'Lo que marca autograder';
$string['advanced:not_advanced'] = 'Esta actividad no se califica con rúbrica ni con guía de evaluación.';
$string['advanced:save'] = 'Guardar lo que marca autograder';
$string['advanced:saved'] = 'Guardado lo que marcará autograder.';
$string['autograder:configure'] = 'Activar o desactivar autograder en una actividad';
$string['autograder:gradeonbehalf'] = 'Ser elegible para que autograder ponga notas en tu nombre';
$string['autograder:manage'] = 'Gestionar los ajustes de autograder a nivel de sitio';
$string['error:advancedgradingstale'] = 'La rúbrica o guía de evaluación ha cambiado desde que se le indicó a autograder qué marcar en ella. Abre las opciones de autograder de la actividad y vuelve a elegir los niveles.';
$string['error:gradewritefailed'] = 'Moodle rechazó la nota que autograder intentó poner.';
$string['error:nogradeitem'] = 'Esta actividad no tiene ítem de calificación donde escribir.';
$string['event:config_created'] = 'Configuración de autograder creada';
$string['event:config_deleted'] = 'Configuración de autograder eliminada';
$string['event:config_updated'] = 'Configuración de autograder actualizada';
$string['event:decision_cancelled'] = 'Decisión de autocalificación cancelada';
$string['event:grading_failed'] = 'Fallo al autocalificar';
$string['event:student_graded'] = 'Estudiante autocalificado';
$string['form:advanced_define_first'] = 'Esta actividad se califica con rúbrica o guía, pero todavía no hay ninguna definida. <a href="{$a}">Defínela primero</a> y luego elige qué marca autograder.';
$string['form:advanced_set'] = 'Autograder ya sabe qué marcar en esta rúbrica o guía. <a href="{$a}">Cambiarlo</a>.';
$string['form:advanced_undefined'] = 'Esta actividad se califica con una rúbrica o guía que autograder no puede leer.';
$string['form:advanced_unset'] = 'Elige <a href="{$a}">qué marca autograder</a> en esta rúbrica o guía — hasta entonces no tiene con qué calificar.';
$string['form:days_to_complete'] = 'Días';
$string['form:enabled'] = 'Activar autograder';
$string['form:enabled_help'] = 'Si se activa, un alumno que complete esta actividad se calificará automáticamente, el tiempo configurado después de su vencimiento, con la nota indicada abajo — salvo que alguien lo califique a mano antes.';
$string['form:error_advanced_unset'] = 'A autograder no se le ha dicho qué marcar en la rúbrica o guía de evaluación de esta actividad. <a href="{$a}">Elígelo primero</a> y después actívalo.';
$string['form:error_grade_above_max'] = 'Esta actividad se califica sobre {$a}, así que la nota no puede ser mayor.';
$string['form:error_grade_negative'] = 'La nota no puede ser negativa.';
$string['form:error_grade_required'] = 'Indica la nota que debe poner autograder.';
$string['form:error_hours_range'] = 'Indica de 0 a 23 horas. Para más tiempo usa el campo de días.';
$string['form:error_minutes_range'] = 'Indica de 0 a 59 minutos. Para más tiempo usa el campo de horas.';
$string['form:error_negative_time'] = 'No puede ser negativo.';
$string['form:error_not_graded'] = 'Autograder necesita que la actividad sea calificable. Elige un tipo de calificación distinto de «Ninguna».';
$string['form:error_numeric'] = 'Debe ser un número.';
$string['form:error_scale_mismatch'] = 'Ese elemento no pertenece a la escala que usa ahora la actividad. Elige uno de la escala que acabas de seleccionar.';
$string['form:error_scale_unset'] = 'Elige qué ítem de la escala debe asignar autograder.';
$string['form:error_whole_number'] = 'Indica un número entero.';
$string['form:grade'] = 'Nota a asignar';
$string['form:grade_range'] = 'Esta actividad se califica sobre {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
$string['form:time_to_complete'] = 'Tiempo de espera antes de calificar';
$string['form:time_to_complete_help'] = 'Cuánto esperar, tras la fecha de vencimiento, antes de calificar.';
$string['modules:intro'] = 'Solo los tipos de actividad activados aquí pueden tener autograder configurado en alguna de sus instancias.';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Configuración de autograder';
$string['preference:notoffered'] = 'Este sitio no permite que los profesores se excluyan de que se califique en su nombre.';
$string['preference:optout'] = 'No permitir que autograder califique en mi nombre';
$string['preference:optout_help'] = 'Si se marca, autograder nunca te elegirá como el docente que califica a un alumno, aunque de otro modo cumplieras los requisitos.';
$string['preference:saved'] = 'Preferencia guardada.';
$string['privacy:metadata'] = 'Autograder guarda, por alumno, si y cuándo debe calificarse automáticamente, y un registro de cada intento de calificación.';
$string['privacy:metadata:config'] = 'Lo que un profesor configuró que autograder hiciera en una actividad.';
$string['privacy:metadata:config:cmid'] = 'La actividad a la que pertenece la configuración.';
$string['privacy:metadata:config:timemodified'] = 'Cuándo se guardó la configuración por última vez.';
$string['privacy:metadata:config:usermodified'] = 'El usuario que guardó la configuración por última vez.';
$string['privacy:metadata:decision'] = 'La decisión de autograder, pendiente o resuelta, sobre un estudiante en una actividad.';
$string['privacy:metadata:decision:baselineduedate'] = 'La fecha desde la que se cuenta el retraso.';
$string['privacy:metadata:decision:duedatereason'] = 'Por qué se usó esa fecha: finalización, entrega, fecha de cierre o una excepción.';
$string['privacy:metadata:decision:failurereason'] = 'Por qué se canceló la decisión o no pudo llevarse a cabo.';
$string['privacy:metadata:decision:gradedvalue'] = 'La calificación que puso autograder.';
$string['privacy:metadata:decision:graderid'] = 'El profesor en cuyo nombre se puso la calificación.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Cuándo toca, o tocaba, poner la calificación.';
$string['privacy:metadata:decision:status'] = 'Si la decisión está esperando, calificada, tomada a mano, cancelada o fallida.';
$string['privacy:metadata:decision:timemodified'] = 'Cuándo cambió la decisión por última vez.';
$string['privacy:metadata:decision:userid'] = 'El estudiante al que se refiere la decisión.';
$string['privacy:metadata:gradelog'] = 'El registro de lo que autograder hizo, lo que no hizo y por qué.';
$string['privacy:metadata:gradelog:graderid'] = 'El profesor en cuyo nombre se puso la calificación.';
$string['privacy:metadata:gradelog:gradevalue'] = 'La calificación puesta, cuando la hubo.';
$string['privacy:metadata:gradelog:message'] = 'Qué ocurrió, en palabras.';
$string['privacy:metadata:gradelog:outcome'] = 'Si el estudiante fue calificado, omitido, cancelado o falló.';
$string['privacy:metadata:gradelog:timecreated'] = 'Cuándo ocurrió.';
$string['privacy:metadata:gradelog:userid'] = 'El estudiante al que se refiere la entrada.';
$string['privacy:metadata:preference:optout'] = 'Si este usuario ha pedido no ser elegido como el profesor en cuyo nombre califica autograder.';
$string['privacy:path:config'] = 'Configuración de autograder';
$string['privacy:path:decision'] = 'Decisiones de autograder';
$string['privacy:path:gradelog'] = 'Historial de autograder';
$string['setting:allowoptout'] = 'Permitir que los profesores se excluyan';
$string['setting:allowoptout_desc'] = 'Ofrece a cada usuario una preferencia para pedir que autograder no califique nunca en su nombre. Desactivado por defecto: sacar profesores del reparto cambia a quién se califica y cuándo, y en un sitio con pocos profesores elegibles puede dejar una actividad sin nadie en cuyo nombre calificar. Mientras esté desactivado, la preferencia ni se muestra ni se tiene en cuenta.';
$string['setting:fallback_grader'] = 'Calificador de respaldo';
$string['setting:fallback_grader_desc'] = 'Se usa solo cuando ningún docente del propio curso puede calificar a un alumno (ver la capacidad "gradeonbehalf"). Solo se ofrecen usuarios que plausiblemente podrían calificar algo.';
$string['setting:fallback_grader_ineligible'] = 'Ese usuario no tiene ninguna capacidad de calificación y no puede configurarse como calificador de respaldo.';
$string['setting:fallback_grader_none'] = 'Ninguno';
$string['setting:fallback_grader_placeholder'] = 'Buscar un usuario…';
$string['setting:modules_disable'] = 'Desactivar autograder para {$a}';
$string['setting:modules_enable'] = 'Activar autograder para {$a}';
$string['setting:modules_enabled_column'] = 'Activo';
$string['setting:modules_heading'] = 'Elige qué tipos de actividad pueden configurar autograder en la <a href="{$a->url}">página de actividades autocalificables</a>.';
$string['setting:retentiondays'] = 'Retención (días)';
$string['setting:retentiondays_desc'] = 'Cuánto se conserva una decisión terminada y su registro de calificación antes de purgarse.';
$string['setting:tiebreak'] = 'Regla de desempate';
$string['setting:tiebreak_desc'] = 'Cuando más de un docente puede calificar a un alumno, cuál de ellos se elige.';
$string['setting:tiebreak_last_course_access'] = 'El que accedió al curso más recientemente';
$string['setting:tiebreak_lowest_userid'] = 'El de menor ID de usuario';
$string['settings:generaltab'] = 'General';
$string['settings:modulestab'] = 'Actividades autocalificables';
$string['settings:retentiontab'] = 'Retención';
$string['task:cancel_module'] = 'Cancelar las autocalificaciones pendientes de una actividad';
$string['task:catch_up_module'] = 'Poner al día a los estudiantes que ya esperan en una actividad';
$string['task:grade_student'] = 'Calificar a un estudiante';
$string['task:purge_history'] = 'Purgar las decisiones y el registro de autocalificación ya terminados';
$string['task:recalculate_module'] = 'Recalcular las fechas de autocalificación de una actividad';
$string['task:reconcile_pending'] = 'Reencolar las decisiones de autocalificación que perdieron su tarea';
