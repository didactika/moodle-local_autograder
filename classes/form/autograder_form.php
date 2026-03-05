<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form validation and helper functions for local_autograder plugin.
 *
 * Defines validation callbacks and form setup for autograder configuration
 * within supported Moodle activities (assign, forum, quiz).
 *
 * @package    local_autograder
 */

defined('MOODLE_INTERNAL') || die();


/**
 * Validation rule: requires completion tracking when autograder is enabled.
 *
 * Ensures that the module's completion tracking is active
 * if the autograder feature is enabled for the activity.
 *
 * @param mixed $data Submitted form data (unused).
 * @param object $objChecker Object containing mform and formwrapper references.
 * @return bool True if valid, false otherwise.
 */
    function local_autograder_check_activity_completion($_, $objChecker) {
        $values = $objChecker->mform->getSubmitValues();
        if (empty($values['autograderenabled'])) {return true;}
        if (empty($values['completion']) || $values['completion'] == 0) {return false;}
        return true;
    }


/**
 * Validation rule: only allow "point" grading type when autograder is enabled.
 *
 * Verifies that the activity uses a point-based grading scheme.
 * This prevents enabling autograder for scales or custom grading methods.
 *
 * @param mixed $data Submitted form data (unused).
 * @param object $objChecker Object containing mform and formwrapper references.
 * @return bool True if the grade type is valid, false otherwise.
 */
    function local_autograder_only_point_grade($_, $objChecker) {
        $submitValues = $objChecker->mform->getSubmitValues();

        if (empty($submitValues['autograderenabled'])) {
            return true;
        }

        $current = $objChecker->formwrapper->get_current();
        $moduleName = $current->modulename;

        if ($moduleName === 'quiz') {
            return true;
        }

        if ($moduleName === 'forum') {
            $gradeType = $submitValues['grade_forum']['modgrade_type'] ?? null;
        } else {
            $gradeType = $submitValues['grade']['modgrade_type'] ?? null;
        }

        if ($gradeType === null || $gradeType === 'none') {
            return false;
        }

        return $gradeType === 'point';
    }


/**
 * Validation rule: ensures autograder grade does not exceed the module’s maximum.
 *
 * Compares the autograder's configured grade against the maximum allowed
 * grade defined in the activity settings.
 *
 * @param mixed $data Submitted grade value.
 * @param object $objChecker Object containing mform and formwrapper references.
 * @return bool True if within allowed limits, false otherwise.
 */
    function local_autograder_max_score_checker($data, $objChecker) {
        global $DB;

        $submitValues = $objChecker->mform->getSubmitValues();
        if (empty($submitValues['autograderenabled'])) {
            return true;
        }

        $current = $objChecker->formwrapper->get_current();
        $moduleName = $current->modulename;
        $maxGrade = null;

        if ($moduleName === 'quiz') {
            if (!empty($current->instance)) {
                $quiz = $DB->get_record('quiz', ['id' => $current->instance], 'grade');
                if ($quiz) {
                    $maxGrade = $quiz->grade;
                }
            }

            if ($maxGrade === null) {
                return true;
            }
        } else {
            if ($moduleName === 'assign') {
                $gradeField = 'grade';
            } else if ($moduleName === 'forum') {
                $gradeField = 'grade_forum';
            } else {
                return true;
            }

            if (isset($submitValues[$gradeField]['modgrade_point'])) {
                $maxGrade = $submitValues[$gradeField]['modgrade_point'];
            } else if (isset($current->{$gradeField})) {
                $maxGrade = $current->{$gradeField};
            }
        }

        if ($maxGrade === null) {
            return false;
        }

        return $data <= $maxGrade;
    }



/**
 * Retrieve default configuration values for autograder form fields.
 *
 * Loads existing configuration from the database if available,
 * otherwise uses global plugin defaults. Calculates the time delay
 * in days, hours, and minutes from the stored seconds value.
 *
 * @param int $cmid Course module ID.
 * @return array Associative array with keys: days, hours, minutes, grade, instance.
 * @throws dml_exception If database access fails.
 */
function local_autograder_get_default_values($cmid) {
    global $DB;

    $global_days = (int)(get_config('local_autograder', 'daysToComplete') ?: 2);
    $global_hours = (int)(get_config('local_autograder', 'hoursToComplete') ?: 0);
    $global_minutes = (int)(get_config('local_autograder', 'minutesToComplete') ?: 0);
    $global_grade = (int)(get_config('local_autograder', 'defaultGrade') ?: 10);

    $defaults = [
        'days' => $global_days,
        'hours' => $global_hours,
        'minutes' => $global_minutes,
        'grade' => $global_grade,
        'instance' => null
    ];
    $instance = $DB->get_record('local_autograder', ['cmid' => $cmid]);

    if (!$instance) {
        return $defaults;
    }

    $defaults['instance'] = $instance;
    $delay = (int)$instance->processingdelayseconds;

    if ($delay > 0) {
        $defaults['days'] = floor($delay / 86400);
        $delay %= 86400;
        $defaults['hours'] = floor($delay / 3600);
        $delay %= 3600;
        $defaults['minutes'] = floor($delay / 60);
    }
    if (isset($instance->gradetoassign) && $instance->gradetoassign !== '') {
        $defaults['grade'] = (int)$instance->gradetoassign;
    }

    return $defaults;
}




