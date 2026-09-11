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

$string['pluginname'] = 'Autograder';

$string['settings:generaltab'] = 'Generale';
$string['settings:modulestab'] = 'Attività autovalutabili';
$string['settings:retentiontab'] = 'Conservazione';
$string['setting:tiebreak'] = 'Regola di spareggio';
$string['setting:tiebreak_desc'] = 'Quando più docenti possono valutare uno studente, quale viene scelto.';
$string['setting:tiebreak_lowest_userid'] = "Quello con l'ID utente più basso";
$string['setting:tiebreak_last_course_access'] = 'Quello che ha effettuato l\'accesso al corso più di recente';
$string['setting:default_grade'] = 'Voto predefinito';
$string['setting:default_grade_desc'] = 'Voto suggerito la prima volta che si attiva autograder su un\'attività.';
$string['setting:default_days'] = 'Ritardo predefinito (giorni)';
$string['setting:default_time_desc'] = 'Quanto attendere, dopo la scadenza, prima di valutare — suddiviso in giorni, ore e minuti.';
$string['setting:default_hours'] = 'Ritardo predefinito (ore)';
$string['setting:default_minutes'] = 'Ritardo predefinito (minuti)';
$string['setting:fallback_grader'] = 'Valutatore di riserva';
$string['setting:fallback_grader_desc'] = 'Usato solo quando nessun docente del corso stesso è idoneo a valutare uno studente (vedi la capacità "gradeonbehalf"). Sono proposti solo utenti che potrebbero plausibilmente valutare qualcosa.';
$string['setting:fallback_grader_none'] = 'Nessuno';
$string['setting:fallback_grader_placeholder'] = 'Cerca un utente…';
$string['setting:fallback_grader_ineligible'] = 'Quell\'utente non possiede alcuna capacità di valutazione e non può essere impostato come valutatore di riserva.';
$string['setting:modules_heading'] = 'Scegli quali tipi di attività possono avere autograder configurato nella <a href="{$a->url}">pagina delle attività autovalutabili</a>.';
$string['setting:retentiondays'] = 'Conservazione (giorni)';
$string['setting:retentiondays_desc'] = 'Per quanto tempo una decisione conclusa e il suo registro di valutazione vengono conservati prima di essere eliminati.';

$string['modules:intro'] = 'Solo i tipi di attività selezionati qui possono avere autograder configurato su una delle loro istanze.';
$string['modules:saved'] = 'Salvato.';

$string['form:heading'] = 'Autograder';
$string['form:enabled'] = 'Attiva autograder';
$string['form:enabled_help'] = 'Se attivato, uno studente che completa questa attività viene valutato automaticamente, il tempo configurato dopo la scadenza, con il voto indicato sotto — a meno che qualcuno lo valuti manualmente prima.';
$string['form:grade'] = 'Voto da assegnare';
$string['form:days_to_complete'] = 'Giorni';
$string['form:hours_to_complete'] = 'Ore';
$string['form:minutes_to_complete'] = 'Minuti';
$string['form:time_to_complete_help'] = 'Quanto attendere, dopo la scadenza, prima di valutare.';
$string['form:error_numeric'] = 'Deve essere un numero.';
$string['form:error_negative_time'] = 'Non può essere negativo.';
$string['form:error_completion_tracking'] = 'Autograder richiede che il tracciamento del completamento sia attivo su questa attività.';

$string['preference:optout'] = 'Non permettere ad autograder di valutare a mio nome';
$string['preference:optout_help'] = 'Se selezionato, autograder non ti sceglierà mai come il docente che valuta uno studente, anche se altrimenti saresti idoneo.';
$string['preference:saved'] = 'Preferenza salvata.';

$string['event:config_created'] = 'Configurazione di autograder creata';
$string['event:config_updated'] = 'Configurazione di autograder aggiornata';
$string['event:config_deleted'] = 'Configurazione di autograder eliminata';

$string['autograder:gradeonbehalf'] = 'Essere idoneo affinché autograder assegni voti a tuo nome';
$string['autograder:configure'] = 'Attivare o disattivare autograder su un\'attività';
$string['autograder:manage'] = 'Gestire le impostazioni di autograder a livello di sito';
$string['autograder:viewreport'] = 'Visualizzare il report di autograder di un corso';

$string['privacy:metadata'] = 'Autograder conserva, per ogni studente, se e quando deve essere valutato automaticamente, e un registro di ogni tentativo di valutazione.';
