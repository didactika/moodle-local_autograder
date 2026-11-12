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
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Defina primero la rúbrica o la pauta de calificación de esta actividad y después vuelva para indicar qué niveles debe marcar Autograder.';
$string['advanced:edit_definition'] = 'Para cambiar la propia rúbrica o pauta de calificación, <a href="{$a}">edite su definición</a>.';
$string['advanced:heading'] = 'Niveles que marca Autograder';
$string['advanced:intro'] = 'Rellénelo exactamente como lo haría al calificar a mano a un estudiante de <strong>{$a}</strong>. Autograder marca lo que usted elija aquí y Moodle calcula la calificación a partir de ello del mismo modo que siempre.';
$string['advanced:marks'] = 'Lo que marca Autograder';
$string['advanced:not_advanced'] = 'Esta actividad no se califica con una rúbrica ni con una pauta de calificación.';
$string['advanced:save'] = 'Guardar lo que marca Autograder';
$string['advanced:saved'] = 'Se guardó lo que marcará Autograder.';
$string['autograder:configure'] = 'Activar o desactivar Autograder en una actividad';
$string['autograder:manage'] = 'Gestionar los ajustes de Autograder de todo el sitio';
$string['error:advancedgradingstale'] = 'La rúbrica o la pauta de calificación ha cambiado desde que se indicó a Autograder qué marcar en ella. Abra los ajustes de Autograder de la actividad y elija de nuevo los niveles.';
$string['error:gradewritefailed'] = 'Moodle rechazó la calificación que Autograder intentó publicar.';
$string['error:nogradeitem'] = 'Esta actividad no tiene un elemento de calificación en el que escribir.';
$string['event:config_created'] = 'Configuración de Autograder creada';
$string['event:config_deleted'] = 'Configuración de Autograder eliminada';
$string['event:config_updated'] = 'Configuración de Autograder actualizada';
$string['event:decision_cancelled'] = 'Decisión de Autograder cancelada';
$string['event:grading_failed'] = 'Calificación de Autograder fallida';
$string['event:student_graded'] = 'Estudiante calificado por Autograder';
$string['form:advanced_define_first'] = 'Esta actividad se califica con una rúbrica o una pauta de calificación, pero todavía no hay ninguna definida. <a href="{$a}">Defínala primero</a> y después elija lo que marca Autograder.';
$string['form:advanced_set'] = 'Autograder sabe qué marcar en esta rúbrica o pauta. <a href="{$a}">Cámbielo</a>.';
$string['form:advanced_undefined'] = 'Esta actividad se califica con una rúbrica o una pauta de calificación que Autograder no puede leer.';
$string['form:advanced_unset'] = 'Elija <a href="{$a}">qué marca Autograder</a> en esta rúbrica o pauta; hasta entonces no tiene con qué calificar.';
$string['form:days_to_complete'] = 'Días';
$string['form:enabled'] = 'Activar Autograder';
$string['form:enabled_help'] = 'Cuando está activado, el estudiante que completa esta actividad se califica automáticamente, transcurrido el tiempo configurado desde su vencimiento, con la calificación indicada más abajo, salvo que alguien lo califique antes a mano.';
$string['form:error_advanced_unset'] = 'No se ha indicado a Autograder qué marcar en la rúbrica o la pauta de calificación de esta actividad. <a href="{$a}">Elíjalo primero</a> y después active Autograder.';
$string['form:error_grade_above_max'] = 'Esta actividad se califica sobre {$a}, de modo que la calificación no puede ser superior.';
$string['form:error_grade_negative'] = 'La calificación no puede ser negativa.';
$string['form:error_grade_required'] = 'Introduzca la calificación que debe dar Autograder.';
$string['form:error_hours_range'] = 'Introduzca de 0 a 23 horas. Use el campo de días para cualquier valor mayor.';
$string['form:error_minutes_range'] = 'Introduzca de 0 a 59 minutos. Use el campo de horas para cualquier valor mayor.';
$string['form:error_negative_time'] = 'No puede ser negativo.';
$string['form:error_not_graded'] = 'Autograder necesita que esta actividad se califique. Elija un tipo de calificación distinto de «Ninguna».';
$string['form:error_numeric'] = 'Debe ser un número.';
$string['form:error_scale_mismatch'] = 'Ese elemento no pertenece a la escala que usa ahora esta actividad. Elija uno de la escala que acaba de seleccionar.';
$string['form:error_scale_unset'] = 'Elija qué elemento de la escala debe asignar Autograder.';
$string['form:error_whole_number'] = 'Introduzca un número entero.';
$string['form:grade'] = 'Calificación a asignar';
$string['form:grade_range'] = 'Esta actividad se califica sobre {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
$string['form:needs_completion'] = 'Autograder solo puede saber que un estudiante ha realizado este tipo de actividad mediante la finalización de la actividad. Establezca una condición que el estudiante pueda cumplir por sí mismo: marcarla como hecha, verla o una de las condiciones propias de la actividad. No incluya «Recibir una calificación» ni «Recibir una calificación de aprobado»: mientras la calificación no exista, la actividad nunca cuenta como hecha, de modo que nada indicaría a Autograder que el estudiante ha terminado, y la calificación que se espera sería la que daría Autograder.';
$string['form:notenabled_advanced'] = 'Autograder se dejó desactivado: no se le ha indicado qué marcar en la rúbrica o la pauta de calificación de esta actividad. <a href="{$a}">Elíjalo primero</a> y después actívelo.';
$string['form:notenabled_nocompletion'] = 'Autograder se dejó desactivado: solo puede saber que un estudiante ha realizado este tipo de actividad mediante la finalización de la actividad, y la finalización de esta actividad no tiene ninguna condición que el estudiante pueda cumplir por sí mismo o exige también una calificación.';
$string['form:quiz_note'] = 'En un cuestionario, Autograder solo interviene mientras una pregunta, como un ensayo, espera corrección manual, y entonces califica todo el cuestionario. Cuando el docente la corrige, la calificación propia del cuestionario ocupa su lugar. Un cuestionario que se corrige solo conserva su propia calificación.';
$string['form:time_to_complete'] = 'Tiempo de espera antes de calificar';
$string['form:time_to_complete_help'] = 'Cuánto esperar, después de la fecha límite, antes de calificar.';
$string['messageprovider:failuredigest'] = 'Estudiantes que Autograder no pudo calificar';
$string['modules:count'] = '{$a} tipos de actividad';
$string['modules:empty'] = 'Ningún tipo de actividad coincide.';
$string['modules:failed'] = 'El cambio no se guardó: {$a}';
$string['modules:filter_all'] = 'Todos';
$string['modules:filter_off'] = 'Desactivados';
$string['modules:filter_on'] = 'Activados';
$string['modules:intro'] = 'Desactivar un tipo oculta los ajustes de Autograder de sus actividades y cancela la calificación que seguía pendiente para ellas. Cada actividad conserva su propio ajuste de Autograder, de modo que al activar de nuevo el tipo se reanudan, salvo donde ya se registró una calificación.';
$string['modules:lead'] = 'Los tipos de actividad que reciben el trabajo de los estudiantes y tienen una calificación que otorga un docente';
$string['modules:loading'] = 'Cargando los tipos de actividad…';
$string['modules:name'] = 'Nombre';
$string['modules:reads_assign'] = 'Lee las entregas de los estudiantes';
$string['modules:reads_completion'] = 'Sigue la finalización de la actividad';
$string['modules:reads_forum'] = 'Lee los mensajes de los estudiantes';
$string['modules:reads_quiz'] = 'Lee los intentos de los estudiantes';
$string['modules:search'] = 'Buscar tipos de actividad';
$string['notify:footer'] = 'Recibe este mensaje porque puede configurar Autograder en estas actividades.';
$string['notify:intro'] = 'Desde el último resumen, Autograder no pudo calificar a algunos estudiantes en las actividades siguientes. Esos estudiantes no han sido calificados.';
$string['notify:more'] = 'Actividades que no se enumeran aquí: {$a}.';
$string['notify:reason_grade_write_failed'] = 'Moodle rechazó la calificación. Las causas habituales son una rúbrica o una pauta de calificación que cambió desde que se indicó a Autograder qué marcar en ella, o un docente que no tiene permiso para calificar la actividad.';
$string['notify:reason_line'] = 'Estudiantes afectados: {$a->count}. {$a->reason}';
$string['notify:reason_no_grader'] = 'No se encontró ningún docente a cuyo nombre registrar la calificación y no hay un calificador de reserva que usar en su lugar.';
$string['notify:reason_other'] = 'No se pudo registrar la calificación.';
$string['notify:small'] = 'Autograder no pudo calificar a algunos estudiantes. Actividades afectadas: {$a}.';
$string['notify:subject'] = 'Autograder no pudo calificar a algunos estudiantes';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Preferencias de Autograder';
$string['preference:notoffered'] = 'Este sitio no permite que los docentes se excluyan de Autograder.';
$string['preference:optout'] = 'No registrar nunca calificaciones automáticas a mi nombre';
$string['preference:optout_help'] = 'Autograder no registrará nunca calificaciones a su nombre, ni siquiera en los cursos en los que usted enseña.';
$string['preference:saved'] = 'Preferencia guardada.';
$string['privacy:metadata:config'] = 'Lo que un docente configuró que Autograder hiciera en una actividad.';
$string['privacy:metadata:config:cmid'] = 'La actividad a la que pertenece la configuración.';
$string['privacy:metadata:config:timemodified'] = 'Cuándo se guardó por última vez la configuración.';
$string['privacy:metadata:config:usermodified'] = 'El usuario que guardó por última vez la configuración.';
$string['privacy:metadata:core_message'] = 'Se puede enviar a los docentes un resumen de los estudiantes que Autograder no pudo calificar, mediante el sistema de mensajería.';
$string['privacy:metadata:decision'] = 'La decisión pendiente o resuelta de Autograder sobre un estudiante en una actividad.';
$string['privacy:metadata:decision:baselineduedate'] = 'La fecha desde la que se cuenta la espera.';
$string['privacy:metadata:decision:duedatereason'] = 'Por qué se usó esa fecha: la finalización, la entrega, la fecha de cierre o una excepción.';
$string['privacy:metadata:decision:failurereason'] = 'Por qué la decisión se canceló o no pudo llevarse a cabo.';
$string['privacy:metadata:decision:gradedvalue'] = 'La calificación que publicó Autograder.';
$string['privacy:metadata:decision:graderid'] = 'El docente a cuyo nombre se publicó la calificación.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Cuándo debe publicarse, o debía haberse publicado, la calificación.';
$string['privacy:metadata:decision:status'] = 'Si la decisión está en espera, calificada, asumida a mano, cancelada o fallida.';
$string['privacy:metadata:decision:timemodified'] = 'Cuándo cambió por última vez la decisión.';
$string['privacy:metadata:decision:userid'] = 'El estudiante al que se refiere la decisión.';
$string['privacy:metadata:gradelog'] = 'Un registro de lo que Autograder hizo y no hizo, y por qué.';
$string['privacy:metadata:gradelog:graderid'] = 'El docente a cuyo nombre se publicó la calificación.';
$string['privacy:metadata:gradelog:gradevalue'] = 'La calificación publicada, cuando la hubo.';
$string['privacy:metadata:gradelog:message'] = 'Lo que ocurrió, con palabras.';
$string['privacy:metadata:gradelog:outcome'] = 'Si el estudiante se calificó, se omitió, se canceló o falló, o si la calificación de Autograder cedió ante la propia de la actividad.';
$string['privacy:metadata:gradelog:timecreated'] = 'Cuándo ocurrió.';
$string['privacy:metadata:gradelog:userid'] = 'El estudiante al que se refiere la entrada.';
$string['privacy:metadata:preference:optout'] = 'Si este usuario ha pedido no ser elegido como el docente a cuyo nombre califica Autograder.';
$string['privacy:path:config'] = 'Configuración de Autograder';
$string['privacy:path:decision'] = 'Decisiones de Autograder';
$string['privacy:path:gradelog'] = 'Historial de Autograder';
$string['setting:allowoptout'] = 'Permitir que los docentes se excluyan';
$string['setting:allowoptout_desc'] = 'Añade una página de preferencias en la que los usuarios pueden pedir que Autograder no registre nunca calificaciones a su nombre. En sitios con pocos docentes, las exclusiones pueden dejar a los estudiantes sin nadie a cuyo nombre calificar. Cuando está desactivado, la página se oculta y las exclusiones existentes se ignoran.';
$string['setting:fallback_grader'] = 'Calificador de reserva';
$string['setting:fallback_grader_desc'] = 'Las calificaciones se registran a nombre de este usuario cuando no se puede elegir un docente según las reglas automáticas, o cuando falla la calificación a nombre del docente elegido. Solo se enumeran los usuarios con un rol que califica; nunca se usa a los administradores del sitio. A diferencia de los docentes, este usuario no se comprueba en cada actividad, de modo que elija a alguien que pueda calificar en todos los sitios donde se use Autograder. Si es Ninguno, esos estudiantes no se califican y el fallo se registra.';
$string['setting:fallback_grader_ineligible'] = 'Este usuario no puede ser el calificador de reserva. Nunca se usa a los administradores del sitio ni a los usuarios sin un rol que pueda calificar.';
$string['setting:fallback_grader_nomatches'] = 'Ningún usuario coincide.';
$string['setting:fallback_grader_none'] = 'Ninguno';
$string['setting:fallback_grader_placeholder'] = 'Buscar un usuario…';
$string['setting:fallback_grader_searching'] = 'Buscando…';
$string['setting:grading_heading'] = 'Calificación e historial';
$string['setting:modules_disable'] = 'Desactivar Autograder para {$a}';
$string['setting:modules_disabled'] = 'Autograder desactivado para {$a}.';
$string['setting:modules_enable'] = 'Activar Autograder para {$a}';
$string['setting:modules_enabled'] = 'Autograder activado para {$a}.';
$string['setting:modules_enabled_column'] = 'Activado';
$string['setting:modules_heading'] = 'Elija qué tipos de actividad pueden usar Autograder en la página <a href="{$a->url}">Tipos de actividad</a>.';
$string['setting:notifyfailures'] = 'Avisar a los docentes de los fallos de calificación';
$string['setting:notifyfailures_desc'] = 'Envía a todas las personas matriculadas en el curso que pueden configurar Autograder en una actividad un resumen de los estudiantes que no pudo calificar en ella, agrupado por actividad. Se envía semanalmente de forma predeterminada; el horario puede cambiarse en Tareas programadas. Cada fallo se informa una vez, a partir del momento en que se activa esta opción.';
$string['setting:notifystudent'] = 'Notificar a los estudiantes';
$string['setting:notifystudent_desc'] = 'Envía al estudiante la notificación de calificación de Moodle cuando Autograder califica una tarea o un foro, como si un docente hubiera elegido notificar al calificar. Solo afecta a las tareas y a los foros.';
$string['setting:retentiondays'] = 'Conservar el historial durante (días)';
$string['setting:retentiondays_desc'] = 'Las decisiones terminadas (calificadas, canceladas, fallidas…) y las entradas del registro de calificaciones más antiguas que este valor se eliminan a diario mediante una tarea programada. Las decisiones pendientes nunca se eliminan. Introduzca 0 para conservarlo todo.';
$string['setting:teacher_roles'] = 'Roles de docente seleccionados';
$string['setting:teacher_roles_desc'] = 'Roles que cuentan como docentes. Nunca se elige a los usuarios cuyos roles no estén seleccionados aquí. Los roles seleccionados deben poder calificar las actividades en las que se usa Autograder.';
$string['setting:teacher_source_mode'] = 'Roles de docente';
$string['setting:teacher_source_mode_chosen'] = 'Solo los roles seleccionados';
$string['setting:teacher_source_mode_desc'] = 'El modo automático incluye todos los roles con moodle/grade:edit o con una capacidad de calificación de actividad (como mod/assign:grade), de modo que los nuevos roles personalizados funcionan sin configuración adicional.';
$string['setting:teacher_source_mode_grading'] = 'Automático: cualquier rol que pueda calificar';
$string['setting:teachers_heading'] = 'Las calificaciones se registran a nombre de uno de los docentes del estudiante: usuarios con un rol de docente asignado en el propio curso, no en su categoría ni en todo el sitio, cuya matriculación en el curso está activa. Nunca se usa a los administradores del sitio ni a los usuarios suspendidos, ni a un docente que no puede calificar la actividad o que no puede ver el grupo del estudiante en una actividad con grupos separados. Se prefiere al docente que comparte más grupos con el estudiante; si ninguno comparte un grupo, a un docente que no esté en ningún grupo; y si todos los docentes están en algún otro grupo, a cualquiera de ellos. En los cursos con grupos separados y un agrupamiento predeterminado, solo cuentan los grupos de ese agrupamiento.';
$string['setting:tiebreak'] = 'Selección del docente';
$string['setting:tiebreak_desc'] = 'Cuál se elige cuando más de un docente puede calificar a un estudiante.';
$string['setting:tiebreak_last_course_access'] = 'Acceso más reciente al curso';
$string['setting:tiebreak_lowest_userid'] = 'ID de usuario más bajo';
$string['settings:generaltab'] = 'General';
$string['settings:modulestab'] = 'Tipos de actividad';
$string['settings:teacherstab'] = 'Docentes';
$string['task:cancel_module'] = 'Cancelar la calificación de Autograder pendiente en una actividad';
$string['task:catch_up_module'] = 'Poner al día a los estudiantes que ya esperan en una actividad';
$string['task:grade_student'] = 'Calificar a un estudiante';
$string['task:notify_failures'] = 'Avisar a los docentes de los estudiantes que Autograder no pudo calificar';
$string['task:purge_history'] = 'Depurar las decisiones terminadas de Autograder y el registro de calificaciones';
$string['task:recalculate_module'] = 'Recalcular las fechas de vencimiento de Autograder de una actividad';
$string['task:reconcile_pending'] = 'Volver a poner en cola las decisiones de Autograder que perdieron su tarea';