/**
 * Adds the autograder configuration section to module settings forms.
 *
 * This hook inserts form fields such as enable toggle, grade input,
 * and completion delay (days, hours, minutes) into supported module
 * settings forms (assign, forum, quiz). It also registers validation rules.
 *
 * @param moodleform $formwrapper The module form wrapper.
 * @param MoodleQuickForm $mform The Moodle form being built.
 * @return void
 * @throws coding_exception
 * @throws dml_exception
 */
    function local_autograder_coursemodule_standard_elements(moodleform $formwrapper, MoodleQuickForm $mform) {
        global $DB;

        if (!get_config('local_autograder', 'enable')) {
            return;
        }

        $current = $formwrapper->get_current();
        $module_name = $current->modulename;
        $cmid = $current->coursemodule;

        if (!in_array($module_name, ['assign', 'forum', 'quiz'])) {
            return;
        }

        $defaults = local_autograder_get_default_values($cmid);
        $instance = $DB->get_record('local_autograder', ['cmid' => $cmid]);
        $objChecker = (object)['formwrapper' => $formwrapper, 'mform' => $mform];

        $mform->addElement('header', 'autogradersection', get_string('form:heading', 'local_autograder'));

        $mform->addElement('selectyesno', 'autograderenabled', get_string('form:enabled', 'local_autograder'));
        $mform->addHelpButton('autograderenabled', 'form:enabled', 'local_autograder');
        $mform->setDefault('autograderenabled', $instance ? $instance->enable : 0);

        $mform->addElement('text', 'autogradergrade', get_string('form:grade', 'local_autograder'));
        $mform->setType('autogradergrade', PARAM_INT);
        $mform->setDefault('autogradergrade', $defaults['grade']);
        $mform->addHelpButton('autogradergrade', 'form:grade', 'local_autograder');
        $mform->addRule('autogradergrade', get_string('form:error_numeric', 'local_autograder'), 'numeric', null, 'client');
        $mform->disabledIf('autogradergrade', 'autograderenabled', 'neq', '1');
        $mform->hideIf('autogradergrade', 'autograderenabled', 'neq', '1');

        if (in_array($module_name, ['assign', 'forum'])) {
            $mform->registerRule('lessMaxScore', 'callback', 'local_autograder_max_score_checker');
            $mform->addRule('autogradergrade', get_string('form:error_max_grade', 'local_autograder'), 'lessMaxScore', $objChecker, 'server');
        }

        $time_fields = [
            'days_to_complete' => [
                'label' => 'form:days_to_complete',
                'help' => 'form:time_to_complete',
                'default' => $defaults['days'],
                'error' => 'form:error_days_range'
            ],
            'hours_to_complete' => [
                'label' => 'form:hours_to_complete',
                'help' => 'form:time_to_complete',
                'default' => $defaults['hours'],
                'error' => 'form:error_hours_range'
            ],
            'minutes_to_complete' => [
                'label' => 'form:minutes_to_complete',
                'help' => 'form:time_to_complete',
                'default' => $defaults['minutes'],
                'error' => 'form:error_minutes_range'
            ]
        ];

        foreach ($time_fields as $name => $info) {
            $mform->addElement('text', $name, get_string($info['label'], 'local_autograder'));
            $mform->setType($name, PARAM_INT);
            $mform->setDefault($name, $info['default']);
            $mform->addHelpButton($name, $info['help'], 'local_autograder');
            $mform->disabledIf($name, 'autograderenabled', 'neq', '1');
            $mform->hideIf($name, 'autograderenabled', 'neq', '1');
            $mform->addRule($name, null, 'numeric', null, 'client');
            $mform->addRule($name, get_string($info['error'], 'local_autograder'), 'callback', function($value) {
                return $value >= 0;
            });
        }

        $mform->registerRule('activityCompletion', 'callback', 'local_autograder_check_activity_completion');
        $mform->addRule('autograderenabled', get_string('form:error_completion_tracking', 'local_autograder'), 'activityCompletion', $objChecker, 'server');
        $mform->addRule('completion', get_string('form:error_completion_tracking', 'local_autograder'), 'activityCompletion', $objChecker, 'server');

        $mform->registerRule('onlyPointerScore', 'callback', 'local_autograder_only_point_grade');
        $mform->addRule('autograderenabled', get_string('form:error_type_grade', 'local_autograder'), 'onlyPointerScore', $objChecker, 'server');

        if (in_array($module_name, ['assign', 'forum'])) {
            $grade_field = $module_name === 'assign' ? 'grade' : 'grade_forum';
            $mform->addRule($grade_field, get_string('form:error_type_grade', 'local_autograder'), 'onlyPointerScore', $objChecker, 'server');
        }
    }

