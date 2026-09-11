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

$string['autograder:configure'] = 'Activar o desactivar autograder en una actividad';
$string['autograder:gradeonbehalf'] = 'Ser elegible para que autograder ponga notas en tu nombre';
$string['autograder:manage'] = 'Gestionar los ajustes de autograder a nivel de sitio';
$string['autograder:viewreport'] = 'Ver el informe de autograder de un curso';
$string['event:config_created'] = 'Configuración de autograder creada';
$string['event:config_deleted'] = 'Configuración de autograder eliminada';
$string['event:config_updated'] = 'Configuración de autograder actualizada';
$string['form:days_to_complete'] = 'Días';
$string['form:enabled'] = 'Activar autograder';
$string['form:enabled_help'] = 'Si se activa, un alumno que complete esta actividad se calificará automáticamente, el tiempo configurado después de su vencimiento, con la nota indicada abajo — salvo que alguien lo califique a mano antes.';
$string['form:error_completion_tracking'] = 'Autograder necesita que el seguimiento de finalización esté activado en esta actividad.';
$string['form:error_negative_time'] = 'No puede ser negativo.';
$string['form:error_numeric'] = 'Debe ser un número.';
$string['form:grade'] = 'Nota a asignar';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
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
