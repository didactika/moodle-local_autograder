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
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Define primero la rúbrica o guía de evaluación de esta actividad y vuelve para indicar qué niveles debe marcar autograder.';
$string['advanced:error_score_range'] = 'Debe estar entre 0 y {$a}.';
$string['advanced:heading'] = 'Niveles que marca autograder';
$string['advanced:intro'] = 'Elige qué marca autograder en cada criterio de <strong>{$a}</strong>. Califica exactamente como si un docente los hubiera marcado a mano, así que Moodle calcula la nota resultante por su cuenta.';
$string['advanced:level'] = 'Nivel';
$string['advanced:not_advanced'] = 'Esta actividad no se califica con rúbrica ni con guía de evaluación.';
$string['advanced:remark'] = 'Comentario (opcional)';
$string['advanced:saved'] = 'Guardado lo que marcará autograder.';
$string['advanced:score'] = 'Puntuación (sobre {$a})';
$string['autograder:configure'] = 'Activar o desactivar autograder en una actividad';
$string['autograder:gradeonbehalf'] = 'Ser elegible para que autograder ponga notas en tu nombre';
$string['autograder:manage'] = 'Gestionar los ajustes de autograder a nivel de sitio';
$string['autograder:viewreport'] = 'Ver el informe de autograder de un curso';
$string['error:gradewritefailed'] = 'Moodle rechazó la nota que autograder intentó poner.';
$string['error:nogradeitem'] = 'Esta actividad no tiene ítem de calificación donde escribir.';
$string['event:config_created'] = 'Configuración de autograder creada';
$string['event:config_deleted'] = 'Configuración de autograder eliminada';
$string['event:config_updated'] = 'Configuración de autograder actualizada';
$string['form:advanced_define_first'] = 'Esta actividad se califica con rúbrica o guía, pero todavía no hay ninguna definida. <a href="{$a}">Defínela primero</a> y luego elige qué marca autograder.';
$string['form:advanced_set'] = 'Autograder ya sabe qué marcar en esta rúbrica o guía. <a href="{$a}">Cambiarlo</a>.';
$string['form:advanced_undefined'] = 'Esta actividad se califica con una rúbrica o guía que autograder no puede leer.';
$string['form:advanced_unset'] = 'Elige <a href="{$a}">qué marca autograder</a> en esta rúbrica o guía — hasta entonces no tiene con qué calificar.';
$string['form:days_to_complete'] = 'Días';
$string['form:enabled'] = 'Activar autograder';
$string['form:enabled_help'] = 'Si se activa, un alumno que complete esta actividad se calificará automáticamente, el tiempo configurado después de su vencimiento, con la nota indicada abajo — salvo que alguien lo califique a mano antes.';
$string['form:error_negative_time'] = 'No puede ser negativo.';
$string['form:error_not_graded'] = 'Autograder necesita que la actividad sea calificable. Elige un tipo de calificación distinto de «Ninguna».';
$string['form:error_numeric'] = 'Debe ser un número.';
$string['form:error_scale_unset'] = 'Elige qué ítem de la escala debe asignar autograder.';
$string['form:grade'] = 'Nota a asignar';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
$string['form:time_to_complete'] = 'Tiempo de espera antes de calificar';
$string['form:time_to_complete_help'] = 'Cuánto esperar, tras la fecha de vencimiento, antes de calificar.';
$string['modules:intro'] = 'Solo los tipos de actividad activados aquí pueden tener autograder configurado en alguna de sus instancias.';
$string['pluginname'] = 'Autograder';
$string['preference:optout'] = 'No permitir que autograder califique en mi nombre';
$string['preference:optout_help'] = 'Si se marca, autograder nunca te elegirá como el docente que califica a un alumno, aunque de otro modo cumplieras los requisitos.';
$string['preference:saved'] = 'Preferencia guardada.';
$string['privacy:metadata'] = 'Autograder guarda, por alumno, si y cuándo debe calificarse automáticamente, y un registro de cada intento de calificación.';
$string['setting:default_days'] = 'Espera por defecto (días)';
$string['setting:default_grade'] = 'Nota por defecto';
$string['setting:default_grade_desc'] = 'Nota sugerida al activar autograder por primera vez en una actividad.';
$string['setting:default_hours'] = 'Espera por defecto (horas)';
$string['setting:default_minutes'] = 'Espera por defecto (minutos)';
$string['setting:default_time_desc'] = 'Cuánto esperar, tras la fecha de vencimiento, antes de calificar — repartido en días, horas y minutos.';
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
