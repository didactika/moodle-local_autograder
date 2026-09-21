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

use local_autograder\local\grading\advanced_grading;

/**
 * What autograder needs to know about one activity type: when it closes, what
 * exceptions move that date for a given student, and how to post a grade to
 * it the way a teacher would.
 *
 * One subclass per activity type that needs its own answer; {@see generic_adapter} covers everything else through the gradebook.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class module_adapter {
    /**
     * Set for exactly as long as autograder is writing a grade.
     *
     * Posting a grade makes Moodle fire `\core\event\user_graded`, and this
     * plugin listens to that event to notice a *person* grading a student and
     * call off the pending decision. Without this flag autograder's own write
     * would trip its own observer and mark the decision it just completed as
     * manually graded. The observer checks {@see is_writing()} and ignores the
     * event while it is set. Safe as a static because the event is dispatched
     * synchronously, inside the same call.
     *
     * @var bool
     */
    private static bool $writing = false;

    /** @var \stdClass The course module record. */
    protected \cm_info|\stdClass $cm;

    /** @var \stdClass The autograder configuration row for it. */
    protected \stdClass $config;

    /**
     * Binds an adapter to one course module and its autograder configuration.
     *
     * @param \cm_info|\stdClass $cm
     * @param \stdClass $config
     */
    final public function __construct(\cm_info|\stdClass $cm, \stdClass $config) {
        $this->cm = $cm;
        $this->config = $config;
    }

    /**
     * The adapter that knows about this course module's activity type.
     *
     * @param \cm_info|\stdClass $cm A course module record with `modname`.
     * @param \stdClass $config Its `local_autograder_config` row.
     * @return self
     */
    public static function for_cm(\cm_info|\stdClass $cm, \stdClass $config): self {
        $classname = __NAMESPACE__ . '\\' . $cm->modname . '_adapter';

        if (class_exists($classname)) {
            return new $classname($cm, $config);
        }

        return new generic_adapter($cm, $config);
    }

    /**
     * Whether autograder is posting a grade right now.
     *
     * @return bool
     */
    public static function is_writing(): bool {
        return self::$writing;
    }

    /**
     * The activity's own closing instant, or null when it has none.
     *
     * @return int|null
     */
    abstract public function close_date(): ?int;

    /**
     * When this student handed the activity in, or null when they have not —
     * or when the activity has no notion of handing anything in.
     *
     * What autograder counts from where an activity does not track completion
     * — a student who submitted has done the thing, whether or not
     * anyone asked Moodle to tick a completion box for it.
     *
     * @param int $userid
     * @return int|null
     */
    public function submitted_at(int $userid): ?int {
        return null;
    }

    /**
     * The closing instant an exception grants this student personally, or
     * null when no user-level exception applies.
     *
     * @param int $userid
     * @return int|null
     */
    abstract public function user_override_date(int $userid): ?int;

    /**
     * Every closing instant an exception grants one of these groups. The
     * caller (the due-date rule) takes the most permissive.
     *
     * @param int[] $groupids
     * @return int[]
     */
    abstract public function group_override_dates(array $groupids): array;

    /**
     * Posts the configured grade for this student, as this teacher.
     *
     * @param int $userid The student.
     * @param int $graderid The teacher to post it as.
     * @return float The grade actually posted.
     * @throws \moodle_exception When the grade could not be posted.
     */
    final public function write_grade(int $userid, int $graderid): float {
        // Nothing is asked in advance about whether this teacher may post it.
        // The write itself is the answer: it either stores the grade or throws,
        // and the caller then falls back and finally fails. Guessing here only
        // ever refused writes that would have succeeded.
        self::$writing = true;

        try {
            return $this->post_grade($userid, $graderid);
        } finally {
            self::$writing = false;
        }
    }

    /**
     * The adapter's own grade-posting, wrapped by {@see write_grade()}.
     *
     * @param int $userid
     * @param int $graderid
     * @return float The grade actually posted.
     */
    abstract protected function post_grade(int $userid, int $graderid): float;

    /**
     * The grade a person already put on this activity for this student, or
     * null when the only grade there is one nobody set by hand.
     *
     * An overridden grade, or one stamped with a real user in `usermodified`,
     * is a person's. A grade the activity computed for itself —
     * a quiz score off an attempt — is not, and must not stop autograder.
     *
     * @param int $userid
     * @return \grade_grade|null The grade, when a person set it.
     */
    public function existing_person_grade(int $userid): ?\grade_grade {
        $gradeitem = $this->grade_item();

        if (!$gradeitem) {
            return null;
        }

        $grade = \grade_grade::fetch(['itemid' => $gradeitem->id, 'userid' => $userid]);

        if (!$grade || $grade->finalgrade === null) {
            return null;
        }

        if (!empty($grade->overridden)) {
            return $grade;
        }

        return !empty($grade->usermodified) ? $grade : null;
    }

    /**
     * The activity's grade item.
     *
     * @return \grade_item|null
     */
    protected function grade_item(): ?\grade_item {
        global $CFG;

        require_once($CFG->libdir . '/gradelib.php');

        $item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => $this->cm->modname,
            'iteminstance' => $this->cm->instance,
            // Forum's activity grade is item 1; item 0 is post ratings, which
            // autograder must not touch. See eligibility::grade_itemnumber().
            'itemnumber' => \local_autograder\local\config\eligibility::grade_itemnumber($this->cm->modname),
            'courseid' => $this->cm->course,
        ]);

        return $item ?: null;
    }

    /**
     * The activity's own row (`{assign}`, `{quiz}`, …).
     *
     * @return \stdClass|false
     */
    protected function instance() {
        global $DB;

        return $DB->get_record($this->cm->modname, ['id' => $this->cm->instance]);
    }

    /**
     * The configured grade as a plain number, for the methods that post one.
     *
     * @return float
     */
    protected function configured_grade(): float {
        return (float) $this->config->gradevalue;
    }

    /**
     * The configured per-criterion selection for an advanced-grading method,
     * already shaped the way `gradingform_instance::submit_and_get_grade()`
     * wants it: `['criteria' => [criterionid => [...]]]`.
     *
     * @return array|null Null when this activity is not advanced-graded.
     */
    protected function configured_advanced_grading(): ?array {
        if (!in_array($this->config->grademethod, ['rubric', 'guide'], true)) {
            return null;
        }

        // A rubric edited since autograder was told what to mark — or one that
        // came in through a restore, which renumbers every criterion — leaves
        // a filling pointing at criteria that no longer exist. Handing that to
        // the grading form would put a wrong number in the gradebook, so this
        // stops and says so instead, which the teacher can act on.
        if (!advanced_grading::filling_is_current($this->cm, $this->config->advancedgrading)) {
            throw new \moodle_exception('error:advancedgradingstale', 'local_autograder');
        }

        return ['criteria' => advanced_grading::decode($this->config->advancedgrading)];
    }

    /**
     * Writes the grade straight into the gradebook as a manual override, as
     * the given teacher.
     *
     * The path for activities with no grading API of their own to go through
     * — and the right one for a quiz, whose grade comes from its attempts and
     * which a teacher changes by overriding it in the gradebook exactly like
     * this.
     *
     * @param int $userid
     * @param int $graderid
     * @return float The grade posted.
     * @throws \moodle_exception
     */
    protected function override_in_gradebook(int $userid, int $graderid): float {
        $gradeitem = $this->grade_item();

        if (!$gradeitem) {
            throw new \moodle_exception('error:nogradeitem', 'local_autograder');
        }

        $grade = $this->configured_grade();
        $posted = $gradeitem->update_final_grade(
            $userid,
            $grade,
            'local_autograder',
            false,
            FORMAT_MOODLE,
            $graderid,
        );

        if (!$posted) {
            throw new \moodle_exception('error:gradewritefailed', 'local_autograder');
        }

        return $grade;
    }
}
