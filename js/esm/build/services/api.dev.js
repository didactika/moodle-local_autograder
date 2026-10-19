var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the plugin, in one place.
 *
 * @module     local_autograder/services/api
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { fetchOne } from "@moodle/lms/core/ajax";
const setModuleType = /* @__PURE__ */ __name((modname, enabled) => fetchOne({ methodname: "local_autograder_set_module_type", args: { modname, enabled } }), "setModuleType");
const searchGraders = /* @__PURE__ */ __name((query) => fetchOne({ methodname: "local_autograder_search_graders", args: { query } }), "searchGraders");
const describeError = /* @__PURE__ */ __name((error) => {
  if (error instanceof Error) {
    return error.message;
  }
  if (error !== null && typeof error === "object" && "message" in error) {
    return String(error.message);
  }
  return String(error);
}, "describeError");
export {
  describeError,
  searchGraders,
  setModuleType
};
//# sourceMappingURL=api.dev.js.map
