import{fetchOne as n}from"@moodle/lms/core/ajax";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the plugin, in one place.
 *
 * @module     local_autograder/services/api
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const s=(e,t)=>n({methodname:"local_autograder_set_module_type",args:{modname:e,enabled:t}}),o=e=>n({methodname:"local_autograder_search_graders",args:{query:e}}),r=e=>e instanceof Error?e.message:e!==null&&typeof e=="object"&&"message"in e?String(e.message):String(e);export{r as describeError,o as searchGraders,s as setModuleType};
