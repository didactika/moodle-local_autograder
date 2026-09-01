<?php

/**
 * Event autograder_created are defined here.
 *
 * @package     local_autograder
 * @copyright   2026 Acción Docente SDR <ct.accion.docente@funiber.org>
 * @author      Eduardo Cubias <eduardo.cubias@ct.uneatlantico.es>
 * @author      Hector Arrechea <hector.arrechea@uneatlantico.es>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autograder\event;

defined('MOODLE_INTERNAL') || die();

class autograder_created extends \core\event\base {
    protected function init() {
        $this->data['objecttable'] = 'course_modules';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
    /**
     * Get event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:autograder_created', 'local_autograder');
    }


    public function get_description() {
        return "The autograder configuration for course module '{$this->contextinstanceid}' has been created.";
    }

}
