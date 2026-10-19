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

namespace local_autograder\external;

use local_autograder\local\config\eligibility;

/**
 * The switch of the page of the activity types.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_autograder\external\set_module_type
 */
final class set_module_type_test extends \advanced_testcase {
    /**
     * A type is switched off and on again, and the answer says the state that it has.
     */
    public function test_a_type_is_switched_off_and_on(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $off = set_module_type::execute('assign', false);
        $off = \core_external\external_api::clean_returnvalue(set_module_type::execute_returns(), $off);
        $this->assertFalse($off['enabled']);
        $this->assertFalse(eligibility::is_module_type_enabled('assign'));

        $on = set_module_type::execute('assign', true);
        $this->assertTrue($on['enabled']);
        $this->assertTrue(eligibility::is_module_type_enabled('assign'));
    }

    /**
     * A type that cannot be graded is refused, so a request cannot write any name into the setting.
     */
    public function test_a_type_that_cannot_be_graded_is_refused(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->expectException(\invalid_parameter_exception::class);
        set_module_type::execute('label', true);
    }

    /**
     * Whoever may not manage Autograder cannot change it.
     */
    public function test_it_needs_the_capability_to_manage(): void {
        $this->resetAfterTest();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->expectException(\required_capability_exception::class);
        set_module_type::execute('assign', false);
    }
}
