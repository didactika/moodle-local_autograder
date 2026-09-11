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

$string['autograder:configure'] = 'Ativar ou desativar o autograder numa atividade';
$string['autograder:gradeonbehalf'] = 'Ser elegível para que o autograder atribua notas em seu nome';
$string['autograder:manage'] = 'Gerir as definições do autograder ao nível do site';
$string['autograder:viewreport'] = 'Ver o relatório do autograder de um curso';
$string['error:gradewritefailed'] = 'O Moodle recusou a nota que o autograder tentou atribuir.';
$string['error:nogradeitem'] = 'Esta atividade não tem item de avaliação onde escrever.';
$string['event:config_created'] = 'Configuração do autograder criada';
$string['event:config_deleted'] = 'Configuração do autograder eliminada';
$string['event:config_updated'] = 'Configuração do autograder atualizada';
$string['form:days_to_complete'] = 'Dias';
$string['form:enabled'] = 'Ativar autograder';
$string['form:enabled_help'] = 'Se ativado, um estudante que conclua esta atividade é avaliado automaticamente, o tempo configurado após o prazo, com a nota indicada abaixo — a menos que alguém o avalie manualmente antes.';
$string['form:error_completion_tracking'] = 'O autograder requer que o acompanhamento de conclusão esteja ativado nesta atividade.';
$string['form:error_negative_time'] = 'Não pode ser negativo.';
$string['form:error_numeric'] = 'Deve ser um número.';
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
