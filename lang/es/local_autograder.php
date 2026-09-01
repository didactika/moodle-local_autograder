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
 * Plugin strings are defined here.
 *
 * @package     local_autograder
 * @category    string
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @author      Eduardo Cubias <eduardo.cubias@ct.uneatlantico.es>
 * @author      Hector Arrechea <hector.arrechea@uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Local Autograder';

$string['form:heading'] = 'Calificación Automática';
$string['form:enabled'] = 'Activar la calificación automática para esta actividad';
$string['form:grade'] = 'Nota automática a asignar';
$string['form:error_numeric'] = 'Ingrese solo valor numérico entero';
$string['form:error_max_grade'] = 'La puntuación de calificación automática no puede ser superior a la puntuación máxima';
$string['form:days_to_complete'] = 'Días de espera para autocalificación';
$string['form:hours_to_complete'] = 'Horas de espera para autocalificación';
$string['form:minutes_to_complete'] = 'Minutos de espera para autocalificación';
$string['form:time_to_complete'] = 'Tiempo de espera para calificar';
$string['form:enabled_help'] = 'Activa/desactiva la calificación automática para esta actividad. Si está activado, se asignará la nota automática tras el tiempo configurado.';
$string['form:grade_help'] = 'Nota numérica entera que se asignará automáticamente cuando finalice el tiempo establecido.';
$string['form:time_to_complete_help'] = 'Establece el plazo (días o horas o minutos) tras el cual se aplicará la calificación automática.';
$string['form:error_days_range'] = 'Los días deben estar entre 0 y 100.';
$string['form:error_hours_range'] = 'Las horas deben estar entre 0 y 24.';
$string['form:error_minutes_range'] = 'Los minutos deben estar entre 0 y 60.';
$string['form:error_all_time_zero'] = 'Al menos uno de días, horas o minutos debe ser mayor que 0.';
$string['form:error_completion_tracking'] = 'La calificación automática está habilitada, establezca el seguimiento de finalización';
$string['form:error_type_grade'] = 'La calificación automática está habilitada, solo se acepta el tipo de puntuación';

$string['settings:enable'] = 'Habilitar plugin autograder';
$string['settings:enableDescription'] = 'Valor por defecto: Sí';

$string['setting:days_to_completeTitle'] = 'Días de espera para autocalificación';
$string['setting:days_to_completeHelper'] = 'Cantidad de días permitidos que debe esperar el sistema antes de la calificación automática.';

$string['setting:hours_to_completeTitle'] = 'Horas de espera para autocalificación';
$string['setting:hours_to_completeHelper'] = 'Cantidad de horas permitidas (además de los días) que debe esperar el sistema antes de la calificación automática.';

$string['setting:minutes_to_completeTitle'] = 'Minutos de espera para autocalificación';
$string['setting:minutes_to_completeHelper'] = 'Cantidad de minutos permitidos (además de días y horas) que debe esperar el sistema antes de la calificación automática.';

$string['setting:default_gradeTitle'] = 'Nota automática a asignar';
$string['setting:default_gradeHelper'] = 'Nota predeterminada a asignar si no se especifica una nota concreta. Ingrese solo valor numérico entero';

$string['event:autograder_created'] = 'Autocalificador Creado';
$string['event:autograder_updated'] = 'Autocalificador Actualizado';

$string['form:error_completion_tracking'] = 'Para habilitar el autocalificador, debe activar el rastreo de finalización en la sección "Finalización de actividad".';
$string['form:error_type_grade'] = 'El autocalificador solo funciona con actividades configuradas con calificación por "Puntuación" (no escalas ni sin calificación).';