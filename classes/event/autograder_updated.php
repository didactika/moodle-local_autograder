<?php

/**
 * Event autograder_updated are defined here.
 *
 * @package     local_autograder
 * @copyright   2025 ADSDR <eduardo.cubias@ct.uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autograder\event;

defined('MOODLE_INTERNAL') || die();

class autograder_updated extends \core\event\base {
    protected function init() {
        $this->data['objecttable'] = 'course_modules';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Get event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:autograder_updated', 'local_autograder');
    }
    public function get_description() {
        return "The autograder configuration for course module '{$this->contextinstanceid}' has been updated.";
    }

}
