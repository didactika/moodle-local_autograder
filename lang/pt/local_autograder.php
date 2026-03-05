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
 * @copyright   2025 ADSDR <eduardo.cubias@ct.uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

    $string['pluginname'] = 'Local Autograder';

    $string['form:heading'] = 'Avaliação Automática';
    $string['form:enabled'] = 'Ativar avaliação automática para esta atividade';
    $string['form:grade'] = 'Nota automática a atribuir';
    $string['form:error_numeric'] = 'Insira apenas valor numérico inteiro';
    $string['form:error_max_grade'] = 'A nota automática não pode ser maior que a nota máxima';
    $string['form:days_to_complete'] = 'Dias de espera para avaliação automática';
    $string['form:hours_to_complete'] = 'Horas de espera para avaliação automática';
    $string['form:minutes_to_complete'] = 'Minutos de espera para avaliação automática';
    $string['form:time_to_complete'] = 'Tempo de espera antes da avaliação';
    $string['form:enabled_help'] = 'Ativa/desativa a avaliação automática para esta atividade. Se ativada, a nota automática será atribuída após o tempo configurado.';
    $string['form:grade_help'] = 'Nota numérica inteira que será atribuída automaticamente quando o tempo configurado terminar.';
    $string['form:time_to_complete_help'] = 'Define o período (dias ou horas ou minutos) após o qual a avaliação automática será aplicada.';
    $string['form:error_days_range'] = 'Os dias devem estar entre 0 e 100.';
    $string['form:error_hours_range'] = 'As horas devem estar entre 0 e 24.';
    $string['form:error_minutes_range'] = 'Os minutos devem estar entre 0 e 60.';
    $string['form:error_all_time_zero'] = 'Pelo menos um entre dias, horas ou minutos deve ser maior que 0.';
    $string['form:error_completion_tracking'] = 'A avaliação automática está ativada, configure o acompanhamento de conclusão';
    $string['form:error_type_grade'] = 'A avaliação automática está ativada, apenas o tipo pontuação é aceito';

    $string['settings:enable'] = 'Ativar plugin autograder';
    $string['settings:enableDescription'] = 'Valor padrão: Sim';

    $string['setting:days_to_completeTitle'] = 'Dias de espera para avaliação automática';
    $string['setting:days_to_completeHelper'] = 'Número de dias que o sistema deve esperar antes da avaliação automática.';

    $string['setting:hours_to_completeTitle'] = 'Horas de espera para avaliação automática';
    $string['setting:hours_to_completeHelper'] = 'Número de horas permitidas (além dos dias) que o sistema deve esperar antes da avaliação automática.';

    $string['setting:minutes_to_completeTitle'] = 'Minutos de espera para avaliação automática';
    $string['setting:minutes_to_completeHelper'] = 'Número de minutos permitidos (além de dias e horas) que o sistema deve esperar antes da avaliação automática.';

    $string['setting:default_gradeTitle'] = 'Nota automática a atribuir';
    $string['setting:default_gradeHelper'] = 'Nota padrão a atribuir se nenhuma nota específica for fornecida. Insira apenas valor numérico inteiro';

    $string['event:autograder_created'] = 'Autograder Criado';
    $string['event:autograder_updated'] = 'Autograder Atualizado';

    $string['form:error_completion_tracking'] = 'Para ativar o autograder, você deve ativar o rastreamento de conclusão na seção "Conclusão da atividade".';
    $string['form:error_type_grade'] = 'O autograder funciona apenas com atividades configuradas com avaliação por "Pontuação" (não escalas nem sem avaliação).';

