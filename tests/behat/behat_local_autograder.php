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

// NOTE: no MOODLE_INTERNAL test here. This file is required by Behat before the Moodle environment is set up.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Steps of the pages of Autograder that are drawn by React.
 *
 * @package     local_autograder
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_autograder extends behat_base {
    /**
     * Open a page of the plugin by its path.
     *
     * @Given /^I open the autograder page "([^"]+)"$/
     * @param string $path Path of the page, such as modules.php.
     */
    public function i_open_the_autograder_page(string $path): void {
        $this->getSession()->visit($this->locate_path('/local/autograder/' . $path));
    }

    /**
     * Wait until the component of the page has drawn itself.
     *
     * @When /^I wait for the autograder page$/
     */
    public function i_wait_for_the_autograder_page(): void {
        $this->getSession()->wait(15000, "document.querySelector('.lag') !== null && document.querySelector('.lag-sk') === null");
    }

    /**
     * Check whether the site allows Autograder for an activity type.
     *
     * @Then /^autograder should (not )?be allowed for the "([^"]+)" activity type$/
     * @param string $not Not empty when it should not be allowed.
     * @param string $modname The activity type.
     */
    public function autograder_should_be_allowed_for(string $not, string $modname): void {
        global $DB;

        // What the page saved in another process is read from the database: this one may hold the setting from before.
        $stored = $DB->get_field('config_plugins', 'value', ['plugin' => 'local_autograder', 'name' => 'enabled_modules']);
        $types = $stored === false ? ['assign', 'forum', 'quiz'] : array_filter(array_map('trim', explode(',', (string) $stored)));
        $allowed = in_array($modname, $types, true);
        if ($allowed === ($not !== '')) {
            throw new \Behat\Mink\Exception\ExpectationException(
                'Autograder is ' . ($allowed ? '' : 'not ') . 'allowed for ' . $modname . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Save a screenshot of the whole page, for looking at it.
     *
     * @Then /^I capture the autograder page as "([^"]+)"$/
     * @param string $name Screenshot name.
     */
    public function i_capture_the_autograder_page(string $name): void {
        global $CFG;
        $file = ($CFG->behat_faildump_path ?? sys_get_temp_dir()) . '/' . clean_param($name, PARAM_ALPHANUMEXT) . '.png';
        file_put_contents($file, $this->getSession()->getScreenshot());
    }
}
