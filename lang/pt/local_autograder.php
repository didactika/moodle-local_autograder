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
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Defina primeiro a rubrica ou o guia de avaliação desta atividade e depois volte para indicar que níveis o Autograder deve assinalar.';
$string['advanced:edit_definition'] = 'Para alterar a própria rubrica ou o guia de avaliação, <a href="{$a}">edite a sua definição</a>.';
$string['advanced:heading'] = 'Níveis assinalados pelo Autograder';
$string['advanced:intro'] = 'Preencha-o exatamente como faria ao classificar manualmente um estudante de <strong>{$a}</strong>. O Autograder assinala o que escolher aqui e o Moodle calcula a classificação a partir disso da mesma forma que sempre.';
$string['advanced:marks'] = 'O que o Autograder assinala';
$string['advanced:not_advanced'] = 'Esta atividade não é classificada com uma rubrica nem com um guia de avaliação.';
$string['advanced:save'] = 'Guardar o que o Autograder assinala';
$string['advanced:saved'] = 'Foi guardado o que o Autograder assinalará.';
$string['autograder:configure'] = 'Ativar ou desativar o Autograder numa atividade';
$string['autograder:manage'] = 'Gerir as definições do Autograder de todo o site';
$string['error:advancedgradingstale'] = 'A rubrica ou o guia de avaliação foi alterado desde que o Autograder foi informado do que assinalar nele. Abra as definições do Autograder da atividade e escolha de novo os níveis.';
$string['error:gradewritefailed'] = 'O Moodle recusou a classificação que o Autograder tentou publicar.';
$string['error:nogradeitem'] = 'Esta atividade não tem um item de classificação onde escrever.';
$string['event:config_created'] = 'Configuração do Autograder criada';
$string['event:config_deleted'] = 'Configuração do Autograder eliminada';
$string['event:config_updated'] = 'Configuração do Autograder atualizada';
$string['event:decision_cancelled'] = 'Decisão do Autograder cancelada';
$string['event:grading_failed'] = 'Classificação do Autograder falhada';
$string['event:student_graded'] = 'Estudante classificado pelo Autograder';
$string['form:advanced_define_first'] = 'Esta atividade é classificada com uma rubrica ou um guia de avaliação, mas ainda não há nenhum definido. <a href="{$a}">Defina-o primeiro</a> e depois escolha o que o Autograder assinala.';
$string['form:advanced_set'] = 'O Autograder sabe o que assinalar nesta rubrica ou guia. <a href="{$a}">Altere-o</a>.';
$string['form:advanced_undefined'] = 'Esta atividade é classificada com uma rubrica ou um guia de avaliação que o Autograder não consegue ler.';
$string['form:advanced_unset'] = 'Escolha <a href="{$a}">o que o Autograder assinala</a> nesta rubrica ou guia; até lá, não tem com que classificar.';
$string['form:days_to_complete'] = 'Dias';
$string['form:enabled'] = 'Ativar o Autograder';
$string['form:enabled_help'] = 'Quando ativado, um estudante que conclui esta atividade é classificado automaticamente, passado o tempo configurado desde o prazo, com a classificação definida abaixo, a menos que alguém o classifique antes manualmente.';
$string['form:error_advanced_unset'] = 'O Autograder não foi informado do que assinalar na rubrica ou no guia de avaliação desta atividade. <a href="{$a}">Escolha-o primeiro</a> e depois ative o Autograder.';
$string['form:error_grade_above_max'] = 'Esta atividade é classificada em {$a}, pelo que a classificação não pode ser superior.';
$string['form:error_grade_negative'] = 'A classificação não pode ser negativa.';
$string['form:error_grade_required'] = 'Introduza a classificação que o Autograder deve atribuir.';
$string['form:error_hours_range'] = 'Introduza de 0 a 23 horas. Use o campo dos dias para qualquer duração superior.';
$string['form:error_minutes_range'] = 'Introduza de 0 a 59 minutos. Use o campo das horas para qualquer duração superior.';
$string['form:error_negative_time'] = 'Não pode ser negativo.';
$string['form:error_not_graded'] = 'O Autograder precisa de que esta atividade seja classificada. Escolha um tipo de classificação diferente de «Nenhuma».';
$string['form:error_numeric'] = 'Tem de ser um número.';
$string['form:error_scale_mismatch'] = 'Esse item não pertence à escala que esta atividade usa agora. Escolha um da escala que acabou de selecionar.';
$string['form:error_scale_unset'] = 'Escolha que item da escala o Autograder deve atribuir.';
$string['form:error_whole_number'] = 'Introduza um número inteiro.';
$string['form:grade'] = 'Classificação a atribuir';
$string['form:grade_range'] = 'Esta atividade é classificada em {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Horas';
$string['form:minutes_to_complete'] = 'Minutos';
$string['form:needs_completion'] = 'O Autograder só consegue saber que um estudante realizou este tipo de atividade através da conclusão da atividade. Defina uma condição que o estudante possa cumprir por si próprio: marcá-la como concluída, visualizá-la ou uma das condições da própria atividade. Não inclua «Receber uma classificação» nem «Receber uma classificação de aprovação»: enquanto a classificação não existir, a atividade nunca conta como concluída, pelo que nada indicaria ao Autograder que o estudante terminou, e a classificação esperada seria a que o Autograder daria.';
$string['form:notenabled_advanced'] = 'O Autograder foi deixado desativado: não foi informado do que assinalar na rubrica ou no guia de avaliação desta atividade. <a href="{$a}">Escolha-o primeiro</a> e depois ative-o.';
$string['form:notenabled_nocompletion'] = 'O Autograder foi deixado desativado: só consegue saber que um estudante realizou este tipo de atividade através da conclusão da atividade, e a conclusão desta atividade não tem nenhuma condição que o estudante possa cumprir por si próprio ou exige também uma classificação.';
$string['form:quiz_note'] = 'Num questionário, o Autograder só intervém enquanto uma pergunta, como um ensaio, aguarda correção manual, e então classifica todo o questionário. Quando o docente a corrige, a classificação própria do questionário ocupa o seu lugar. Um questionário que se corrige sozinho mantém a sua própria classificação.';
$string['form:time_to_complete'] = 'Tempo de espera antes de classificar';
$string['form:time_to_complete_help'] = 'Quanto tempo esperar, depois do prazo, antes de classificar.';
$string['messageprovider:failuredigest'] = 'Estudantes que o Autograder não conseguiu classificar';
$string['modules:count'] = '{$a} tipos de atividade';
$string['modules:empty'] = 'Nenhum tipo de atividade corresponde.';
$string['modules:filter_all'] = 'Todos';
$string['modules:filter_off'] = 'Desativados';
$string['modules:filter_on'] = 'Ativados';
$string['modules:intro'] = 'Desativar um tipo oculta as definições do Autograder das suas atividades e cancela a classificação que ainda estava pendente para elas. Cada atividade mantém a sua própria definição do Autograder, pelo que ativar de novo o tipo as retoma, exceto onde já foi registada uma classificação.';
$string['modules:lead'] = 'Os tipos de atividade que recebem o trabalho dos estudantes e têm uma classificação atribuída por um docente';
$string['modules:name'] = 'Nome';
$string['modules:reads'] = 'O que o Autograder lê';
$string['modules:reads_assign'] = 'Lê as entregas dos estudantes';
$string['modules:reads_completion'] = 'Acompanha a conclusão da atividade';
$string['modules:reads_forum'] = 'Lê as mensagens dos estudantes';
$string['modules:reads_quiz'] = 'Lê as tentativas dos estudantes';
$string['modules:search'] = 'Pesquisar tipos de atividade';
$string['modules:state'] = 'Autograder';
$string['notify:footer'] = 'Recebe esta mensagem porque pode configurar o Autograder nestas atividades.';
$string['notify:intro'] = 'Desde o último resumo, o Autograder não conseguiu classificar alguns estudantes nas atividades abaixo. Esses estudantes não foram classificados.';
$string['notify:more'] = 'Atividades não listadas aqui: {$a}.';
$string['notify:reason_grade_write_failed'] = 'O Moodle recusou a classificação. As causas habituais são uma rubrica ou um guia de avaliação alterado desde que o Autograder foi informado do que assinalar nele, ou um docente que não tem permissão para classificar a atividade.';
$string['notify:reason_line'] = 'Estudantes afetados: {$a->count}. {$a->reason}';
$string['notify:reason_no_grader'] = 'Não foi encontrado nenhum docente em nome do qual registar a classificação e não há um classificador de reserva para usar em seu lugar.';
$string['notify:reason_other'] = 'Não foi possível registar a classificação.';
$string['notify:small'] = 'O Autograder não conseguiu classificar alguns estudantes. Atividades afetadas: {$a}.';
$string['notify:subject'] = 'O Autograder não conseguiu classificar alguns estudantes';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Preferências do Autograder';
$string['preference:notoffered'] = 'Este site não permite que os docentes se excluam do Autograder.';
$string['preference:optout'] = 'Nunca registar classificações automáticas em meu nome';
$string['preference:optout_help'] = 'O Autograder nunca registará classificações em seu nome, nem mesmo nos cursos em que leciona.';
$string['preference:saved'] = 'Preferência guardada.';
$string['privacy:metadata:config'] = 'O que um docente configurou para o Autograder fazer numa atividade.';
$string['privacy:metadata:config:cmid'] = 'A atividade a que pertence a configuração.';
$string['privacy:metadata:config:timemodified'] = 'Quando a configuração foi guardada pela última vez.';
$string['privacy:metadata:config:usermodified'] = 'O utilizador que guardou a configuração em último lugar.';
$string['privacy:metadata:core_message'] = 'Pode ser enviado aos docentes um resumo dos estudantes que o Autograder não conseguiu classificar, através do sistema de mensagens.';
$string['privacy:metadata:decision'] = 'A decisão pendente ou resolvida do Autograder sobre um estudante numa atividade.';
$string['privacy:metadata:decision:baselineduedate'] = 'A data a partir da qual a espera é contada.';
$string['privacy:metadata:decision:duedatereason'] = 'Porque foi usada essa data: a conclusão, a entrega, a data de fecho ou uma exceção.';
$string['privacy:metadata:decision:failurereason'] = 'Porque a decisão foi cancelada ou não pôde ser executada.';
$string['privacy:metadata:decision:gradedvalue'] = 'A classificação que o Autograder publicou.';
$string['privacy:metadata:decision:graderid'] = 'O docente em nome do qual a classificação foi publicada.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Quando a classificação deve ser, ou devia ter sido, publicada.';
$string['privacy:metadata:decision:status'] = 'Se a decisão está em espera, classificada, assumida manualmente, cancelada ou falhada.';
$string['privacy:metadata:decision:timemodified'] = 'Quando a decisão mudou pela última vez.';
$string['privacy:metadata:decision:userid'] = 'O estudante a que a decisão diz respeito.';
$string['privacy:metadata:gradelog'] = 'Um registo do que o Autograder fez e não fez, e porquê.';
$string['privacy:metadata:gradelog:graderid'] = 'O docente em nome do qual a classificação foi publicada.';
$string['privacy:metadata:gradelog:gradevalue'] = 'A classificação publicada, quando houve.';
$string['privacy:metadata:gradelog:message'] = 'O que aconteceu, por palavras.';
$string['privacy:metadata:gradelog:outcome'] = 'Se o estudante foi classificado, ignorado, cancelado ou falhou, ou se a classificação do Autograder cedeu perante a própria da atividade.';
$string['privacy:metadata:gradelog:timecreated'] = 'Quando aconteceu.';
$string['privacy:metadata:gradelog:userid'] = 'O estudante a que a entrada diz respeito.';
$string['privacy:metadata:preference:optout'] = 'Se este utilizador pediu para não ser escolhido como o docente em nome do qual o Autograder classifica.';
$string['privacy:path:config'] = 'Configuração do Autograder';
$string['privacy:path:decision'] = 'Decisões do Autograder';
$string['privacy:path:gradelog'] = 'Histórico do Autograder';
$string['setting:allowoptout'] = 'Permitir que os docentes se excluam';
$string['setting:allowoptout_desc'] = 'Adiciona uma página de preferências onde os utilizadores podem pedir que o Autograder nunca registe classificações em seu nome. Em sites com poucos docentes, as exclusões podem deixar os estudantes sem ninguém em nome de quem classificar. Quando desativado, a página fica oculta e as exclusões existentes são ignoradas.';
$string['setting:fallback_grader'] = 'Classificador de reserva';
$string['setting:fallback_grader_desc'] = 'As classificações são registadas em nome deste utilizador quando não é possível escolher um docente segundo as regras automáticas, ou quando a classificação em nome do docente escolhido falha. Só são listados os utilizadores com um papel que classifica; os administradores do site nunca são usados. Ao contrário dos docentes, este utilizador não é verificado em cada atividade, pelo que deve escolher alguém autorizado a classificar em todos os locais onde o Autograder é usado. Se for Nenhum, esses estudantes não são classificados e a falha fica registada.';
$string['setting:fallback_grader_ineligible'] = 'Este utilizador não pode ser o classificador de reserva. Os administradores do site e os utilizadores sem um papel que possa classificar nunca são usados.';
$string['setting:fallback_grader_none'] = 'Nenhum';
$string['setting:fallback_grader_placeholder'] = 'Pesquisar um utilizador…';
$string['setting:grading_heading'] = 'Classificação e histórico';
$string['setting:modules_disable'] = 'Desativar o Autograder para {$a}';
$string['setting:modules_disabled'] = 'Autograder desativado para {$a}.';
$string['setting:modules_enable'] = 'Ativar o Autograder para {$a}';
$string['setting:modules_enabled'] = 'Autograder ativado para {$a}.';
$string['setting:modules_enabled_column'] = 'Ativado';
$string['setting:modules_heading'] = 'Escolha que tipos de atividade podem usar o Autograder na página <a href="{$a->url}">Tipos de atividade</a>.';
$string['setting:notifyfailures'] = 'Avisar os docentes das falhas de classificação';
$string['setting:notifyfailures_desc'] = 'Envia a todas as pessoas inscritas no curso que podem configurar o Autograder numa atividade um resumo dos estudantes que não conseguiu classificar nela, agrupado por atividade. Enviado semanalmente por predefinição; o horário pode ser alterado em Tarefas agendadas. Cada falha é comunicada uma só vez, a partir do momento em que esta opção é ativada.';
$string['setting:notifystudent'] = 'Notificar os estudantes';
$string['setting:notifystudent_desc'] = 'Envia ao estudante a notificação de classificação do Moodle quando o Autograder classifica um trabalho ou um fórum, como se um docente tivesse escolhido notificar ao classificar. Só são afetados os trabalhos e os fóruns.';
$string['setting:retentiondays'] = 'Manter o histórico durante (dias)';
$string['setting:retentiondays_desc'] = 'As decisões terminadas (classificadas, canceladas, falhadas…) e as entradas do registo de classificações mais antigas do que este valor são eliminadas diariamente por uma tarefa agendada. As decisões pendentes nunca são eliminadas. Introduza 0 para manter tudo.';
$string['setting:teacher_roles'] = 'Papéis de docente selecionados';
$string['setting:teacher_roles_desc'] = 'Os papéis que contam como docentes. Os utilizadores cujos papéis não estejam selecionados aqui nunca são escolhidos. Os papéis selecionados devem poder classificar as atividades em que o Autograder é usado.';
$string['setting:teacher_source_mode'] = 'Papéis de docente';
$string['setting:teacher_source_mode_chosen'] = 'Apenas os papéis selecionados';
$string['setting:teacher_source_mode_desc'] = 'O modo automático inclui todos os papéis com moodle/grade:edit ou com uma capacidade de classificação de atividade (como mod/assign:grade), pelo que os novos papéis personalizados funcionam sem configuração adicional.';
$string['setting:teacher_source_mode_grading'] = 'Automático: qualquer papel que possa classificar';
$string['setting:teachers_heading'] = 'As classificações são registadas em nome de um dos docentes do estudante: utilizadores com um papel de docente atribuído no próprio curso, e não na sua categoria nem em todo o site, cuja inscrição no curso está ativa. Os administradores do site e os utilizadores suspensos nunca são usados, nem um docente que não possa classificar a atividade ou que não possa ver o grupo do estudante numa atividade com grupos separados. Prefere-se o docente que partilha mais grupos com o estudante; se nenhum partilhar um grupo, um docente que não esteja em nenhum grupo; e se todos os docentes estiverem noutro grupo, qualquer um deles. Nos cursos com grupos separados e um agrupamento predefinido, só contam os grupos desse agrupamento.';
$string['setting:tiebreak'] = 'Seleção do docente';
$string['setting:tiebreak_desc'] = 'Qual é escolhido quando mais de um docente pode classificar um estudante.';
$string['setting:tiebreak_last_course_access'] = 'Acesso mais recente ao curso';
$string['setting:tiebreak_lowest_userid'] = 'ID de utilizador mais baixo';
$string['settings:generaltab'] = 'Geral';
$string['settings:modulestab'] = 'Tipos de atividade';
$string['settings:teacherstab'] = 'Docentes';
$string['task:cancel_module'] = 'Cancelar a classificação do Autograder pendente numa atividade';
$string['task:catch_up_module'] = 'Pôr em dia os estudantes que já esperam numa atividade';
$string['task:grade_student'] = 'Classificar um estudante';
$string['task:notify_failures'] = 'Avisar os docentes dos estudantes que o Autograder não conseguiu classificar';
$string['task:purge_history'] = 'Depurar as decisões terminadas do Autograder e o registo de classificações';
$string['task:recalculate_module'] = 'Recalcular os prazos do Autograder de uma atividade';
$string['task:reconcile_pending'] = 'Voltar a pôr em fila as decisões do Autograder que perderam a sua tarefa';
