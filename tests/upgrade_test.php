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

namespace local_autograder;

use local_autograder\local\config_repository;

/**
 * The handover from the external service to this plugin.
 *
 * A site upgrading from v2 keeps every activity's settings but has no
 * decisions at all, because decisions are worked out from Moodle's own data
 * rather than copied across. Something has to ask for them, or the upgrade
 * would look like a success and grade nobody.
 *
 * @package     local_autograder
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      ::upgrade_local_autograder_catch_up_everything
 */
final class upgrade_test extends \advanced_testcase {
    /**
     * Loads the upgrade steps, which are not autoloaded.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();

        require_once($CFG->dirroot . '/local/autograder/db/upgrade.php');

        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Every activity autograder is switched on for gets asked about, and the
     * ones it is switched off for do not.
     */
    public function test_the_handover_asks_about_every_enabled_activity(): void {
        $enabled = $this->autograded_activity(true);
        $disabled = $this->autograded_activity(false);

        $this->expectOutputRegex('/queued a catch-up for 1 activity/');
        upgrade_local_autograder_catch_up_everything();

        $cmids = $this->queued_cmids();

        $this->assertContains((int) $enabled->id, $cmids);
        $this->assertNotContains((int) $disabled->id, $cmids);
    }

    /**
     * Running it twice does not queue the work twice — an upgrade that is
     * retried, or a site upgraded through several versions at once, must not
     * end up sweeping the same class over and over.
     */
    public function test_the_handover_can_be_run_again(): void {
        $this->autograded_activity(true);

        $this->expectOutputRegex('/queued a catch-up/');
        upgrade_local_autograder_catch_up_everything();
        $first = $this->queued_cmids();

        upgrade_local_autograder_catch_up_everything();

        $this->assertSame($first, $this->queued_cmids());
    }

    /**
     * A site with nothing switched on says nothing and queues nothing.
     */
    public function test_nothing_switched_on_means_nothing_queued(): void {
        upgrade_local_autograder_catch_up_everything();

        $this->assertSame([], $this->queued_cmids());
    }

    /**
     * The activity ids the catch-up has been queued for.
     *
     * @return int[]
     */
    private function queued_cmids(): array {
        global $DB;

        $tasks = $DB->get_records_select(
            'task_adhoc',
            $DB->sql_like('classname', ':classname'),
            ['classname' => '%catch_up_module%']
        );
        $cmids = array_map(function ($task) {
            return (int) json_decode($task->customdata)->cmid;
        }, array_values($tasks));

        sort($cmids);

        return $cmids;
    }

    /**
     * An assignment with autograder switched on, or off.
     *
     * @param bool $enabled
     * @return \stdClass The course module.
     */
    private function autograded_activity(bool $enabled): \stdClass {
        $course = $this->getDataGenerator()->create_course();
        $assign = $this->getDataGenerator()->create_module('assign', [
            'course' => $course->id,
            'grade' => 100,
        ]);
        $cm = get_coursemodule_from_instance('assign', $assign->id, $course->id, false, MUST_EXIST);

        config_repository::upsert_for_cm(
            (int) $cm->id,
            (int) $course->id,
            $enabled,
            'point',
            70.0,
            null,
            DAYSECS,
            2
        );

        return $cm;
    }
}
