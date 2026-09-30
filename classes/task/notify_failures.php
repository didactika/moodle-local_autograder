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

namespace local_autograder\task;

use local_autograder\local\decision\decision_repository;

/**
 * Tells the teachers of an activity which of its students autograder could
 * not grade.
 *
 * A failed decision says nothing on its own: it fails at whatever moment that
 * student came due, while nobody is looking, and the teacher finds out when
 * somebody asks why they have no grade. This is the one place that says so
 * out loud.
 *
 * Grouped by activity, not by student. A rubric that changed underneath a
 * class of thirty is one problem with one fix, and reporting it as thirty
 * would bury it. And sent to the people enrolled in the course who can
 * configure autograder on the activity — the ones who can act on it — rather
 * than to the teacher a grade would have been recorded as: a failed decision
 * stores no grader, and the failure most worth hearing about is the one where
 * there was none. See {@see self::recipients_for()}.
 *
 * Each failure is reported once. The task remembers how far its last run
 * reached and asks only for what failed after that, so a missed run widens
 * the next one instead of losing what fell in the gap.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notify_failures extends \core\task\scheduled_task {
    /** @var int Activities one message lists before summing up the rest. */
    public const ACTIVITY_LIMIT = 20;

    /**
     * The name shown on the admin's task screens.
     */
    public function get_name(): string {
        return get_string('task:notify_failures', 'local_autograder');
    }

    /**
     * Sends each teacher concerned one summary of what failed since last time.
     */
    public function execute(): void {
        if (!get_config('local_autograder', 'notifyfailures')) {
            return;
        }

        // Bounded above as well as below, and the same instant stored at the
        // end: a decision that fails between this query and the marker being
        // written falls after the marker, and so into the next run, rather
        // than behind it and out of every run.
        //
        // This relies on failures outliving a run. purge_history deletes
        // settled decisions older than the retention period, failed ones
        // included; a retention shorter than this task's interval would purge
        // some before they were ever reported.
        $since = get_config('local_autograder', 'lastnotified');
        $now = time();

        // Never run before, which the setting's callback normally rules out
        // but cannot when the setting was never saved through the admin
        // screens — forced in config.php, say, or set from the command line.
        // Starting the count here is the same answer the callback would give.
        if ($since === false) {
            set_config('lastnotified', $now, 'local_autograder');

            return;
        }

        $since = (int) $since;

        $byrecipient = self::activities_by_recipient(self::failures_between($since, $now));

        foreach ($byrecipient as $userid => $activities) {
            self::send($userid, $activities);
        }

        set_config('lastnotified', $now, 'local_autograder');

        if (!empty($byrecipient)) {
            mtrace('local_autograder: told ' . count($byrecipient) . ' teacher(s) about students autograder could not grade.');
        }
    }

    /**
     * Starts the count afresh whenever the notices are switched on.
     *
     * Without it the first run would ask for everything that ever failed on
     * the site — on one that has been running for months, a first message full
     * of failures nobody can do anything about any more. Switched on, a site
     * hears about what fails from then on.
     *
     * Called by the setting itself, and only when its value actually changed.
     *
     * @param string $fullname The setting's full name, as core passes it.
     */
    public static function setting_changed(string $fullname): void {
        if (get_config('local_autograder', 'notifyfailures')) {
            set_config('lastnotified', time(), 'local_autograder');
        }
    }

    /**
     * How many students failed in each activity, and why, in a time window.
     *
     * Counted in the database rather than loaded and counted here: a rubric
     * broken across a large course is a great many rows and one line of the
     * message.
     *
     * @param int $since Exclusive.
     * @param int $until Inclusive.
     * @return array<int, array<string, int>> cmid => failure reason => students.
     */
    private static function failures_between(int $since, int $until): array {
        global $DB;

        // A recordset, because cmid repeats once per reason and a plain record
        // list keys by its first column.
        $rows = $DB->get_recordset_sql(
            "SELECT cmid, failurereason, COUNT(1) AS total
               FROM {local_autograder_decision}
              WHERE status = :status AND timemodified > :since AND timemodified <= :until
           GROUP BY cmid, failurereason",
            ['status' => decision_repository::STATUS_FAILED, 'since' => $since, 'until' => $until]
        );

        $failures = [];

        foreach ($rows as $row) {
            $failures[(int) $row->cmid][(string) $row->failurereason] = (int) $row->total;
        }

        $rows->close();

        return $failures;
    }

    /**
     * Turns failures per activity into activities per teacher.
     *
     * @param array $failures cmid => failure reason => students, as {@see self::failures_between()} returns them.
     * @return array<int, \stdClass[]> userid => the activities to tell them about.
     */
    private static function activities_by_recipient(array $failures): array {
        $byrecipient = [];

        foreach ($failures as $cmid => $reasons) {
            $cm = get_coursemodule_from_id('', $cmid, 0, false, IGNORE_MISSING);

            if (!$cm) {
                continue;
            }

            $context = \context_module::instance($cmid);
            $recipients = self::recipients_for($context);

            if (empty($recipients)) {
                continue;
            }

            $activity = (object) [
                'cm' => $cm,
                'context' => $context,
                'reasons' => $reasons,
            ];

            foreach ($recipients as $userid) {
                $byrecipient[$userid][] = $activity;
            }
        }

        return $byrecipient;
    }

    /**
     * Who is told about an activity: the people enrolled in its course who can
     * fix it.
     *
     * Able to fix it means holding `local/autograder:configure` on the
     * activity — switching autograder off there, or reaching the page where
     * its rubric levels are chosen — through whatever role. Enrolled means an
     * active enrolment in the course: a site manager enrolled in the course is
     * one of its people, and is told like anyone else.
     *
     * What is left out is everybody whose only claim is a role held above the
     * course. A manager of the site or of a category holds the capability in
     * every course beneath them, and would otherwise be sent every failure on
     * the site each week; that view is a report's job, not a message's.
     *
     * Site administrators are measured the same way. Core's enrolled-users
     * query resolves the capability on roles alone, so the blanket yes an
     * administrator gets from has_capability() does not count here — only
     * what their roles allow.
     *
     * @param \context_module $context
     * @return int[] User ids.
     */
    private static function recipients_for(\context_module $context): array {
        return array_map('intval', array_keys(get_enrolled_users(
            $context,
            'local/autograder:configure',
            0,
            'u.id',
            null,
            0,
            0,
            true
        )));
    }

    /**
     * Sends one teacher one message covering all of their activities.
     *
     * @param int $userid
     * @param \stdClass[] $activities
     */
    private static function send(int $userid, array $activities): void {
        $user = \core_user::get_user($userid);

        if (!$user || !empty($user->deleted) || !empty($user->suspended)) {
            return;
        }

        $shown = array_slice($activities, 0, self::ACTIVITY_LIMIT);
        $hidden = count($activities) - count($shown);

        $text = [get_string('notify:intro', 'local_autograder'), ''];
        $html = ['<p>' . get_string('notify:intro', 'local_autograder') . '</p>', '<ul>'];

        foreach ($shown as $activity) {
            $course = get_course((int) $activity->cm->course);
            $coursecontext = \context_course::instance((int) $course->id);
            $url = new \moodle_url('/course/modedit.php', ['update' => $activity->cm->id]);

            $name = format_string($activity->cm->name, true, ['context' => $activity->context]);
            $coursename = format_string($course->fullname, true, ['context' => $coursecontext]);
            $plainname = format_string($activity->cm->name, true, ['context' => $activity->context, 'escape' => false]);
            $plaincourse = format_string($course->fullname, true, ['context' => $coursecontext, 'escape' => false]);

            $text[] = "{$plainname} ({$plaincourse})";
            $html[] = '<li>' . \html_writer::link($url, $name) . ' (' . $coursename . ')<ul>';

            foreach ($activity->reasons as $reason => $count) {
                $line = get_string('notify:reason_line', 'local_autograder', (object) [
                    'count' => $count,
                    'reason' => self::reason($reason),
                ]);

                $text[] = '  ' . $line;
                $html[] = '<li>' . $line . '</li>';
            }

            $text[] = '  ' . $url->out(false);
            $text[] = '';
            $html[] = '</ul></li>';
        }

        $html[] = '</ul>';

        if ($hidden > 0) {
            $more = get_string('notify:more', 'local_autograder', $hidden);
            $text[] = $more;
            $html[] = '<p>' . $more . '</p>';
        }

        $footer = get_string('notify:footer', 'local_autograder');
        $text[] = '';
        $text[] = $footer;
        $html[] = '<p>' . $footer . '</p>';

        $message = new \core\message\message();
        $message->component = 'local_autograder';
        $message->name = 'failuredigest';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $user;
        $message->subject = get_string('notify:subject', 'local_autograder');
        $message->fullmessage = implode("\n", $text);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = implode("\n", $html);
        $message->smallmessage = get_string('notify:small', 'local_autograder', count($activities));
        $message->notification = 1;

        message_send($message);
    }

    /**
     * What a failure reason means, in words a teacher can act on.
     *
     * @param string $reason As stored on the decision.
     * @return string
     */
    private static function reason(string $reason): string {
        $key = 'notify:reason_' . $reason;

        return get_string_manager()->string_exists($key, 'local_autograder')
            ? get_string($key, 'local_autograder')
            : get_string('notify:reason_other', 'local_autograder');
    }
}
