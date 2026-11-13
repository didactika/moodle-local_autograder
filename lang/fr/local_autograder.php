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
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['advanced:define_first'] = 'Définissez d’abord la grille critériée ou le guide d’évaluation de cette activité, puis revenez indiquer quels niveaux Autograder doit cocher.';
$string['advanced:edit_definition'] = 'Pour modifier la grille critériée ou le guide d’évaluation lui-même, <a href="{$a}">modifiez sa définition</a>.';
$string['advanced:heading'] = 'Niveaux cochés par Autograder';
$string['advanced:intro'] = 'Remplissez-le exactement comme vous le feriez en notant à la main un étudiant de <strong>{$a}</strong>. Autograder coche ce que vous choisissez ici et Moodle en calcule la note de la même manière que d’habitude.';
$string['advanced:marks'] = 'Ce qu’Autograder coche';
$string['advanced:not_advanced'] = 'Cette activité n’est pas notée avec une grille critériée ni avec un guide d’évaluation.';
$string['advanced:save'] = 'Enregistrer ce qu’Autograder coche';
$string['advanced:saved'] = 'Ce qu’Autograder cochera a été enregistré.';
$string['autograder:configure'] = 'Activer ou désactiver Autograder pour une activité';
$string['autograder:manage'] = 'Gérer les paramètres d’Autograder pour l’ensemble du site';
$string['error:advancedgradingstale'] = 'La grille critériée ou le guide d’évaluation a changé depuis qu’Autograder a été informé de ce qu’il doit y cocher. Ouvrez les paramètres d’Autograder de l’activité et choisissez de nouveau les niveaux.';
$string['error:gradewritefailed'] = 'Moodle a refusé la note qu’Autograder a tenté de publier.';
$string['error:nogradeitem'] = 'Cette activité n’a pas d’élément d’évaluation dans lequel écrire.';
$string['event:config_created'] = 'Configuration d’Autograder créée';
$string['event:config_deleted'] = 'Configuration d’Autograder supprimée';
$string['event:config_updated'] = 'Configuration d’Autograder mise à jour';
$string['event:decision_cancelled'] = 'Décision d’Autograder annulée';
$string['event:grading_failed'] = 'Échec de la notation par Autograder';
$string['event:student_graded'] = 'Étudiant noté par Autograder';
$string['form:advanced_define_first'] = 'Cette activité est notée avec une grille critériée ou un guide d’évaluation, mais aucun n’est encore défini. <a href="{$a}">Définissez-le d’abord</a>, puis choisissez ce qu’Autograder coche.';
$string['form:advanced_set'] = 'Autograder sait quoi cocher dans cette grille ou ce guide. <a href="{$a}">Modifiez-le</a>.';
$string['form:advanced_undefined'] = 'Cette activité est notée avec une grille critériée ou un guide d’évaluation qu’Autograder ne peut pas lire.';
$string['form:advanced_unset'] = 'Choisissez <a href="{$a}">ce qu’Autograder coche</a> dans cette grille ou ce guide ; d’ici là, il n’a rien avec quoi noter.';
$string['form:days_to_complete'] = 'Jours';
$string['form:enabled'] = 'Activer Autograder';
$string['form:enabled_help'] = 'Lorsqu’il est activé, un étudiant qui termine cette activité est noté automatiquement, après le délai configuré suivant l’échéance, avec la note définie ci-dessous, sauf si quelqu’un le note d’abord à la main.';
$string['form:error_advanced_unset'] = 'Autograder n’a pas été informé de ce qu’il doit cocher dans la grille critériée ou le guide d’évaluation de cette activité. <a href="{$a}">Choisissez-le d’abord</a>, puis activez Autograder.';
$string['form:error_grade_above_max'] = 'Cette activité est notée sur {$a} ; la note ne peut donc pas être supérieure.';
$string['form:error_grade_negative'] = 'La note ne peut pas être négative.';
$string['form:error_grade_required'] = 'Saisissez la note qu’Autograder doit attribuer.';
$string['form:error_hours_range'] = 'Saisissez de 0 à 23 heures. Utilisez le champ des jours pour toute durée supérieure.';
$string['form:error_minutes_range'] = 'Saisissez de 0 à 59 minutes. Utilisez le champ des heures pour toute durée supérieure.';
$string['form:error_negative_time'] = 'Ne peut pas être négatif.';
$string['form:error_not_graded'] = 'Autograder a besoin que cette activité soit notée. Choisissez un type de note autre que « Aucune ».';
$string['form:error_numeric'] = 'Doit être un nombre.';
$string['form:error_scale_mismatch'] = 'Cet élément n’appartient pas à l’échelle que cette activité utilise désormais. Choisissez-en un dans l’échelle que vous venez de sélectionner.';
$string['form:error_scale_unset'] = 'Choisissez l’élément de l’échelle qu’Autograder doit attribuer.';
$string['form:error_whole_number'] = 'Saisissez un nombre entier.';
$string['form:grade'] = 'Note à attribuer';
$string['form:grade_range'] = 'Cette activité est notée sur {$a}.';
$string['form:heading'] = 'Autograder';
$string['form:hours_to_complete'] = 'Heures';
$string['form:minutes_to_complete'] = 'Minutes';
$string['form:needs_completion'] = 'Autograder ne peut savoir qu’un étudiant a fait ce type d’activité que grâce à l’achèvement de l’activité. Définissez une condition que l’étudiant peut remplir seul : la marquer comme terminée, la consulter, ou l’une des conditions propres à l’activité. N’incluez pas « Recevoir une note » ni « Recevoir une note de réussite » : tant que la note n’existe pas, l’activité ne compte jamais comme terminée, donc rien n’indiquerait à Autograder que l’étudiant a fini, et la note attendue serait celle qu’Autograder donnerait.';
$string['form:notenabled_advanced'] = 'Autograder a été laissé désactivé : il n’a pas été informé de ce qu’il doit cocher dans la grille critériée ou le guide d’évaluation de cette activité. <a href="{$a}">Choisissez-le d’abord</a>, puis activez-le.';
$string['form:notenabled_nocompletion'] = 'Autograder a été laissé désactivé : il ne peut savoir qu’un étudiant a fait ce type d’activité que grâce à l’achèvement de l’activité, et l’achèvement de cette activité n’a aucune condition que l’étudiant peut remplir seul, ou exige aussi une note.';
$string['form:quiz_note'] = 'Dans un test, Autograder n’intervient que tant qu’une question, comme une dissertation, attend d’être corrigée à la main, puis il note l’ensemble du test. Une fois que l’enseignant l’a corrigée, la note propre du test prend sa place. Un test qui se corrige seul conserve sa propre note.';
$string['form:time_to_complete'] = 'Délai d’attente avant la notation';
$string['form:time_to_complete_help'] = 'Combien de temps attendre, après la date d’échéance, avant de noter.';
$string['messageprovider:failuredigest'] = 'Étudiants qu’Autograder n’a pas pu noter';
$string['modules:count'] = '{$a} types d’activité';
$string['modules:empty'] = 'Aucun type d’activité ne correspond.';
$string['modules:filter_all'] = 'Tous';
$string['modules:filter_off'] = 'Désactivés';
$string['modules:filter_on'] = 'Activés';
$string['modules:intro'] = 'Désactiver un type masque les paramètres d’Autograder de ses activités et annule la notation encore en attente pour elles. Chaque activité conserve son propre paramètre Autograder ; réactiver le type les reprend donc, sauf lorsqu’une note a déjà été enregistrée.';
$string['modules:lead'] = 'Les types d’activité qui reçoivent le travail des étudiants et dont la note est attribuée par un enseignant';
$string['modules:name'] = 'Nom';
$string['modules:reads'] = 'Ce qu’Autograder lit';
$string['modules:reads_assign'] = 'Lit les remises des étudiants';
$string['modules:reads_completion'] = 'Suit l’achèvement de l’activité';
$string['modules:reads_forum'] = 'Lit les messages des étudiants';
$string['modules:reads_quiz'] = 'Lit les tentatives des étudiants';
$string['modules:search'] = 'Rechercher des types d’activité';
$string['modules:state'] = 'Autograder';
$string['notify:footer'] = 'Vous recevez ce message parce que vous pouvez configurer Autograder dans ces activités.';
$string['notify:intro'] = 'Depuis le dernier récapitulatif, Autograder n’a pas pu noter certains étudiants dans les activités ci-dessous. Ces étudiants n’ont pas été notés.';
$string['notify:more'] = 'Activités non énumérées ici : {$a}.';
$string['notify:reason_grade_write_failed'] = 'Moodle a refusé la note. Les causes habituelles sont une grille critériée ou un guide d’évaluation modifié depuis qu’Autograder a été informé de ce qu’il doit y cocher, ou un enseignant qui n’est pas autorisé à noter l’activité.';
$string['notify:reason_line'] = 'Étudiants concernés : {$a->count}. {$a->reason}';
$string['notify:reason_no_grader'] = 'Aucun enseignant n’a été trouvé au nom duquel enregistrer la note, et il n’y a pas de correcteur de secours à utiliser à la place.';
$string['notify:reason_other'] = 'La note n’a pas pu être enregistrée.';
$string['notify:small'] = 'Autograder n’a pas pu noter certains étudiants. Activités concernées : {$a}.';
$string['notify:subject'] = 'Autograder n’a pas pu noter certains étudiants';
$string['pluginname'] = 'Autograder';
$string['preference:heading'] = 'Préférences d’Autograder';
$string['preference:notoffered'] = 'Ce site ne permet pas aux enseignants de se désinscrire d’Autograder.';
$string['preference:optout'] = 'Ne jamais enregistrer de notes automatiques à mon nom';
$string['preference:optout_help'] = 'Autograder n’enregistrera jamais de notes à votre nom, même dans les cours que vous enseignez.';
$string['preference:saved'] = 'Préférence enregistrée.';
$string['privacy:metadata:config'] = 'Ce qu’un enseignant a configuré qu’Autograder fasse dans une activité.';
$string['privacy:metadata:config:cmid'] = 'L’activité à laquelle appartient la configuration.';
$string['privacy:metadata:config:timemodified'] = 'Le moment où la configuration a été enregistrée pour la dernière fois.';
$string['privacy:metadata:config:usermodified'] = 'L’utilisateur qui a enregistré la configuration en dernier.';
$string['privacy:metadata:core_message'] = 'Un récapitulatif des étudiants qu’Autograder n’a pas pu noter peut être envoyé aux enseignants par le système de messagerie.';
$string['privacy:metadata:decision'] = 'La décision en attente ou réglée d’Autograder concernant un étudiant dans une activité.';
$string['privacy:metadata:decision:baselineduedate'] = 'La date à partir de laquelle le délai est compté.';
$string['privacy:metadata:decision:duedatereason'] = 'Pourquoi cette date a été retenue : l’achèvement, la remise, la date de fermeture ou une exception.';
$string['privacy:metadata:decision:failurereason'] = 'Pourquoi la décision a été annulée ou n’a pas pu être exécutée.';
$string['privacy:metadata:decision:gradedvalue'] = 'La note qu’Autograder a publiée.';
$string['privacy:metadata:decision:graderid'] = 'L’enseignant au nom duquel la note a été publiée.';
$string['privacy:metadata:decision:scheduledgradetime'] = 'Le moment où la note doit être, ou devait être, publiée.';
$string['privacy:metadata:decision:status'] = 'Si la décision est en attente, notée, reprise à la main, annulée ou en échec.';
$string['privacy:metadata:decision:timemodified'] = 'Le moment où la décision a changé pour la dernière fois.';
$string['privacy:metadata:decision:userid'] = 'L’étudiant concerné par la décision.';
$string['privacy:metadata:gradelog'] = 'Un enregistrement de ce qu’Autograder a fait et n’a pas fait, et pourquoi.';
$string['privacy:metadata:gradelog:graderid'] = 'L’enseignant au nom duquel la note a été publiée.';
$string['privacy:metadata:gradelog:gradevalue'] = 'La note publiée, le cas échéant.';
$string['privacy:metadata:gradelog:message'] = 'Ce qui s’est passé, en mots.';
$string['privacy:metadata:gradelog:outcome'] = 'Si l’étudiant a été noté, ignoré, annulé ou en échec, ou si la note d’Autograder a cédé devant celle de l’activité.';
$string['privacy:metadata:gradelog:timecreated'] = 'Le moment où cela s’est produit.';
$string['privacy:metadata:gradelog:userid'] = 'L’étudiant concerné par l’entrée.';
$string['privacy:metadata:preference:optout'] = 'Si cet utilisateur a demandé à ne pas être choisi comme l’enseignant au nom duquel Autograder note.';
$string['privacy:path:config'] = 'Configuration d’Autograder';
$string['privacy:path:decision'] = 'Décisions d’Autograder';
$string['privacy:path:gradelog'] = 'Historique d’Autograder';
$string['setting:allowoptout'] = 'Permettre aux enseignants de se désinscrire';
$string['setting:allowoptout_desc'] = 'Ajoute une page de préférences où les utilisateurs peuvent demander qu’Autograder n’enregistre jamais de notes à leur nom. Sur les sites comptant peu d’enseignants, les désinscriptions peuvent laisser des étudiants sans personne au nom de qui noter. Lorsque l’option est désactivée, la page est masquée et les désinscriptions existantes sont ignorées.';
$string['setting:fallback_grader'] = 'Correcteur de secours';
$string['setting:fallback_grader_desc'] = 'Les notes sont enregistrées au nom de cet utilisateur lorsqu’un enseignant ne peut pas être choisi selon les règles automatiques, ou lorsque la notation au nom de l’enseignant choisi échoue. Seuls les utilisateurs ayant un rôle de notation sont listés ; les administrateurs du site ne sont jamais utilisés. Contrairement aux enseignants, cet utilisateur n’est pas vérifié pour chaque activité ; choisissez donc quelqu’un autorisé à noter partout où Autograder est utilisé. Si Aucun, ces étudiants ne sont pas notés et l’échec est consigné.';
$string['setting:fallback_grader_ineligible'] = 'Cet utilisateur ne peut pas être le correcteur de secours. Les administrateurs du site et les utilisateurs sans rôle pouvant noter ne sont jamais utilisés.';
$string['setting:fallback_grader_none'] = 'Aucun';
$string['setting:fallback_grader_placeholder'] = 'Rechercher un utilisateur…';
$string['setting:grading_heading'] = 'Notation et historique';
$string['setting:modules_disable'] = 'Désactiver Autograder pour {$a}';
$string['setting:modules_disabled'] = 'Autograder désactivé pour {$a}.';
$string['setting:modules_enable'] = 'Activer Autograder pour {$a}';
$string['setting:modules_enabled'] = 'Autograder activé pour {$a}.';
$string['setting:modules_enabled_column'] = 'Activé';
$string['setting:modules_heading'] = 'Choisissez les types d’activité qui peuvent utiliser Autograder sur la page <a href="{$a->url}">Types d’activité</a>.';
$string['setting:notifyfailures'] = 'Avertir les enseignants des échecs de notation';
$string['setting:notifyfailures_desc'] = 'Envoie à toutes les personnes inscrites au cours qui peuvent configurer Autograder dans une activité un récapitulatif des étudiants qu’il n’a pas pu y noter, regroupés par activité. Envoyé chaque semaine par défaut ; l’horaire peut être modifié dans les tâches planifiées. Chaque échec n’est signalé qu’une fois, à partir du moment où cette option est activée.';
$string['setting:notifystudent'] = 'Notifier les étudiants';
$string['setting:notifystudent_desc'] = 'Envoie à l’étudiant la notification de notation de Moodle lorsqu’Autograder note un devoir ou un forum, comme si un enseignant avait choisi de notifier en notant. Seuls les devoirs et les forums sont concernés.';
$string['setting:retentiondays'] = 'Conserver l’historique pendant (jours)';
$string['setting:retentiondays_desc'] = 'Les décisions terminées (notées, annulées, en échec…) et les entrées du journal de notation plus anciennes que cette valeur sont supprimées chaque jour par une tâche planifiée. Les décisions en attente ne sont jamais supprimées. Saisissez 0 pour tout conserver.';
$string['setting:teacher_roles'] = 'Rôles d’enseignant sélectionnés';
$string['setting:teacher_roles_desc'] = 'Les rôles qui comptent comme enseignants. Les utilisateurs dont les rôles ne sont pas sélectionnés ici ne sont jamais choisis. Les rôles sélectionnés doivent pouvoir noter les activités sur lesquelles Autograder est utilisé.';
$string['setting:teacher_source_mode'] = 'Rôles d’enseignant';
$string['setting:teacher_source_mode_chosen'] = 'Uniquement les rôles sélectionnés';
$string['setting:teacher_source_mode_desc'] = 'Le mode automatique inclut tous les rôles disposant de moodle/grade:edit ou d’une capacité de notation d’activité (comme mod/assign:grade), de sorte que les nouveaux rôles personnalisés fonctionnent sans configuration supplémentaire.';
$string['setting:teacher_source_mode_grading'] = 'Automatique : tout rôle pouvant noter';
$string['setting:teachers_heading'] = 'Les notes sont enregistrées au nom d’un des enseignants de l’étudiant : les utilisateurs ayant un rôle d’enseignant attribué dans le cours lui-même, et non dans sa catégorie ou sur l’ensemble du site, dont l’inscription au cours est active. Les administrateurs du site et les utilisateurs suspendus ne sont jamais utilisés, pas plus qu’un enseignant qui ne peut pas noter l’activité ou qui ne peut pas voir le groupe de l’étudiant dans une activité à groupes séparés. L’enseignant qui partage le plus de groupes avec l’étudiant est préféré ; si aucun ne partage de groupe, un enseignant qui n’est dans aucun groupe ; et si tous les enseignants sont dans un autre groupe, n’importe lequel d’entre eux. Dans les cours à groupes séparés avec un groupement par défaut, seuls les groupes de ce groupement comptent.';
$string['setting:tiebreak'] = 'Sélection de l’enseignant';
$string['setting:tiebreak_desc'] = 'Lequel est choisi lorsque plusieurs enseignants peuvent noter un étudiant.';
$string['setting:tiebreak_last_course_access'] = 'Accès au cours le plus récent';
$string['setting:tiebreak_lowest_userid'] = 'Identifiant d’utilisateur le plus bas';
$string['settings:generaltab'] = 'Général';
$string['settings:modulestab'] = 'Types d’activité';
$string['settings:teacherstab'] = 'Enseignants';
$string['task:cancel_module'] = 'Annuler la notation Autograder en attente dans une activité';
$string['task:catch_up_module'] = 'Rattraper les étudiants qui attendent déjà dans une activité';
$string['task:grade_student'] = 'Noter un étudiant';
$string['task:notify_failures'] = 'Avertir les enseignants des étudiants qu’Autograder n’a pas pu noter';
$string['task:purge_history'] = 'Purger les décisions Autograder terminées et le journal de notation';
$string['task:recalculate_module'] = 'Recalculer les échéances Autograder d’une activité';
$string['task:reconcile_pending'] = 'Remettre en file les décisions Autograder qui ont perdu leur tâche';
