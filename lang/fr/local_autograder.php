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

    $string['form:heading'] = 'Notation Automatique';
    $string['form:enabled'] = 'Activer la notation automatique pour cette activité';
    $string['form:grade'] = 'Note automatique à attribuer';
    $string['form:error_numeric'] = 'Entrez uniquement une valeur numérique entière';
    $string['form:error_max_grade'] = 'La note automatique ne peut pas être supérieure à la note maximale';
    $string['form:days_to_complete'] = 'Jours d’attente avant la notation automatique';
    $string['form:hours_to_complete'] = 'Heures d’attente avant la notation automatique';
    $string['form:minutes_to_complete'] = 'Minutes d’attente avant la notation automatique';
    $string['form:time_to_complete'] = 'Temps d’attente avant la notation';
    $string['form:enabled_help'] = 'Active/désactive la notation automatique pour cette activité. Si activée, la note automatique sera attribuée après le temps configuré.';
    $string['form:grade_help'] = 'Note numérique entière qui sera automatiquement attribuée lorsque le temps configuré est écoulé.';
    $string['form:time_to_complete_help'] = 'Définit la période (jours ou heures ou minutes) après laquelle la notation automatique sera appliquée.';
    $string['form:error_days_range'] = 'Les jours doivent être compris entre 0 et 100.';
    $string['form:error_hours_range'] = 'Les heures doivent être comprises entre 0 et 24.';
    $string['form:error_minutes_range'] = 'Les minutes doivent être comprises entre 0 et 60.';
    $string['form:error_all_time_zero'] = 'Au moins un des jours, heures ou minutes doit être supérieur à 0.';
    $string['form:error_completion_tracking'] = 'La notation automatique est activée, configurez le suivi d’achèvement';
    $string['form:error_type_grade'] = 'La notation automatique est activée, seul le type score est accepté';

    $string['settings:enable'] = 'Activer le plugin autograder';
    $string['settings:enableDescription'] = 'Valeur par défaut : Oui';

    $string['setting:days_to_completeTitle'] = 'Jours d’attente avant la notation automatique';
    $string['setting:days_to_completeHelper'] = 'Nombre de jours que le système doit attendre avant la notation automatique.';

    $string['setting:hours_to_completeTitle'] = 'Heures d’attente avant la notation automatique';
    $string['setting:hours_to_completeHelper'] = 'Nombre d’heures autorisées (en plus des jours) que le système doit attendre avant la notation automatique.';

    $string['setting:minutes_to_completeTitle'] = 'Minutes d’attente avant la notation automatique';
    $string['setting:minutes_to_completeHelper'] = 'Nombre de minutes autorisées (en plus des jours et des heures) que le système doit attendre avant la notation automatique.';

    $string['setting:default_gradeTitle'] = 'Note automatique à attribuer';
    $string['setting:default_gradeHelper'] = 'Note par défaut à attribuer si aucune note spécifique n’est fournie. Entrez uniquement une valeur numérique entière';

    $string['event:autograder_created'] = 'Autograder Créé';
    $string['event:autograder_updated'] = 'Autograder Mis à Jour';

    $string['form:error_completion_tracking'] = 'Pour activer l\'autograder, vous devez activer le suivi d’achèvement dans la section "Achèvement de l’activité".';
    $string['form:error_type_grade'] = 'L\'autograder fonctionne uniquement avec des activités configurées avec une notation par "Score" (pas d’échelles ni sans notation).';

