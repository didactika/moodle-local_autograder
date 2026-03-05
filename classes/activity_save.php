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
 * Plugin version and other meta-data are defined here.
 *
 * @package     local_autograder
 * @copyright   2025 ADSDR <eduardo.cubias@ct.uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_autograder;

defined('MOODLE_INTERNAL') || die();
global $CFG;
        require_once($CFG->dirroot . '/local/autograder/lib.php');
        require_once($CFG->dirroot . '/local/autograder/classes/event/autograder_created.php');
        require_once($CFG->dirroot . '/local/autograder/classes/event/autograder_updated.php');

/**
 * Class responsible for saving autograder configuration when module settings are updated.
 *
 * This class hooks into Moodle’s `coursemodule_edit_post_actions` callback
 * to persist the autograder data linked to a given course module (CM).
 */
class activity_save {

    /**
     * Save or update autograder configuration after editing an activity.
     *
     * This method runs when the user submits a module settings form.
     * It normalizes the time fields (days, hours, minutes), calculates
     * the delay in seconds, and inserts or updates the corresponding
     * record in the `local_autograder` table.
     *
     * @param \stdClass $data Form data submitted from module settings.
     * @return \stdClass The same data object, unmodified for Moodle’s further processing.
     * @throws \dml_exception If database access fails.
     */
    public static function coursemodule_edit_post_actions($data) {
        global $DB;

        if (!get_config('local_autograder', 'enable')) {
            return $data;
        }

        $grade_to_save = '';
        if (!empty($data->grade_forum) || !empty($data->grade)) {
            $grade_to_save = local_autograder_grade_to_save($data);
        }

        $days    = max(0, (int)($data->days_to_complete ?? 0));
        $hours   = max(0, (int)($data->hours_to_complete ?? 0));
        $minutes = max(0, (int)($data->minutes_to_complete ?? 0));

        if ($minutes >= 60) {
            $hours   += intdiv($minutes, 60);
            $minutes %= 60;
        }
        if ($hours >= 24) {
            $days  += intdiv($hours, 24);
            $hours %= 24;
        }

        if ($days === 0 && $hours === 0 && $minutes === 0) {
            $days = 2;
        }

        $processing_delay_seconds =
            ($days * DAYSECS) + ($hours * HOURSECS) + ($minutes * MINSECS);

        $instance = $DB->get_record('local_autograder', ['cmid' => $data->coursemodule]);

        $record = new \stdClass();
        $record->enable = !empty($data->autograderenabled);
        $record->gradetoassign =
            ($data->autogradergrade > 0) ? $data->autogradergrade : $grade_to_save;
        $record->processingdelayseconds = $processing_delay_seconds;
        $record->timemodified = time();

        $event_data = [
            'cmid' => $data->coursemodule,
            'enabled' => (bool)$record->enable,
            'grade_to_assign' => $record->gradetoassign,
            'processing_delay_seconds' => $record->processingdelayseconds,
        ];

        if ($instance) {
            $record->id = $instance->id;
            $DB->update_record('local_autograder', $record);
            $event_data['id'] = $instance->id;
            $event = \local_autograder\event\autograder_updated::create([
                'objectid' => $record->id,
                'context' => \context_module::instance($data->coursemodule),
                'other' => $event_data,
            ]);
            $event->trigger();
        } elseif (!empty($data->autograderenabled)) {
            $record->cmid = $data->coursemodule;
            $record->courseid = $data->course;
            $recordid = $DB->insert_record('local_autograder', $record);
            $event_data['id'] = $recordid;
            $event = \local_autograder\event\autograder_created::create([
                'objectid' => $recordid,
                'context' => \context_module::instance($data->coursemodule),
                'other' => $event_data,
            ]);
            $event->trigger();
        }

        return $data;
    }
}
