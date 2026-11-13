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
 * Italian language strings.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Definisci prima la rubrica o la guida di valutazione di questa attività, poi torna a indicare quali livelli Autograder deve selezionare.';
$string['advanced:edit_definition'] = 'Per modificare la rubrica o la guida di valutazione stessa, <a href="{$a}">modifica la sua definizione</a>.';
$string['advanced:heading'] = 'Livelli selezionati da Autograder';
$string['advanced:intro'] = 'Compilalo esattamente come faresti valutando a mano uno studente di <strong>{$a}</strong>. Autograder seleziona ciò che scegli qui e Moodle ne calcola il voto come fa sempre.';
$string['advanced:marks'] = 'Ciò che Autograder seleziona';
$string['advanced:not_advanced'] = 'Questa attività non è valutata con una rubrica né con una guida di valutazione.';
$string['advanced:save'] = 'Salva ciò che Autograder seleziona';
$string['advanced:saved'] = 'Ciò che Autograder selezionerà è stato salvato.';
$string['autograder:configure'] = 'Attivare o disattivare Autograder per un’attività';
$string['autograder:manage'] = 'Gestire le impostazioni di Autograder per l’intero sito';
$string['error:advancedgradingstale'] = 'La rubrica o la guida di valutazione è cambiata da quando ad Autograder è stato indicato cosa selezionare. Apri le impostazioni di Autograder dell’attività e scegli di nuovo i livelli.';
$string['error:gradewritefailed'] = 'Moodle ha rifiutato il voto che Autograder ha tentato di pubblicare.';
$string['error:nogradeitem'] = 'Questa attività non ha un elemento di valutazione in cui scrivere.';
$string['event:config_created'] = 'Configurazione di Autograder creata';
$string['event:config_deleted'] = 'Configurazione di Autograder eliminata';
$string['event:config_updated'] = 'Configurazione di Autograder aggiornata';
$string['event:decision_cancelled'] = 'Decisione di Autograder annullata';
$string['event:grading_failed'] = 'Valutazione di Autograder non riuscita';
$string['event:student_graded'] = 'Studente valutato da Autograder';
$string['form:advanced_define_first'] = 'Questa attività è valutata con una rubrica o una guida di valutazione, ma non ne è ancora definita nessuna. <a href="{$a}">Definiscila prima</a>, poi scegli cosa seleziona Autograder.';
$string['form:advanced_set'] = 'Autograder sa cosa selezionare in questa rubrica o guida. <a href="{$a}">Modificalo</a>.';
$string['form:advanced_undefined'] = 'Questa attività è valutata con una rubrica o una guida di valutazione che Autograder non riesce a leggere.';
$string['form:advanced_unset'] = 'Scegli <a href="{$a}">cosa seleziona Autograder</a> in questa rubrica o guida: fino ad allora non ha nulla con cui valutare.';
$string['form:days_to_complete'] = 'Giorni';
$string['form:enabled'] = 'Attiva Autograder';
$string['form:enabled_help'] = 'Quando è attivo, uno studente che completa questa attività viene valutato automaticamente, dopo il tempo configurato dalla scadenza, con il voto indicato qui sotto, a meno che qualcuno lo valuti prima a mano.';
$string['form:error_advanced_unset'] = 'Ad Autograder non è stato indicato cosa selezionare nella rubrica o nella guida di valutazione di questa attività. <a href="{$a}">Sceglilo prima</a>, poi attiva Autograder.';
$string['form:error_grade_above_max'] = 'Questa attività è valutata su {$a}, quindi il voto non può essere superiore.';
$string['form:error_grade_negative'] = 'Il voto non può essere negativo.';
$string['form:error_grade_required'] = 'Inserisci il voto che Autograder deve assegnare.';
$string['form:error_hours_range'] = 'Inserisci da 0 a 23 ore. Usa il campo dei giorni per qualsiasi durata maggiore.';
$string['form:error_minutes_range'] = 'Inserisci da 0 a 59 minuti. Usa il campo delle ore per qualsiasi durata maggiore.';
$string['form:error_negative_time'] = 'Non può essere negativo.';
$string['form:error_not_graded'] = 'Autograder richiede che questa attività sia valutata. Scegli un tipo di voto diverso da «Nessuno».';
$string['form:error_numeric'] = 'Deve essere un numero.';
$string['form:error_scale_mismatch'] = 'Quell’elemento non appartiene alla scala che questa attività usa ora. Scegline uno dalla scala che hai appena selezionato.';
$string['form:error_scale_unset'] = 'Scegli quale elemento della scala Autograder deve assegnare.';
$string['form:error_whole_number'] = 'Inserisci un numero intero.';
$string['form:grade'] = 'Voto da assegnare';
$string['form:grade_range'] = 'Questa attività è valutata su {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Ore';
$string['form:minutes_to_complete'] = 'Minuti';
$string['form:needs_completion'] = 'Autograder può sapere che uno studente ha svolto questo tipo di attività solo tramite il completamento dell’attività. Imposta una condizione che lo studente possa soddisfare da solo: segnarla come completata, visualizzarla o una delle condizioni proprie dell’attività. Non includere «Ricevere un voto» né «Ricevere un voto sufficiente»: finché il voto non c’è, l’attività non risulta mai completata, quindi nulla indicherebbe ad Autograder che lo studente ha finito, e il voto atteso sarebbe quello che darebbe Autograder.';
$string['form:notenabled_advanced'] = 'Autograder è stato lasciato disattivato: non gli è stato indicato cosa selezionare nella rubrica o nella guida di valutazione di questa attività. <a href="{$a}">Sceglilo prima</a>, poi attivalo.';
$string['form:notenabled_nocompletion'] = 'Autograder è stato lasciato disattivato: può sapere che uno studente ha svolto questo tipo di attività solo tramite il completamento dell’attività, e il completamento di questa attività non ha alcuna condizione che lo studente possa soddisfare da solo, oppure richiede anche un voto.';
$string['form:quiz_note'] = 'In un test, Autograder interviene solo mentre una domanda, come un saggio, attende di essere corretta a mano, e allora valuta l’intero test. Quando il docente la corregge, il voto proprio del test prende il suo posto. Un test che si corregge da solo mantiene il proprio voto.';
$string['form:time_to_complete'] = 'Tempo di attesa prima della valutazione';
$string['form:time_to_complete_help'] = 'Quanto attendere, dopo la scadenza, prima di valutare.';
$string['messageprovider:failuredigest'] = 'Studenti che Autograder non ha potuto valutare';
$string['modules:count'] = '{$a} tipi di attività';
$string['modules:empty'] = 'Nessun tipo di attività corrisponde.';
$string['modules:filter_all'] = 'Tutti';
$string['modules:filter_off'] = 'Disattivati';
$string['modules:filter_on'] = 'Attivati';
$string['modules:intro'] = 'Disattivare un tipo nasconde le impostazioni di Autograder delle sue attività e annulla la valutazione ancora in sospeso per esse. Ogni attività mantiene la propria impostazione di Autograder, quindi riattivare il tipo le riprende, tranne dove un voto è già stato registrato.';
$string['modules:lead'] = 'I tipi di attività che ricevono il lavoro degli studenti e hanno un voto assegnato da un docente';
$string['modules:name'] = 'Nome';
$string['modules:reads'] = 'Cosa legge Autograder';
$string['modules:reads_assign'] = 'Legge le consegne degli studenti';
$string['modules:reads_completion'] = 'Segue il completamento dell’attività';
$string['modules:reads_forum'] = 'Legge i messaggi degli studenti';
$string['modules:reads_quiz'] = 'Legge i tentativi degli studenti';
$string['modules:search'] = 'Cerca tipi di attività';
$string['modules:state'] = 'Autograder';
$string['notify:footer'] = 'Ricevi questo messaggio perché puoi configurare Autograder in queste attività.';
$string['notify:intro'] = 'Dall’ultimo riepilogo, Autograder non ha potuto valutare alcuni studenti nelle attività qui sotto. Questi studenti non sono stati valutati.';
$string['notify:more'] = 'Attività non elencate qui: {$a}.';
$string['notify:reason_grade_write_failed'] = 'Moodle ha rifiutato il voto. Le cause abituali sono una rubrica o una guida di valutazione cambiata da quando ad Autograder è stato indicato cosa selezionare, oppure un docente che non ha il permesso di valutare l’attività.';
$string['notify:reason_line'] = 'Studenti interessati: {$a->count}. {$a->reason}';
$string['notify:reason_no_grader'] = 'Non è stato trovato nessun docente a nome del quale registrare il voto e non c’è un valutatore di riserva da usare al suo posto.';
$string['notify:reason_other'] = 'Non è stato possibile registrare il voto.';
$string['notify:small'] = 'Autograder non ha potuto valutare alcuni studenti. Attività interessate: {$a}.';
$string['notify:subject'] = 'Autograder non ha potuto valutare alcuni studenti';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Preferenze di Autograder';
$string['preference:notoffered'] = 'Questo sito non permette ai docenti di escludersi da Autograder.';
$string['preference:optout'] = 'Non registrare mai voti automatici a mio nome';
$string['preference:optout_help'] = 'Autograder non registrerà mai voti a tuo nome, nemmeno nei corsi in cui insegni.';
$string['preference:saved'] = 'Preferenza salvata.';
$string['privacy:metadata:config'] = 'Ciò che un docente ha configurato che Autograder faccia in un’attività.';
$string['privacy:metadata:config:cmid'] = 'L’attività a cui appartiene la configurazione.';
$string['privacy:metadata:config:timemodified'] = 'Quando la configurazione è stata salvata l’ultima volta.';
$string['privacy:metadata:config:usermodified'] = 'L’utente che ha salvato per ultimo la configurazione.';
$string['privacy:metadata:core_message'] = 'Ai docenti può essere inviato un riepilogo degli studenti che Autograder non ha potuto valutare, tramite il sistema di messaggistica.';
$string['privacy:metadata:decision'] = 'La decisione in sospeso o conclusa di Autograder riguardo a uno studente in un’attività.';
$string['privacy:metadata:decision:baselineduedate'] = 'La data da cui si conta l’attesa.';
$string['privacy:metadata:decision:duedatereason'] = 'Perché è stata usata quella data: il completamento, la consegna, la data di chiusura o un’eccezione.';
$string['privacy:metadata:decision:failurereason'] = 'Perché la decisione è stata annullata o non ha potuto essere eseguita.';
$string['privacy:metadata:decision:gradedvalue'] = 'Il voto che Autograder ha pubblicato.';
$string['privacy:metadata:decision:graderid'] = 'Il docente a nome del quale è stato pubblicato il voto.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Quando il voto deve essere, o doveva essere, pubblicato.';
$string['privacy:metadata:decision:status'] = 'Se la decisione è in attesa, valutata, assunta a mano, annullata o non riuscita.';
$string['privacy:metadata:decision:timemodified'] = 'Quando la decisione è cambiata per l’ultima volta.';
$string['privacy:metadata:decision:userid'] = 'Lo studente a cui si riferisce la decisione.';
$string['privacy:metadata:gradelog'] = 'Un registro di ciò che Autograder ha fatto e non ha fatto, e perché.';
$string['privacy:metadata:gradelog:graderid'] = 'Il docente a nome del quale è stato pubblicato il voto.';
$string['privacy:metadata:gradelog:gradevalue'] = 'Il voto pubblicato, dove c’è stato.';
$string['privacy:metadata:gradelog:message'] = 'Cosa è successo, a parole.';
$string['privacy:metadata:gradelog:outcome'] = 'Se lo studente è stato valutato, saltato, annullato o non riuscito, oppure se il voto di Autograder ha ceduto a quello proprio dell’attività.';
$string['privacy:metadata:gradelog:timecreated'] = 'Quando è successo.';
$string['privacy:metadata:gradelog:userid'] = 'Lo studente a cui si riferisce la voce.';
$string['privacy:metadata:preference:optout'] = 'Se questo utente ha chiesto di non essere scelto come il docente a nome del quale valuta Autograder.';
$string['privacy:path:config'] = 'Configurazione di Autograder';
$string['privacy:path:decision'] = 'Decisioni di Autograder';
$string['privacy:path:gradelog'] = 'Cronologia di Autograder';
$string['setting:allowoptout'] = 'Consenti ai docenti di escludersi';
$string['setting:allowoptout_desc'] = 'Aggiunge una pagina di preferenze in cui gli utenti possono chiedere che Autograder non registri mai voti a loro nome. Nei siti con pochi docenti, le esclusioni possono lasciare gli studenti senza nessuno a nome del quale valutare. Quando è disattivato, la pagina è nascosta e le esclusioni esistenti vengono ignorate.';
$string['setting:fallback_grader'] = 'Valutatore di riserva';
$string['setting:fallback_grader_desc'] = 'I voti sono registrati a nome di questo utente quando non è possibile scegliere un docente in base alle regole automatiche, o quando la valutazione a nome del docente scelto non riesce. Sono elencati solo gli utenti con un ruolo che valuta; gli amministratori del sito non vengono mai usati. A differenza dei docenti, questo utente non viene verificato per ogni attività, quindi scegli qualcuno autorizzato a valutare ovunque si usi Autograder. Se Nessuno, quegli studenti non vengono valutati e l’errore viene registrato.';
$string['setting:fallback_grader_ineligible'] = 'Questo utente non può essere il valutatore di riserva. Gli amministratori del sito e gli utenti senza un ruolo che possa valutare non vengono mai usati.';
$string['setting:fallback_grader_none'] = 'Nessuno';
$string['setting:fallback_grader_placeholder'] = 'Cerca un utente…';
$string['setting:grading_heading'] = 'Valutazione e cronologia';
$string['setting:modules_disable'] = 'Disattiva Autograder per {$a}';
$string['setting:modules_disabled'] = 'Autograder disattivato per {$a}.';
$string['setting:modules_enable'] = 'Attiva Autograder per {$a}';
$string['setting:modules_enabled'] = 'Autograder attivato per {$a}.';
$string['setting:modules_enabled_column'] = 'Attivato';
$string['setting:modules_heading'] = 'Scegli quali tipi di attività possono usare Autograder nella pagina <a href="{$a->url}">Tipi di attività</a>.';
$string['setting:notifyfailures'] = 'Avvisa i docenti degli errori di valutazione';
$string['setting:notifyfailures_desc'] = 'Invia a tutte le persone iscritte al corso che possono configurare Autograder in un’attività un riepilogo degli studenti che non ha potuto valutare, raggruppati per attività. Inviato ogni settimana per impostazione predefinita; la pianificazione può essere modificata in Attività pianificate. Ogni errore viene segnalato una sola volta, a partire da quando questa opzione viene attivata.';
$string['setting:notifystudent'] = 'Notifica gli studenti';
$string['setting:notifystudent_desc'] = 'Invia allo studente la notifica di valutazione di Moodle quando Autograder valuta un compito o un forum, come se un docente avesse scelto di notificare durante la valutazione. Sono interessati solo i compiti e i forum.';
$string['setting:retentiondays'] = 'Conserva la cronologia per (giorni)';
$string['setting:retentiondays_desc'] = 'Le decisioni concluse (valutate, annullate, non riuscite…) e le voci del registro di valutazione più vecchie di questo valore vengono eliminate ogni giorno da un’attività pianificata. Le decisioni in sospeso non vengono mai eliminate. Inserisci 0 per conservare tutto.';
$string['setting:teacher_roles'] = 'Ruoli di docente selezionati';
$string['setting:teacher_roles_desc'] = 'I ruoli che contano come docenti. Gli utenti i cui ruoli non sono selezionati qui non vengono mai scelti. I ruoli selezionati devono poter valutare le attività in cui si usa Autograder.';
$string['setting:teacher_source_mode'] = 'Ruoli di docente';
$string['setting:teacher_source_mode_chosen'] = 'Solo i ruoli selezionati';
$string['setting:teacher_source_mode_desc'] = 'La modalità automatica include ogni ruolo con moodle/grade:edit o con una capacità di valutazione dell’attività (come mod/assign:grade), così i nuovi ruoli personalizzati funzionano senza ulteriore configurazione.';
$string['setting:teacher_source_mode_grading'] = 'Automatica: qualsiasi ruolo che possa valutare';
$string['setting:teachers_heading'] = 'I voti sono registrati a nome di uno dei docenti dello studente: gli utenti con un ruolo di docente assegnato nel corso stesso, non nella sua categoria né in tutto il sito, la cui iscrizione al corso è attiva. Gli amministratori del sito e gli utenti sospesi non vengono mai usati, né un docente che non può valutare l’attività o che non può vedere il gruppo dello studente in un’attività con gruppi separati. Si preferisce il docente che condivide più gruppi con lo studente; se nessuno condivide un gruppo, un docente che non è in nessun gruppo; e se tutti i docenti sono in qualche altro gruppo, uno qualsiasi di loro. Nei corsi con gruppi separati e un raggruppamento predefinito, contano solo i gruppi di quel raggruppamento.';
$string['setting:tiebreak'] = 'Selezione del docente';
$string['setting:tiebreak_desc'] = 'Quale viene scelto quando più di un docente può valutare uno studente.';
$string['setting:tiebreak_last_course_access'] = 'Accesso al corso più recente';
$string['setting:tiebreak_lowest_userid'] = 'ID utente più basso';
$string['settings:generaltab'] = 'Generale';
$string['settings:modulestab'] = 'Tipi di attività';
$string['settings:teacherstab'] = 'Docenti';
$string['task:cancel_module'] = 'Annulla la valutazione di Autograder in sospeso in un’attività';
$string['task:catch_up_module'] = 'Recupera gli studenti già in attesa in un’attività';
$string['task:grade_student'] = 'Valuta uno studente';
$string['task:notify_failures'] = 'Avvisa i docenti degli studenti che Autograder non ha potuto valutare';
$string['task:purge_history'] = 'Elimina le decisioni concluse di Autograder e il registro di valutazione';
$string['task:recalculate_module'] = 'Ricalcola le scadenze di Autograder di un’attività';
$string['task:reconcile_pending'] = 'Rimetti in coda le decisioni di Autograder che hanno perso il loro compito';
