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

namespace local_autograder\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Which level of each rubric criterion — or which score on each marking-guide
 * criterion — autograder marks.
 *
 * The activity's settings form cannot host this: picking "Rubric" there does
 * not create a rubric, it only says one will be used, and the definition is
 * written afterwards on Moodle's own advanced grading page. This form is
 * reached once that definition exists.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advanced_grading_form extends \moodleform {
    /**
     * Builds one row per criterion: a level picker for a rubric, a score and
     * a comment for a marking guide.
     */
    protected function definition() {
        $mform = $this->_form;
        $criteria = $this->_customdata['criteria'];

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        foreach ($criteria as $criterionid => $criterion) {
            $mform->addElement(
                'static',
                "criterion_{$criterionid}_description",
                '',
                \html_writer::tag('strong', format_text($criterion['description'], FORMAT_HTML)),
            );

            if (!empty($criterion['levels'])) {
                $options = [];

                foreach ($criterion['levels'] as $levelid => $level) {
                    $options[$levelid] = trim(
                        format_string($level['definition']) . ' (' . format_float($level['score'], -1) . ')'
                    );
                }

                $mform->addElement(
                    'select',
                    "criterion_{$criterionid}_levelid",
                    get_string('advanced:level', 'local_autograder'),
                    $options,
                );

                continue;
            }

            $mform->addElement(
                'text',
                "criterion_{$criterionid}_score",
                get_string('advanced:score', 'local_autograder', format_float($criterion['maxscore'], -1)),
            );
            $mform->setType("criterion_{$criterionid}_score", PARAM_FLOAT);

            $mform->addElement(
                'textarea',
                "criterion_{$criterionid}_remark",
                get_string('advanced:remark', 'local_autograder'),
                ['rows' => 2, 'cols' => 50],
            );
            $mform->setType("criterion_{$criterionid}_remark", PARAM_TEXT);
        }

        $this->add_action_buttons();
    }

    /**
     * Refuses a score above what the criterion allows.
     *
     * @param array $data
     * @param array $files
     * @return array<string, string>
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        foreach ($this->_customdata['criteria'] as $criterionid => $criterion) {
            if (!empty($criterion['levels'])) {
                continue;
            }

            $field = "criterion_{$criterionid}_score";
            $score = (float) ($data[$field] ?? 0);

            if ($score < 0 || $score > $criterion['maxscore']) {
                $errors[$field] = get_string(
                    'advanced:error_score_range',
                    'local_autograder',
                    format_float($criterion['maxscore'], -1),
                );
            }
        }

        return $errors;
    }

    /**
     * Turns a stored filling into the flat field values this form shows.
     *
     * @param array $criteria From {@see \local_autograder\local\advanced_grading::criteria()}.
     * @param array $filling From {@see \local_autograder\local\advanced_grading::decode()}.
     * @param int $cmid
     * @return array
     */
    public static function values_from_filling(array $criteria, array $filling, int $cmid): array {
        $values = ['cmid' => $cmid];

        foreach ($criteria as $criterionid => $criterion) {
            $answer = $filling[$criterionid] ?? [];

            if (!empty($criterion['levels'])) {
                if (isset($answer['levelid'])) {
                    $values["criterion_{$criterionid}_levelid"] = (int) $answer['levelid'];
                }

                continue;
            }

            $values["criterion_{$criterionid}_score"] = $answer['score'] ?? 0;
            $values["criterion_{$criterionid}_remark"] = $answer['remark'] ?? '';
        }

        return $values;
    }

    /**
     * Turns this form's submission back into a filling to store.
     *
     * @param array $criteria
     * @param \stdClass $data
     * @return array<int, array<string, mixed>>
     */
    public static function filling_from_submission(array $criteria, \stdClass $data): array {
        $filling = [];

        foreach ($criteria as $criterionid => $criterion) {
            if (!empty($criterion['levels'])) {
                $field = "criterion_{$criterionid}_levelid";

                if (isset($data->{$field})) {
                    $filling[$criterionid] = ['levelid' => (int) $data->{$field}];
                }

                continue;
            }

            $filling[$criterionid] = [
                'score' => (float) ($data->{"criterion_{$criterionid}_score"} ?? 0),
                'remark' => (string) ($data->{"criterion_{$criterionid}_remark"} ?? ''),
            ];
        }

        return $filling;
    }
}
