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
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Definisci prima la griglia o la guida di valutazione di questa attività, poi torna per indicare quali livelli deve segnare autograder.';
$string['advanced:edit_definition'] = 'Per cambiare la rubric o la griglia di valutazione stessa, <a href="{$a}">modifica la sua definizione</a>.';
$string['advanced:heading'] = 'Livelli segnati da autograder';
$string['advanced:intro'] = 'Compila questo esattamente come faresti valutando a mano uno studente di <strong>{$a}</strong>. Autograder contrassegna ciò che scegli qui e Moodle ne ricava il voto come fa sempre.';
$string['advanced:marks'] = 'Ciò che contrassegna autograder';
$string['advanced:not_advanced'] = 'Questa attività non è valutata con una griglia né con una guida di valutazione.';
$string['advanced:save'] = 'Salva ciò che contrassegna autograder';
$string['advanced:saved'] = 'Salvato ciò che segnerà autograder.';
$string['autograder:configure'] = 'Attivare o disattivare autograder su un\'attività';
$string['autograder:gradeonbehalf'] = 'Essere idoneo affinché autograder assegni voti a tuo nome';
$string['autograder:manage'] = 'Gestire le impostazioni di autograder a livello di sito';
$string['error:advancedgradingstale'] = 'La rubric o la griglia di valutazione è cambiata da quando si è indicato ad autograder cosa contrassegnare. Apri le impostazioni autograder dell\'attività e scegli di nuovo i livelli.';
$string['error:gradewritefailed'] = 'Moodle ha rifiutato il voto che autograder ha tentato di inserire.';
$string['error:nogradeitem'] = 'Questa attività non ha un elemento di valutazione su cui scrivere.';
$string['event:config_created'] = 'Configurazione di autograder creata';
$string['event:config_deleted'] = 'Configurazione di autograder eliminata';
$string['event:config_updated'] = 'Configurazione di autograder aggiornata';
$string['event:decision_cancelled'] = 'Decisione di valutazione automatica annullata';
$string['event:grading_failed'] = 'Valutazione automatica non riuscita';
$string['event:student_graded'] = 'Studente valutato automaticamente';
$string['form:advanced_define_first'] = 'Questa attività è valutata con una griglia o una guida, ma non ne è ancora definita nessuna. <a href="{$a}">Definiscila prima</a>, poi scegli cosa segna autograder.';
$string['form:advanced_set'] = 'Autograder sa cosa segnare su questa griglia o guida. <a href="{$a}">Modificalo</a>.';
$string['form:advanced_undefined'] = 'Questa attività è valutata con una griglia o guida che autograder non riesce a leggere.';
$string['form:advanced_unset'] = 'Scegli <a href="{$a}">cosa segna autograder</a> su questa griglia o guida — fino ad allora non ha con cosa valutare.';
$string['form:days_to_complete'] = 'Giorni';
$string['form:enabled'] = 'Attiva autograder';
$string['form:enabled_help'] = 'Se attivato, uno studente che completa questa attività viene valutato automaticamente, il tempo configurato dopo la scadenza, con il voto indicato sotto — a meno che qualcuno lo valuti manualmente prima.';
$string['form:error_advanced_unset'] = 'Ad autograder non è stato indicato cosa contrassegnare sulla rubric o griglia di valutazione di questa attività. <a href="{$a}">Scegli prima quello</a>, poi attivalo.';
$string['form:error_grade_above_max'] = 'Questa attività è valutata su {$a}, quindi il voto non può essere superiore.';
$string['form:error_grade_negative'] = 'Il voto non può essere negativo.';
$string['form:error_grade_required'] = 'Indica il voto che autograder deve assegnare.';
$string['form:error_hours_range'] = 'Indica da 0 a 23 ore. Per tempi maggiori usa il campo dei giorni.';
$string['form:error_minutes_range'] = 'Indica da 0 a 59 minuti. Per tempi maggiori usa il campo delle ore.';
$string['form:error_negative_time'] = 'Non può essere negativo.';
$string['form:error_not_graded'] = 'Autograder richiede che questa attività sia valutata. Scegli un tipo di voto diverso da "Nessuno".';
$string['form:error_numeric'] = 'Deve essere un numero.';
$string['form:error_scale_mismatch'] = 'Quella voce non appartiene alla scala che l\'attività usa adesso. Scegline una dalla scala che hai appena selezionato.';
$string['form:error_scale_unset'] = 'Scegli quale elemento della scala deve assegnare autograder.';
$string['form:error_whole_number'] = 'Indica un numero intero.';
$string['form:grade'] = 'Voto da assegnare';
$string['form:grade_range'] = 'Questa attività è valutata su {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Ore';
$string['form:minutes_to_complete'] = 'Minuti';
$string['form:time_to_complete'] = 'Tempo di attesa prima di valutare';
$string['form:time_to_complete_help'] = 'Quanto attendere, dopo la scadenza, prima di valutare.';
$string['modules:intro'] = 'Solo i tipi di attività attivati qui possono avere autograder configurato su una delle loro istanze.';
$string['pluginname'] = 'Autograder';
$string['preference:optout'] = 'Non permettere ad autograder di valutare a mio nome';
$string['preference:optout_help'] = 'Se selezionato, autograder non ti sceglierà mai come il docente che valuta uno studente, anche se altrimenti saresti idoneo.';
$string['preference:saved'] = 'Preferenza salvata.';
$string['privacy:metadata'] = 'Autograder conserva, per ogni studente, se e quando deve essere valutato automaticamente, e un registro di ogni tentativo di valutazione.';
$string['privacy:metadata:config'] = 'Ciò che un docente ha configurato per autograder su un\'attività.';
$string['privacy:metadata:config:cmid'] = 'L\'attività a cui appartiene la configurazione.';
$string['privacy:metadata:config:timemodified'] = 'Quando la configurazione è stata salvata l\'ultima volta.';
$string['privacy:metadata:config:usermodified'] = 'L\'utente che ha salvato per ultimo la configurazione.';
$string['privacy:metadata:decision'] = 'La decisione di autograder, in attesa o conclusa, su uno studente in un\'attività.';
$string['privacy:metadata:decision:baselineduedate'] = 'La data da cui si conta il ritardo.';
$string['privacy:metadata:decision:duedatereason'] = 'Perché è stata usata quella data: completamento, consegna, data di chiusura o una deroga.';
$string['privacy:metadata:decision:failurereason'] = 'Perché la decisione è stata annullata o non è andata a buon fine.';
$string['privacy:metadata:decision:gradedvalue'] = 'Il voto inserito da autograder.';
$string['privacy:metadata:decision:graderid'] = 'Il docente a nome del quale è stato inserito il voto.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Quando il voto deve, o doveva, essere inserito.';
$string['privacy:metadata:decision:status'] = 'Se la decisione è in attesa, valutata, presa a mano, annullata o fallita.';
$string['privacy:metadata:decision:timemodified'] = 'Quando la decisione è cambiata l\'ultima volta.';
$string['privacy:metadata:decision:userid'] = 'Lo studente a cui si riferisce la decisione.';
$string['privacy:metadata:gradelog'] = 'Il registro di ciò che autograder ha fatto, non ha fatto e perché.';
$string['privacy:metadata:gradelog:graderid'] = 'Il docente a nome del quale è stato inserito il voto.';
$string['privacy:metadata:gradelog:gradevalue'] = 'Il voto inserito, quando c\'è stato.';
$string['privacy:metadata:gradelog:message'] = 'Che cosa è successo, a parole.';
$string['privacy:metadata:gradelog:outcome'] = 'Se lo studente è stato valutato, saltato, annullato o fallito.';
$string['privacy:metadata:gradelog:timecreated'] = 'Quando è successo.';
$string['privacy:metadata:gradelog:userid'] = 'Lo studente a cui si riferisce la voce.';
$string['privacy:metadata:preference:optout'] = 'Se questo utente ha chiesto di non essere scelto come docente a nome del quale autograder valuta.';
$string['privacy:path:config'] = 'Configurazione di autograder';
$string['privacy:path:decision'] = 'Decisioni di autograder';
$string['privacy:path:gradelog'] = 'Cronologia di autograder';
$string['setting:default_days'] = 'Ritardo predefinito (giorni)';
$string['setting:default_grade'] = 'Voto predefinito';
$string['setting:default_grade_desc'] = 'Voto suggerito la prima volta che si attiva autograder su un\'attività.';
$string['setting:default_hours'] = 'Ritardo predefinito (ore)';
$string['setting:default_minutes'] = 'Ritardo predefinito (minuti)';
$string['setting:default_time_desc'] = 'Quanto attendere, dopo la scadenza, prima di valutare — suddiviso in giorni, ore e minuti.';
$string['setting:fallback_grader'] = 'Valutatore di riserva';
$string['setting:fallback_grader_desc'] = 'Usato solo quando nessun docente del corso stesso è idoneo a valutare uno studente (vedi la capacità "gradeonbehalf"). Sono proposti solo utenti che potrebbero plausibilmente valutare qualcosa.';
$string['setting:fallback_grader_ineligible'] = 'Quell\'utente non possiede alcuna capacità di valutazione e non può essere impostato come valutatore di riserva.';
$string['setting:fallback_grader_none'] = 'Nessuno';
$string['setting:fallback_grader_placeholder'] = 'Cerca un utente…';
$string['setting:modules_disable'] = 'Disattiva autograder per {$a}';
$string['setting:modules_enable'] = 'Attiva autograder per {$a}';
$string['setting:modules_enabled_column'] = 'Attivo';
$string['setting:modules_heading'] = 'Scegli quali tipi di attività possono avere autograder configurato nella <a href="{$a->url}">pagina delle attività autovalutabili</a>.';
$string['setting:retentiondays'] = 'Conservazione (giorni)';
$string['setting:retentiondays_desc'] = 'Per quanto tempo una decisione conclusa e il suo registro di valutazione vengono conservati prima di essere eliminati.';
$string['setting:tiebreak'] = 'Regola di spareggio';
$string['setting:tiebreak_desc'] = 'Quando più docenti possono valutare uno studente, quale viene scelto.';
$string['setting:tiebreak_last_course_access'] = 'Quello che ha effettuato l\'accesso al corso più di recente';
$string['setting:tiebreak_lowest_userid'] = "Quello con l'ID utente più basso";
$string['settings:generaltab'] = 'Generale';
$string['settings:modulestab'] = 'Attività autovalutabili';
$string['settings:retentiontab'] = 'Conservazione';
$string['task:cancel_module'] = 'Annullare le valutazioni automatiche in sospeso su un\'attività';
$string['task:catch_up_module'] = 'Recuperare gli studenti già in attesa su un\'attività';
$string['task:grade_student'] = 'Valutare uno studente';
$string['task:purge_history'] = 'Eliminare le decisioni e il registro di valutazione automatica conclusi';
$string['task:recalculate_module'] = 'Ricalcolare le date di valutazione automatica di un\'attività';
$string['task:reconcile_pending'] = 'Rimettere in coda le decisioni di valutazione automatica che hanno perso la loro pianificazione';
