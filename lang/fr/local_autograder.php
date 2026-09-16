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
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = "Définissez d'abord la grille ou le guide d'évaluation de cette activité, puis revenez indiquer quels niveaux autograder doit cocher.";
$string['advanced:edit_definition'] = 'Pour modifier la grille d\'évaluation elle-même, <a href="{$a}">modifiez sa définition</a>.';
$string['advanced:heading'] = 'Niveaux cochés par autograder';
$string['advanced:intro'] = 'Remplissez ceci exactement comme vous le feriez en notant à la main un étudiant de <strong>{$a}</strong>. Autograder coche ce que vous choisissez ici, et Moodle en déduit la note comme il le fait toujours.';
$string['advanced:marks'] = 'Ce que coche autograder';
$string['advanced:not_advanced'] = "Cette activité n'est notée ni par une grille ni par un guide d'évaluation.";
$string['advanced:save'] = 'Enregistrer ce que coche autograder';
$string['advanced:saved'] = "Ce qu'autograder cochera a été enregistré.";
$string['autograder:configure'] = 'Activer ou désactiver autograder sur une activité';
$string['autograder:manage'] = "Gérer les réglages d'autograder au niveau du site";
$string['error:advancedgradingstale'] = 'La grille d\'évaluation a changé depuis qu\'on a indiqué à autograder ce qu\'il devait y cocher. Ouvrez les réglages autograder de l\'activité et choisissez à nouveau les niveaux.';
$string['error:gradewritefailed'] = "Moodle a refusé la note qu'autograder a tenté de déposer.";
$string['error:nogradeitem'] = "Cette activité n'a aucun élément de note où écrire.";
$string['event:config_created'] = "Configuration d'autograder créée";
$string['event:config_deleted'] = "Configuration d'autograder supprimée";
$string['event:config_updated'] = "Configuration d'autograder mise à jour";
$string['event:decision_cancelled'] = 'Décision de notation automatique annulée';
$string['event:grading_failed'] = 'Échec de la notation automatique';
$string['event:student_graded'] = 'Étudiant noté automatiquement';
$string['form:advanced_define_first'] = "Cette activité est notée par une grille ou un guide, mais aucun n'est encore défini. <a href=\"{\$a}\">Définissez-le d'abord</a>, puis choisissez ce qu'autograder coche.";
$string['form:advanced_set'] = "Autograder sait quoi cocher sur cette grille ou ce guide. <a href=\"{\$a}\">Le modifier</a>.";
$string['form:advanced_undefined'] = "Cette activité est notée par une grille ou un guide qu'autograder ne peut pas lire.";
$string['form:advanced_unset'] = "Choisissez <a href=\"{\$a}\">ce qu'autograder coche</a> sur cette grille ou ce guide — sans cela il n'a rien pour noter.";
$string['form:days_to_complete'] = 'Jours';
$string['form:enabled'] = 'Activer autograder';
$string['form:enabled_help'] = "Si activé, un étudiant qui termine cette activité est noté automatiquement, le délai configuré après son échéance, avec la note indiquée ci-dessous — sauf si quelqu'un le note à la main avant.";
$string['form:error_advanced_unset'] = 'On n\'a pas indiqué à autograder ce qu\'il doit cocher sur la grille d\'évaluation de cette activité. <a href="{$a}">Choisissez-le d\'abord</a>, puis activez-le.';
$string['form:error_grade_above_max'] = 'Cette activité est notée sur {$a} : la note ne peut pas être supérieure.';
$string['form:error_grade_negative'] = 'La note ne peut pas être négative.';
$string['form:error_grade_required'] = 'Indiquez la note qu\'autograder doit attribuer.';
$string['form:error_hours_range'] = 'Indiquez de 0 à 23 heures. Au-delà, utilisez le champ des jours.';
$string['form:error_minutes_range'] = 'Indiquez de 0 à 59 minutes. Au-delà, utilisez le champ des heures.';
$string['form:error_negative_time'] = 'Ne peut pas être négatif.';
$string['form:error_not_graded'] = "Autograder a besoin que cette activité soit notée. Choisissez un type de note autre que « Aucune ».";
$string['form:error_numeric'] = 'Doit être un nombre.';
$string['form:error_scale_mismatch'] = 'Cet élément n\'appartient pas au barème que l\'activité utilise maintenant. Choisissez-en un dans le barème que vous venez de sélectionner.';
$string['form:error_scale_unset'] = "Choisissez l'élément de barème qu'autograder doit attribuer.";
$string['form:error_whole_number'] = 'Indiquez un nombre entier.';
$string['form:grade'] = 'Note à attribuer';
$string['form:grade_range'] = 'Cette activité est notée sur {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Heures';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:time_to_complete'] = "Délai d'attente avant la notation";
$string['form:time_to_complete_help'] = "Combien de temps attendre, après la date d'échéance, avant de noter.";
$string['modules:intro'] = "Seuls les types d'activité activés ici peuvent avoir autograder configuré sur l'une de leurs instances.";
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Configuration d\'autograder';
$string['preference:notoffered'] = 'Ce site ne permet pas aux enseignants de refuser qu\'on note en leur nom.';
$string['preference:optout'] = "Ne pas laisser autograder noter en mon nom";
$string['preference:optout_help'] = "Si coché, autograder ne vous choisira jamais comme l'enseignant qui note un étudiant, même si vous y seriez sinon éligible.";
$string['preference:saved'] = 'Préférence enregistrée.';
$string['privacy:metadata'] = "Autograder conserve, par étudiant, s'il doit être noté automatiquement et quand, ainsi qu'un journal de chaque tentative de notation.";
$string['privacy:metadata:config'] = 'Ce qu\'un enseignant a configuré pour autograder sur une activité.';
$string['privacy:metadata:config:cmid'] = 'L\'activité à laquelle appartient la configuration.';
$string['privacy:metadata:config:timemodified'] = 'Quand la configuration a été enregistrée pour la dernière fois.';
$string['privacy:metadata:config:usermodified'] = 'L\'utilisateur ayant enregistré la configuration en dernier.';
$string['privacy:metadata:decision'] = 'La décision d\'autograder, en attente ou réglée, sur un étudiant dans une activité.';
$string['privacy:metadata:decision:baselineduedate'] = 'La date à partir de laquelle le délai est compté.';
$string['privacy:metadata:decision:duedatereason'] = 'Pourquoi cette date a été retenue : achèvement, remise, date de fermeture ou dérogation.';
$string['privacy:metadata:decision:failurereason'] = 'Pourquoi la décision a été annulée ou n\'a pas pu aboutir.';
$string['privacy:metadata:decision:gradedvalue'] = 'La note déposée par autograder.';
$string['privacy:metadata:decision:graderid'] = 'L\'enseignant au nom duquel la note a été déposée.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Quand la note doit, ou devait, être déposée.';
$string['privacy:metadata:decision:status'] = 'Si la décision est en attente, notée, reprise à la main, annulée ou en échec.';
$string['privacy:metadata:decision:timemodified'] = 'Quand la décision a changé pour la dernière fois.';
$string['privacy:metadata:decision:userid'] = 'L\'étudiant concerné par la décision.';
$string['privacy:metadata:gradelog'] = 'Le relevé de ce qu\'autograder a fait, n\'a pas fait, et pourquoi.';
$string['privacy:metadata:gradelog:graderid'] = 'L\'enseignant au nom duquel la note a été déposée.';
$string['privacy:metadata:gradelog:gradevalue'] = 'La note déposée, le cas échéant.';
$string['privacy:metadata:gradelog:message'] = 'Ce qui s\'est passé, en toutes lettres.';
$string['privacy:metadata:gradelog:outcome'] = 'Si l\'étudiant a été noté, ignoré, annulé ou en échec.';
$string['privacy:metadata:gradelog:timecreated'] = 'Quand cela s\'est produit.';
$string['privacy:metadata:gradelog:userid'] = 'L\'étudiant concerné par l\'entrée.';
$string['privacy:metadata:preference:optout'] = 'Si cet utilisateur a demandé à ne pas être choisi comme enseignant au nom duquel autograder note.';
$string['privacy:path:config'] = 'Configuration d\'autograder';
$string['privacy:path:decision'] = 'Décisions d\'autograder';
$string['privacy:path:gradelog'] = 'Historique d\'autograder';
$string['setting:allowoptout'] = 'Autoriser les enseignants à se retirer';
$string['setting:allowoptout_desc'] = 'Propose à chaque utilisateur une préférence demandant qu\'autograder ne note jamais en son nom. Désactivé par défaut : retirer des enseignants du tour de rôle change qui est noté et quand, et sur un site comptant peu d\'enseignants éligibles cela peut laisser une activité sans personne au nom de qui noter. Tant qu\'il est désactivé, la préférence n\'est ni affichée ni prise en compte.';
$string['setting:coordinator_roles'] = 'Rôles de coordination';
$string['setting:coordinator_roles_desc'] = 'La même question pour un cours de programme : quels rôles font de quelqu\'un un de ses coordinateurs. Ignoré sauf si les deux catégories ci-dessous en désignent des différentes.';
$string['setting:fallback_grader'] = 'Correcteur de secours';
$string['setting:fallback_grader_desc'] = "Utilisé seulement quand aucun enseignant du cours lui-même n'est éligible pour noter un étudiant . Seuls les utilisateurs pouvant plausiblement noter quelque chose sont proposés.";
$string['setting:fallback_grader_ineligible'] = "Cet utilisateur ne détient aucune capacité de notation et ne peut pas être défini comme correcteur de secours.";
$string['setting:fallback_grader_none'] = 'Aucun';
$string['setting:fallback_grader_placeholder'] = 'Rechercher un utilisateur…';
$string['setting:modules_disable'] = "Désactiver autograder pour {\$a}";
$string['setting:modules_enable'] = "Activer autograder pour {\$a}";
$string['setting:modules_enabled_column'] = 'Activé';
$string['setting:modules_heading'] = "Choisissez quels types d'activité peuvent avoir autograder configuré sur la <a href=\"{\$a->url}\">page des activités autocorrigeables</a>.";
$string['setting:program_course_category'] = 'Catégorie des cours de programme';
$string['setting:program_course_category_desc'] = 'Les cours de cette catégorie sont des programmes et prennent leurs enseignants dans les rôles de coordination. Laissez-la identique à celle du dessus si le site ne fait pas cette distinction.';
$string['setting:retentiondays'] = 'Rétention (jours)';
$string['setting:retentiondays_desc'] = 'Combien de temps une décision terminée et son journal de notation sont conservés avant purge.';
$string['setting:subject_course_category'] = 'Catégorie des cours de matière';
$string['setting:subject_course_category_desc'] = 'Les cours de cette catégorie sont des matières et prennent leurs enseignants dans les rôles enseignants. Un cours qui n\'est dans aucune des deux est lu comme une matière.';
$string['setting:teacher_roles'] = 'Rôles enseignants';
$string['setting:teacher_roles_desc'] = 'Quels rôles font de quelqu\'un un enseignant du cours. La note est attribuée au nom de l\'un d\'eux, donc un rôle manquant ici ne sera jamais choisi, ce qui laisse un cours à déclarer que personne ne peut le noter. Seuls comptent les rôles attribués dans le cours lui-même ; un rôle porté sur toute la catégorie ne compte pas.';
$string['setting:teachers_heading'] = 'Qui autograder considère comme enseignant et donc au nom de qui il attribue une note. Lorsque le cours sépare ses groupes et désigne un groupement par défaut, la liste est réduite aux enseignants partageant un groupe avec l\'étudiant ; si aucun ne le fait, tous les enseignants du cours tiennent. Aucune capacité n\'est vérifiée : ce qui compte est de porter l\'un de ces rôles.';
$string['setting:tiebreak'] = 'Règle de départage';
$string['setting:tiebreak_desc'] = "Quand plusieurs enseignants peuvent noter un étudiant, lequel est choisi.";
$string['setting:tiebreak_last_course_access'] = "Celui qui a accédé au cours le plus récemment";
$string['setting:tiebreak_lowest_userid'] = "Celui dont l'ID utilisateur est le plus bas";
$string['settings:generaltab'] = 'Général';
$string['settings:modulestab'] = 'Activités autocorrigeables';
$string['settings:retentiontab'] = 'Rétention';
$string['settings:teacherstab'] = 'Enseignants';
$string['task:cancel_module'] = 'Annuler les notations automatiques en attente sur une activité';
$string['task:catch_up_module'] = 'Rattraper les étudiants déjà en attente sur une activité';
$string['task:grade_student'] = 'Noter un étudiant';
$string['task:purge_history'] = 'Purger les décisions et le journal de notation automatique terminés';
$string['task:recalculate_module'] = 'Recalculer les dates de notation automatique d\'une activité';
$string['task:reconcile_pending'] = 'Remettre en file les décisions de notation automatique ayant perdu leur tâche';
