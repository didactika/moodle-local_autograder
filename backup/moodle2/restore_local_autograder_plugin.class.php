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
 * Puts an activity's autograder settings back, and asks autograder to look at
 * the restored activity afresh.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_autograder\local\config\config_repository;
use local_autograder\local\config\eligibility;
use local_autograder\local\grading\advanced_grading;
use local_autograder\task\catch_up_module;

/**
 * Restores the configuration, then leaves the decisions to be worked out from
 * the restored course rather than copied from the old one.
 *
 * The grading method is re-read from the activity that actually exists here
 * rather than trusted from the backup, because a restore can land in a site
 * whose scales differ.
 *
 * A rubric or marking guide filling is translated, not trusted and not
 * dropped. Moodle restores the grading form along with the activity and
 * renumbers every criterion and level on the way, so the stored selection
 * points at ids belonging to the site the backup came from — but core records
 * what became what, and {@see self::remapped_filling()} reads that back.
 *
 * Whatever cannot be carried over honestly leaves the activity switched off
 * rather than enabled with nothing to grade with. Autograder is never restored
 * in a state its own form would refuse to save.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_autograder_plugin extends restore_local_plugin {
    /**
     * The backed-up configuration, held until the restored activity is
     * complete enough to read its real grading method back off it.
     *
     * @var \stdClass|null
     */
    protected ?\stdClass $pending = null;

    /**
     * The paths this plugin claims inside an activity.
     *
     * @return restore_path_element[]
     */
    protected function define_module_plugin_structure() {
        return [
            new restore_path_element('autograder_config', $this->get_pathfor('/autograder_config')),
        ];
    }

    /**
     * Holds one activity's configuration until the activity is finished.
     *
     * @param array|object $data
     */
    public function process_autograder_config($data) {
        $this->pending = (object) $data;
    }

    /**
     * Writes the configuration against the restored activity, then has
     * autograder decide what it owes the students who came across with it.
     *
     * By this point the activity, its grade item and its grading form all
     * exist, which is what makes reading the real grading method back off it
     * possible at all.
     */
    public function after_restore_module() {
        if ($this->pending === null) {
            return;
        }

        $data = $this->pending;
        $this->pending = null;

        $cmid = (int) $this->task->get_moduleid();

        if ($cmid === 0) {
            return;
        }

        $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

        if (!$cm || !eligibility::is_module_type_enabled($cm->modname)) {
            // The activity type is not one this site allows autograder on.
            return;
        }

        $grademethod = eligibility::grademethod_for($cm);

        if ($grademethod === null) {
            // It came back not graded at all; a configuration that can never
            // fire is worse than none.
            return;
        }

        $advanced = in_array($grademethod, ['rubric', 'guide'], true);
        $advancedgrading = $advanced
            ? $this->remapped_filling($cm, $grademethod, $data->advancedgrading ?? null)
            : null;
        $gradevalue = $advanced
            ? null
            : (isset($data->gradevalue) ? (float) $data->gradevalue : null);

        // Switched on only where there is something to grade with. An activity
        // whose filling could not be carried over, or whose grade did not come
        // across, would otherwise come back enabled and fail once per student
        // at whatever moment each of them came due — with nothing on screen
        // beforehand to say why. Off, the teacher meets the form's own notice
        // and fills the gap before turning it on.
        $enabled = !empty($data->enabled) && ($advanced ? $advancedgrading !== null : $gradevalue !== null);

        config_repository::upsert_for_cm(
            $cmid,
            (int) $cm->course,
            $enabled,
            $grademethod,
            $gradevalue,
            $advancedgrading,
            (int) ($data->delayseconds ?? 0),
            (int) $this->get_mappingid('user', $data->usermodified, $this->task->get_userid())
        );

        if (!$enabled) {
            return;
        }

        $task = new catch_up_module();
        $task->set_custom_data((object) ['cmid' => $cmid]);

        // Adhoc rather than inline: a restore should not wait on a class-sized
        // sweep, and the enrolments this reads may still be arriving.
        \core\task\manager::queue_adhoc_task($task, true);
    }

    /**
     * The backed-up per-criterion selection, with every id translated to the
     * one the restored grading form actually uses.
     *
     * Core renumbers a rubric or guide as it restores it and records what
     * became what — `gradingform_rubric_criterion`, `gradingform_rubric_level`
     * and `gradingform_guide_criterion`. Those mappings are written by the
     * activity's grading step, which has already run by the time this is
     * asked: a restore task launches its `after_restore_*` methods only once
     * every one of its steps has executed.
     *
     * All or nothing. A selection missing one criterion is not a partial
     * answer but a wrong one — it would grade against part of a rubric and
     * post whatever that came to — so anything unmappable discards the lot and
     * leaves the teacher to fill it in again.
     *
     * @param \cm_info|\stdClass $cm
     * @param string $grademethod Either "rubric" or "guide".
     * @param string|null $json The filling as it was backed up.
     * @return string|null JSON to store, or null when it cannot be trusted.
     */
    protected function remapped_filling($cm, string $grademethod, ?string $json): ?string {
        $filling = advanced_grading::decode($json);

        if ($filling === []) {
            return null;
        }

        $remapped = [];

        foreach ($filling as $criterionid => $answer) {
            $newcriterionid = $this->get_mappingid(
                'gradingform_' . $grademethod . '_criterion',
                (int) $criterionid
            );

            if (!$newcriterionid) {
                return null;
            }

            // Rubrics answer with a level; guides answer with a score, which
            // is a number rather than a row of its own and so needs no
            // translating.
            if (isset($answer['levelid'])) {
                $newlevelid = $this->get_mappingid(
                    'gradingform_' . $grademethod . '_level',
                    (int) $answer['levelid']
                );

                if (!$newlevelid) {
                    return null;
                }

                $answer['levelid'] = (int) $newlevelid;
            }

            $remapped[(int) $newcriterionid] = $answer;
        }

        $encoded = advanced_grading::encode($remapped);

        // Checked against the form that actually came back, by the same rule
        // that guards every grade: a translation that does not fit it is no
        // better than the untranslated one.
        return advanced_grading::filling_is_current($cm, $encoded) ? $encoded : null;
    }
}
