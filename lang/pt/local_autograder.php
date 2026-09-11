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
 * Portuguese language strings.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Defina primeiro a rubrica ou o guia de avaliação desta atividade e volte para indicar que níveis o autograder deve marcar.';
$string['advanced:error_score_range'] = 'Deve estar entre 0 e {$a}.';
$string['advanced:heading'] = 'Níveis que o autograder marca';
$string['advanced:intro'] = 'Escolha o que o autograder marca em cada critério de <strong>{$a}</strong>. Avalia exatamente como se um professor os tivesse marcado à mão, por isso o Moodle calcula sozinho a nota resultante.';
$string['advanced:level'] = 'Nível';
$string['advanced:not_advanced'] = 'Esta atividade não é avaliada por rubrica nem por guia de avaliação.';
$string['advanced:remark'] = 'Comentário (opcional)';
$string['advanced:saved'] = 'Guardado o que o autograder vai marcar.';
$string['advanced:score'] = 'Pontuação (em {$a})';
$string['autograder:configure'] = 'Ativar ou desativar o autograder numa atividade';
$string['autograder:gradeonbehalf'] = 'Ser elegível para que o autograder atribua notas em seu nome';
$string['autograder:manage'] = 'Gerir as definições do autograder ao nível do site';
$string['autograder:viewreport'] = 'Ver o relatório do autograder de um curso';
$string['error:gradewritefailed'] = 'O Moodle recusou a nota que o autograder tentou atribuir.';
$string['error:nogradeitem'] = 'Esta atividade não tem item de avaliação onde escrever.';
$string['event:config_created'] = 'Configuração do autograder criada';
$string['event:config_deleted'] = 'Configuração do autograder eliminada';
$string['event:config_updated'] = 'Configuração do autograder atualizada';
$string['event:decision_cancelled'] = 'Decisão de avaliação automática cancelada';
$string['event:grading_failed'] = 'Falha na avaliação automática';
$string['event:student_graded'] = 'Estudante avaliado automaticamente';
$string['form:advanced_define_first'] = 'Esta atividade é avaliada por rubrica ou guia, mas ainda não há nenhuma definida. <a href="{$a}">Defina-a primeiro</a> e depois escolha o que o autograder marca.';
$string['form:advanced_set'] = 'O autograder já sabe o que marcar nesta rubrica ou guia. <a href="{$a}">Alterar</a>.';
$string['form:advanced_undefined'] = 'Esta atividade é avaliada por uma rubrica ou guia que o autograder não consegue ler.';
$string['form:advanced_unset'] = 'Escolha <a href="{$a}">o que o autograder marca</a> nesta rubrica ou guia — até lá não tem com que avaliar.';
$string['form:days_to_complete'] = 'Dias';
$string['form:enabled'] = 'Ativar autograder';
$string['form:enabled_help'] = 'Se ativado, um estudante que conclua esta atividade é avaliado automaticamente, o tempo configurado após o prazo, com a nota indicada abaixo — a menos que alguém o avalie manualmente antes.';
$string['form:error_negative_time'] = 'Não pode ser negativo.';
$string['form:error_not_graded'] = 'O autograder precisa que esta atividade seja avaliada. Escolha um tipo de nota diferente de "Nenhuma".';
$string['form:error_numeric'] = 'Deve ser um número.';
$string['form:error_scale_unset'] = 'Escolha que item da escala o autograder deve atribuir.';
$string['form:grade'] = 'Nota a atribuir';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
$string['form:time_to_complete'] = 'Tempo de espera antes de avaliar';
$string['form:time_to_complete_help'] = 'Quanto tempo esperar, após a data limite, antes de avaliar.';
$string['modules:intro'] = 'Só os tipos de atividade ativados aqui podem ter o autograder configurado numa das suas instâncias.';
$string['pluginname'] = 'Autograder';

$string['preference:optout'] = 'Não permitir que o autograder avalie em meu nome';
$string['preference:optout_help'] = 'Se marcado, o autograder nunca o escolherá como o professor que avalia um estudante, mesmo que de outro modo fosse elegível.';
$string['preference:saved'] = 'Preferência guardada.';
$string['privacy:metadata'] = 'O autograder guarda, por estudante, se e quando deve ser avaliado automaticamente, e um registo de cada tentativa de avaliação.';
$string['setting:default_days'] = 'Atraso predefinido (dias)';
$string['setting:default_grade'] = 'Nota predefinida';
$string['setting:default_grade_desc'] = 'Nota sugerida ao ativar autograder pela primeira vez numa atividade.';
$string['setting:default_hours'] = 'Atraso predefinido (horas)';
$string['setting:default_minutes'] = 'Atraso predefinido (minutos)';
$string['setting:default_time_desc'] = 'Quanto tempo esperar, após a data limite, antes de avaliar — repartido em dias, horas e minutos.';
$string['setting:fallback_grader'] = 'Avaliador de reserva';
$string['setting:fallback_grader_desc'] = 'Usado apenas quando nenhum professor do próprio curso é elegível para avaliar um estudante (ver a capacidade "gradeonbehalf"). Só são propostos utilizadores que plausivelmente poderiam avaliar algo.';
$string['setting:fallback_grader_ineligible'] = 'Esse utilizador não possui nenhuma capacidade de avaliação e não pode ser definido como avaliador de reserva.';
$string['setting:fallback_grader_none'] = 'Nenhum';
$string['setting:fallback_grader_placeholder'] = 'Pesquisar um utilizador…';
$string['setting:modules_disable'] = 'Desativar o autograder para {$a}';
$string['setting:modules_enable'] = 'Ativar o autograder para {$a}';
$string['setting:modules_enabled_column'] = 'Ativo';
$string['setting:modules_heading'] = 'Escolha quais tipos de atividade podem ter o autograder configurado na <a href="{$a->url}">página de atividades autoavaliáveis</a>.';
$string['setting:retentiondays'] = 'Retenção (dias)';
$string['setting:retentiondays_desc'] = 'Por quanto tempo uma decisão terminada e o seu registo de avaliação são mantidos antes de serem eliminados.';
$string['setting:tiebreak'] = 'Regra de desempate';
$string['setting:tiebreak_desc'] = 'Quando mais de um professor pode avaliar um estudante, qual deles é escolhido.';
$string['setting:tiebreak_last_course_access'] = 'O que acedeu ao curso mais recentemente';
$string['setting:tiebreak_lowest_userid'] = 'O de menor ID de utilizador';
$string['settings:generaltab'] = 'Geral';
$string['settings:modulestab'] = 'Atividades autoavaliáveis';
$string['settings:retentiontab'] = 'Retenção';
$string['task:cancel_module'] = 'Cancelar as avaliações automáticas pendentes numa atividade';
$string['task:catch_up_module'] = 'Pôr em dia os estudantes que já aguardam numa atividade';
$string['task:grade_student'] = 'Avaliar um estudante';
$string['task:purge_history'] = 'Eliminar as decisões e o registo de avaliação automática já concluídos';
$string['task:recalculate_module'] = 'Recalcular as datas de avaliação automática de uma atividade';
$string['task:reconcile_pending'] = 'Recolocar na fila as decisões de avaliação automática que perderam a sua tarefa';
