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
 * French language strings.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Autograder';

$string['settings:generaltab'] = 'Général';
$string['settings:modulestab'] = 'Activités autocorrigeables';
$string['settings:retentiontab'] = 'Rétention';
$string['setting:tiebreak'] = 'Règle de départage';
$string['setting:tiebreak_desc'] = "Quand plusieurs enseignants peuvent noter un étudiant, lequel est choisi.";
$string['setting:tiebreak_lowest_userid'] = "Celui dont l'ID utilisateur est le plus bas";
$string['setting:tiebreak_last_course_access'] = "Celui qui a accédé au cours le plus récemment";
$string['setting:default_grade'] = 'Note par défaut';
$string['setting:default_grade_desc'] = "Note suggérée lors de la première activation d'autograder sur une activité.";
$string['setting:default_days'] = 'Délai par défaut (jours)';
$string['setting:default_time_desc'] = "Combien de temps attendre, après la date d'échéance, avant de noter — réparti en jours, heures et minutes.";
$string['setting:default_hours'] = 'Délai par défaut (heures)';
$string['setting:default_minutes'] = 'Délai par défaut (minutes)';
$string['setting:fallback_grader'] = 'Correcteur de secours';
$string['setting:fallback_grader_desc'] = "Utilisé seulement quand aucun enseignant du cours lui-même n'est éligible pour noter un étudiant (voir la capacité « gradeonbehalf »). Seuls les utilisateurs pouvant plausiblement noter quelque chose sont proposés.";
$string['setting:fallback_grader_none'] = 'Aucun';
$string['setting:fallback_grader_placeholder'] = 'Rechercher un utilisateur…';
$string['setting:fallback_grader_ineligible'] = "Cet utilisateur ne détient aucune capacité de notation et ne peut pas être défini comme correcteur de secours.";
$string['setting:modules_heading'] = "Choisissez quels types d'activité peuvent avoir autograder configuré sur la <a href=\"{\$a->url}\">page des activités autocorrigeables</a>.";
$string['setting:retentiondays'] = 'Rétention (jours)';
$string['setting:retentiondays_desc'] = 'Combien de temps une décision terminée et son journal de notation sont conservés avant purge.';

$string['modules:intro'] = "Seuls les types d'activité cochés ici peuvent avoir autograder configuré sur l'une de leurs instances.";
$string['modules:saved'] = 'Enregistré.';

$string['form:heading'] = 'Autograder';
$string['form:enabled'] = 'Activer autograder';
$string['form:enabled_help'] = "Si activé, un étudiant qui termine cette activité est noté automatiquement, le délai configuré après son échéance, avec la note indiquée ci-dessous — sauf si quelqu'un le note à la main avant.";
$string['form:grade'] = 'Note à attribuer';
$string['form:days_to_complete'] = 'Jours';
$string['form:hours_to_complete'] = 'Heures';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:time_to_complete_help'] = "Combien de temps attendre, après la date d'échéance, avant de noter.";
$string['form:error_numeric'] = 'Doit être un nombre.';
$string['form:error_negative_time'] = 'Ne peut pas être négatif.';
$string['form:error_completion_tracking'] = "Autograder nécessite que le suivi d'achèvement soit activé sur cette activité.";

$string['preference:optout'] = "Ne pas laisser autograder noter en mon nom";
$string['preference:optout_help'] = "Si coché, autograder ne vous choisira jamais comme l'enseignant qui note un étudiant, même si vous y seriez sinon éligible.";
$string['preference:saved'] = 'Préférence enregistrée.';

$string['event:config_created'] = "Configuration d'autograder créée";
$string['event:config_updated'] = "Configuration d'autograder mise à jour";
$string['event:config_deleted'] = "Configuration d'autograder supprimée";

$string['autograder:gradeonbehalf'] = 'Être éligible pour que autograder pose des notes en votre nom';
$string['autograder:configure'] = 'Activer ou désactiver autograder sur une activité';
$string['autograder:manage'] = "Gérer les réglages d'autograder au niveau du site";
$string['autograder:viewreport'] = "Voir le rapport d'autograder d'un cours";

$string['privacy:metadata'] = "Autograder conserve, par étudiant, s'il doit être noté automatiquement et quand, ainsi qu'un journal de chaque tentative de notation.";
