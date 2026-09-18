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

namespace local_autograder\local\module;

use local_autograder\local\grading\acting_as;

/**
 * Assignment.
 *
 * Graded through `assign::save_grade()` — the same call the grading screen
 * and the `mod_assign_save_grade` web service end up in — so the submission
 * status, the feedback plugins and the gradebook item all move exactly as
 * they would for a teacher grading by hand, including a rubric or marking
 * guide when the activity uses one.
 *
 * That API reads the grader off the global `$USER` (it checks
 * `mod/assign:grade` against it and stamps `$grade->grader` with it).
 * {@see module_adapter::write_grade()} already runs every write as the
 * teacher; this one wraps its own call as well because it is the adapter
 * whose API would grade as the wrong person without it, and it should not
 * rely on its caller to be right.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_adapter extends module_adapter {
    /**
     * The hard close if there is one, otherwise the due date.
     */
    public function close_date(): ?int {
        $assign = $this->instance();

        if (!$assign) {
            return null;
        }

        if (!empty($assign->cutoffdate)) {
            return (int) $assign->cutoffdate;
        }

        return !empty($assign->duedate) ? (int) $assign->duedate : null;
    }

    /**
     * When the student last submitted this assignment.
     *
     * Only a row actually marked submitted counts — a draft the student is
     * still working on is not a hand-in. `latest` picks the current attempt
     * rather than a superseded one.
     *
     * @param int $userid
     * @return int|null
     */
    public function submitted_at(int $userid): ?int {
        global $DB, $CFG;

        // ASSIGN_SUBMISSION_STATUS_SUBMITTED lives in assign's locallib.
        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $submitted = $DB->get_field_sql(
            "SELECT timemodified
               FROM {assign_submission}
              WHERE assignment = :assignment
                AND userid = :userid
                AND status = :status
                AND latest = 1
           ORDER BY timemodified DESC",
            [
                'assignment' => $this->cm->instance,
                'userid' => $userid,
                'status' => ASSIGN_SUBMISSION_STATUS_SUBMITTED,
            ],
            IGNORE_MULTIPLE
        );

        return $submitted ? (int) $submitted : null;
    }

    /**
     * The close date a user override grants this student.
     *
     * @param int $userid
     * @return int|null
     */
    public function user_override_date(int $userid): ?int {
        global $DB;

        $override = $DB->get_record(
            'assign_overrides',
            ['assignid' => $this->cm->instance, 'userid' => $userid],
            'cutoffdate, duedate',
            IGNORE_MULTIPLE,
        );

        return $override ? self::override_close_date($override) : null;
    }

    /**
     * Every close date a group override grants these groups.
     *
     * @param int[] $groupids
     * @return int[]
     */
    public function group_override_dates(array $groupids): array {
        global $DB;

        if (empty($groupids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);
        $params['assignid'] = $this->cm->instance;

        $overrides = $DB->get_records_sql(
            "SELECT id, cutoffdate, duedate
               FROM {assign_overrides}
              WHERE assignid = :assignid AND groupid {$insql}",
            $params,
        );

        $dates = [];

        foreach ($overrides as $override) {
            $date = self::override_close_date($override);

            if ($date !== null) {
                $dates[] = $date;
            }
        }

        return $dates;
    }

    /**
     * One override's effective close date, same precedence as the activity's
     * own: the hard cut-off if it sets one, otherwise the due date.
     *
     * @param \stdClass $override
     * @return int|null
     */
    private static function override_close_date(\stdClass $override): ?int {
        if (!empty($override->cutoffdate)) {
            return (int) $override->cutoffdate;
        }

        return !empty($override->duedate) ? (int) $override->duedate : null;
    }

    /**
     * Grades the assignment as the chosen teacher.
     *
     * @param int $userid
     * @param int $graderid
     * @return float
     * @throws \moodle_exception
     */
    protected function post_grade(int $userid, int $graderid): float {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $context = \context_module::instance($this->cm->id);
        $course = get_course($this->cm->course);
        $advancedgrading = $this->configured_advanced_grading();
        $grade = $this->configured_grade();

        $saved = acting_as::user($graderid, function () use ($context, $course, $userid, $grade, $advancedgrading) {
            $assign = new \assign($context, $this->cm, $course);

            $data = (object) [
                'attemptnumber' => -1,
                'addattempt' => false,
                'applytoall' => false,
                'sendstudentnotifications' => (bool) get_config('local_autograder', 'notifystudent'),
            ];

            // Marking workflow is deliberately left alone: assign only reads
            // `workflowstate` when it is present, and moving it is a
            // teacher's call, not autograder's.
            self::add_empty_feedback($assign, $data);

            if ($advancedgrading !== null) {
                // The rubric/guide filling goes here; assign works the
                // resulting number out from it itself.
                $data->advancedgrading = $advancedgrading;
            } else {
                $data->grade = $grade;
            }

            return $assign->save_grade($userid, $data);
        });

        if (!$saved) {
            throw new \moodle_exception('error:gradewritefailed', 'local_autograder');
        }

        // With a rubric or guide the number is whatever Moodle worked out from
        // the filling, so read back what actually landed rather than assume.
        if ($advancedgrading !== null) {
            $written = $this->existing_grade_value($userid);

            return $written ?? $grade;
        }

        return $grade;
    }

    /**
     * Gives every enabled feedback plugin an empty value of the field it
     * expects, so that none of them sees feedback as having changed.
     *
     * Without this, `assign::save_grade()` reaches each plugin's
     * `is_feedback_modified()` with nothing to read and PHP warns about the
     * missing property — the same rough edge `mod_assign_save_grade` has when
     * called with no `plugindata`. Editor-based feedback plugins all name
     * their field `assignfeedback<type>_editor`; a plugin that uses some other
     * field simply ignores the extra property.
     *
     * @param \assign $assign
     * @param \stdClass $data
     */
    private static function add_empty_feedback(\assign $assign, \stdClass $data): void {
        foreach ($assign->get_feedback_plugins() as $plugin) {
            if (!$plugin->is_enabled() || !$plugin->is_visible()) {
                continue;
            }

            $field = 'assignfeedback' . $plugin->get_type() . '_editor';
            $data->{$field} = ['text' => '', 'format' => FORMAT_HTML];
        }
    }

    /**
     * The number currently sitting in the gradebook for this student.
     *
     * @param int $userid
     * @return float|null
     */
    private function existing_grade_value(int $userid): ?float {
        $gradeitem = $this->grade_item();

        if (!$gradeitem) {
            return null;
        }

        $grade = \grade_grade::fetch(['itemid' => $gradeitem->id, 'userid' => $userid]);

        return ($grade && $grade->finalgrade !== null) ? (float) $grade->finalgrade : null;
    }
}
