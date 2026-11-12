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

namespace local_autograder\local;

use local_autograder\local\config\eligibility;

/**
 * Which activity types Autograder can be used with: the ones that take the work of students and have a grade that a teacher
 * gives, found from what each activity plugin declares and not from a list.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\local\config\eligibility
 */
final class eligibility_test extends \advanced_testcase {
    /**
     * The types that take the work of students are supported.
     */
    public function test_the_types_that_take_work_are_supported(): void {
        foreach (['assign', 'forum', 'quiz'] as $modname) {
            $this->assertTrue(eligibility::is_module_type_supported($modname), $modname);
            $this->assertContains($modname, eligibility::supported_module_types());
        }
    }

    /**
     * A type without a grade, or whose grade nobody gives to the work of students, is not.
     */
    public function test_the_other_types_are_not_supported(): void {
        foreach (['label', 'page', 'url', 'scorm', 'lti', 'h5pactivity', 'glossary', 'data'] as $modname) {
            if (!array_key_exists($modname, \core_component::get_plugin_list('mod'))) {
                continue;
            }
            $this->assertFalse(eligibility::is_module_type_supported($modname), $modname);
            $this->assertNotContains($modname, eligibility::supported_module_types());
        }
    }

    /**
     * A name that is not an activity plugin is not supported.
     */
    public function test_a_name_that_is_not_a_plugin_is_not_supported(): void {
        $this->assertFalse(eligibility::is_module_type_supported('notanactivity'));
    }

    /**
     * A type that does not meet the rule cannot be switched on, and one that no longer meets it is not one of the allowed.
     */
    public function test_an_unsupported_type_cannot_be_enabled(): void {
        $this->resetAfterTest();

        $this->expectException(\invalid_parameter_exception::class);
        eligibility::set_module_type_enabled('label', true);
    }

    /**
     * A site whose setting names a type that is not supported does not allow it.
     */
    public function test_the_allowed_types_leave_out_the_unsupported(): void {
        $this->resetAfterTest();

        set_config('enabled_modules', 'assign,label,forum', 'local_autograder');

        $this->assertSame(['assign', 'forum'], eligibility::enabled_module_types());
        $this->assertFalse(eligibility::is_module_type_enabled('label'));
    }

    /**
     * The upgrade takes the unsupported types off the setting and keeps the supported ones in their order.
     */
    public function test_the_upgrade_takes_the_unsupported_types_off(): void {
        $this->resetAfterTest();

        set_config('enabled_modules', 'assign,scorm,quiz', 'local_autograder');

        $this->assertSame(['scorm'], eligibility::withdraw_unsupported_types());
        $this->assertSame('assign,quiz', get_config('local_autograder', 'enabled_modules'));
        $this->assertSame([], eligibility::withdraw_unsupported_types());
    }
}
