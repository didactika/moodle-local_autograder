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

    $string['form:heading'] = 'Valutazione Automatica';
    $string['form:enabled'] = 'Abilita la valutazione automatica per questa attività';
    $string['form:grade'] = 'Voto automatico da assegnare';
    $string['form:error_numeric'] = 'Inserire solo un valore numerico intero';
    $string['form:error_max_grade'] = 'Il punteggio della valutazione automatica non può essere superiore al punteggio massimo';
    $string['form:days_to_complete'] = 'Giorni di attesa per la valutazione automatica';
    $string['form:hours_to_complete'] = 'Ore di attesa per la valutazione automatica';
    $string['form:minutes_to_complete'] = 'Minuti di attesa per la valutazione automatica';
    $string['form:time_to_complete'] = 'Tempo di attesa prima della valutazione';
    $string['form:enabled_help'] = 'Abilita/disabilita la valutazione automatica per questa attività. Se abilitata, il voto automatico sarà assegnato dopo il tempo configurato.';
    $string['form:grade_help'] = 'Voto numerico intero che sarà assegnato automaticamente quando termina il tempo configurato.';
    $string['form:time_to_complete_help'] = 'Imposta il periodo (giorni o ore o minuti) dopo il quale sarà applicata la valutazione automatica.';
    $string['form:error_days_range'] = 'I giorni devono essere compresi tra 0 e 100.';
    $string['form:error_hours_range'] = 'Le ore devono essere comprese tra 0 e 24.';
    $string['form:error_minutes_range'] = 'I minuti devono essere compresi tra 0 e 60.';
    $string['form:error_all_time_zero'] = 'Almeno uno tra giorni, ore o minuti deve essere maggiore di 0.';
    $string['form:error_completion_tracking'] = 'La valutazione automatica è abilitata, impostare il tracciamento del completamento';
    $string['form:error_type_grade'] = 'La valutazione automatica è abilitata, è accettato solo il tipo punteggio';

    $string['settings:enable'] = 'Abilita il plugin autograder';
    $string['settings:enableDescription'] = 'Valore predefinito: Sì';

    $string['setting:days_to_completeTitle'] = 'Giorni di attesa per la valutazione automatica';
    $string['setting:days_to_completeHelper'] = 'Numero di giorni che il sistema deve attendere prima della valutazione automatica.';

    $string['setting:hours_to_completeTitle'] = 'Ore di attesa per la valutazione automatica';
    $string['setting:hours_to_completeHelper'] = 'Numero di ore consentite (oltre ai giorni) che il sistema deve attendere prima della valutazione automatica.';

    $string['setting:minutes_to_completeTitle'] = 'Minuti di attesa per la valutazione automatica';
    $string['setting:minutes_to_completeHelper'] = 'Numero di minuti consentiti (oltre a giorni e ore) che il sistema deve attendere prima della valutazione automatica.';

    $string['setting:default_gradeTitle'] = 'Voto automatico da assegnare';
    $string['setting:default_gradeHelper'] = 'Voto predefinito da assegnare se non viene specificato un voto. Inserire solo un valore numerico intero';

    $string['event:autograder_created'] = 'Autograder Creato';
    $string['event:autograder_updated'] = 'Autograder Aggiornato';

    $string['form:error_completion_tracking'] = 'Per abilitare l\'autograder, è necessario attivare il tracciamento del completamento nella sezione "Completamento attività".';
    $string['form:error_type_grade'] = 'L\'autograder funziona solo con attività configurate con valutazione a "Punteggio" (non scale o senza valutazione).';


