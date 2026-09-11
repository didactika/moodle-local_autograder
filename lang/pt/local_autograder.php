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
$string['advanced:edit_definition'] = 'Para alterar a rubrica ou o guião de avaliação em si, <a href="{$a}">edite a sua definição</a>.';
$string['advanced:heading'] = 'Níveis que o autograder marca';
$string['advanced:intro'] = 'Preencha isto tal como faria ao avaliar à mão um estudante de <strong>{$a}</strong>. O autograder assinala o que escolher aqui, e o Moodle calcula a nota a partir disso como sempre faz.';
$string['advanced:marks'] = 'O que o autograder assinala';
$string['advanced:not_advanced'] = 'Esta atividade não é avaliada por rubrica nem por guia de avaliação.';
$string['advanced:save'] = 'Guardar o que o autograder assinala';
$string['advanced:saved'] = 'Guardado o que o autograder vai marcar.';
$string['autograder:configure'] = 'Ativar ou desativar o autograder numa atividade';
$string['autograder:gradeonbehalf'] = 'Ser elegível para que o autograder atribua notas em seu nome';
$string['autograder:manage'] = 'Gerir as definições do autograder ao nível do site';
$string['autograder:viewreport'] = 'Ver o relatório do autograder de um curso';
$string['error:advancedgradingstale'] = 'A rubrica ou o guião de avaliação mudou desde que se indicou ao autograder o que assinalar. Abra as definições de autograder da atividade e volte a escolher os níveis.';
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
$string['form:error_advanced_unset'] = 'Não foi indicado ao autograder o que assinalar na rubrica ou guião de avaliação desta atividade. <a href="{$a}">Escolha isso primeiro</a> e depois ative-o.';
$string['form:error_grade_above_max'] = 'Esta atividade é avaliada em {$a}, por isso a nota não pode ser superior.';
$string['form:error_grade_negative'] = 'A nota não pode ser negativa.';
$string['form:error_grade_required'] = 'Indique a nota que o autograder deve atribuir.';
$string['form:error_hours_range'] = 'Indique de 0 a 23 horas. Para mais tempo use o campo dos dias.';
$string['form:error_minutes_range'] = 'Indique de 0 a 59 minutos. Para mais tempo use o campo das horas.';
$string['form:error_negative_time'] = 'Não pode ser negativo.';
$string['form:error_not_graded'] = 'O autograder precisa que esta atividade seja avaliada. Escolha um tipo de nota diferente de "Nenhuma".';
$string['form:error_numeric'] = 'Deve ser um número.';
$string['form:error_scale_mismatch'] = 'Esse item não pertence à escala que a atividade usa agora. Escolha um da escala que acabou de selecionar.';
$string['form:error_scale_unset'] = 'Escolha que item da escala o autograder deve atribuir.';
$string['form:error_whole_number'] = 'Indique um número inteiro.';
$string['form:grade'] = 'Nota a atribuir';
$string['form:grade_range'] = 'Esta atividade é avaliada em {$a}.';
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
$string['privacy:metadata:config'] = 'O que um professor configurou para o autograder fazer numa atividade.';
$string['privacy:metadata:config:cmid'] = 'A atividade a que a configuração pertence.';
$string['privacy:metadata:config:timemodified'] = 'Quando a configuração foi guardada pela última vez.';
$string['privacy:metadata:config:usermodified'] = 'O utilizador que guardou a configuração pela última vez.';
$string['privacy:metadata:decision'] = 'A decisão do autograder, pendente ou concluída, sobre um estudante numa atividade.';
$string['privacy:metadata:decision:baselineduedate'] = 'A data a partir da qual o atraso é contado.';
$string['privacy:metadata:decision:duedatereason'] = 'Porque foi essa a data usada: conclusão, entrega, data de fecho ou uma exceção.';
$string['privacy:metadata:decision:failurereason'] = 'Porque foi a decisão cancelada ou não pôde concretizar-se.';
$string['privacy:metadata:decision:gradedvalue'] = 'A nota lançada pelo autograder.';
$string['privacy:metadata:decision:graderid'] = 'O professor em nome de quem a nota foi lançada.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Quando a nota deve, ou devia, ser lançada.';
$string['privacy:metadata:decision:status'] = 'Se a decisão está pendente, avaliada, assumida à mão, cancelada ou falhada.';
$string['privacy:metadata:decision:timemodified'] = 'Quando a decisão mudou pela última vez.';
$string['privacy:metadata:decision:userid'] = 'O estudante a que a decisão diz respeito.';
$string['privacy:metadata:gradelog'] = 'O registo do que o autograder fez, não fez e porquê.';
$string['privacy:metadata:gradelog:graderid'] = 'O professor em nome de quem a nota foi lançada.';
$string['privacy:metadata:gradelog:gradevalue'] = 'A nota lançada, quando houve.';
$string['privacy:metadata:gradelog:message'] = 'O que aconteceu, por palavras.';
$string['privacy:metadata:gradelog:outcome'] = 'Se o estudante foi avaliado, ignorado, cancelado ou falhou.';
$string['privacy:metadata:gradelog:timecreated'] = 'Quando aconteceu.';
$string['privacy:metadata:gradelog:userid'] = 'O estudante a que a entrada diz respeito.';
$string['privacy:metadata:preference:optout'] = 'Se este utilizador pediu para não ser escolhido como o professor em nome de quem o autograder avalia.';
$string['privacy:path:config'] = 'Configuração do autograder';
$string['privacy:path:decision'] = 'Decisões do autograder';
$string['privacy:path:gradelog'] = 'Histórico do autograder';
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
