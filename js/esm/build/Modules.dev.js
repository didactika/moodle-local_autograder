var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The activity types that Autograder may be switched on for: one row and one switch for each gradeable type. A switch saves the
 * moment it is flipped and says so, and goes back where it was if the server refuses.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useMemo, useState } from "react";
import { createText } from "./engine/text";
import { addToast } from "./engine/toast";
import { describeError, setModuleType } from "./services/api";
function Modules({ types, strings }) {
  const text = useMemo(() => createText(strings), [strings]);
  const [state, setState] = useState(
    () => Object.fromEntries(types.map((item) => [item.type, item.enabled]))
  );
  const [busy, setBusy] = useState("");
  const flip = /* @__PURE__ */ __name((type, name, enabled) => {
    setBusy(type);
    setState((before) => ({ ...before, [type]: enabled }));
    setModuleType(type, enabled).then((result) => {
      setState((before) => ({ ...before, [type]: result.enabled }));
      return addToast(text(result.enabled ? "setting:modules_enabled" : "setting:modules_disabled", name), { type: "success" });
    }).catch((error) => {
      setState((before) => ({ ...before, [type]: !enabled }));
      return addToast(text("modules:failed", describeError(error)), { type: "danger" });
    }).finally(() => setBusy(""));
  }, "flip");
  return /* @__PURE__ */ jsxDEV("div", { className: "lag", children: [
    /* @__PURE__ */ jsxDEV("p", { className: "lag-intro", children: text("modules:intro") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 47,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("ul", { className: "lag-types", "aria-label": text("settings:modulestab"), children: types.map((item) => {
      const on = state[item.type];
      const label = text(on ? "setting:modules_disable" : "setting:modules_enable", item.name);
      return /* @__PURE__ */ jsxDEV("li", { className: `lag-type${on ? " lag-type-on" : ""}`, children: [
        /* @__PURE__ */ jsxDEV("img", { className: "lag-type-icon", src: item.iconurl, alt: "", width: 28, height: 28 }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 53,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV("span", { className: "lag-type-name", children: item.name }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 54,
          columnNumber: 21
        }, this),
        /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            role: "switch",
            className: "lag-switch",
            "aria-checked": on,
            "aria-label": label,
            title: label,
            disabled: busy === item.type,
            onClick: () => flip(item.type, item.name, !on),
            children: /* @__PURE__ */ jsxDEV("span", { className: "lag-switch-knob" }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 65,
              columnNumber: 25
            }, this)
          },
          void 0,
          false,
          {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 55,
            columnNumber: 21
          },
          this
        )
      ] }, item.type, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 52,
        columnNumber: 24
      }, this);
    }) }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 48,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
    lineNumber: 46,
    columnNumber: 12
  }, this);
}
__name(Modules, "Modules");
export {
  Modules as default
};
//# sourceMappingURL=Modules.dev.js.map
