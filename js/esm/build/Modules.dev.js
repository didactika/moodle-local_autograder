var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The activity types that Autograder may be switched on for: the ones that take the work of students and have a grade that a
 * teacher gives, one row and one switch for each. A switch saves the moment it is flipped and says so, and goes back where it was
 * if the server refuses.
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
const SEARCH_FROM = 8;
function Modules({ types, strings }) {
  const text = useMemo(() => createText(strings), [strings]);
  const [state, setState] = useState(
    () => Object.fromEntries(types.map((item) => [item.type, item.enabled]))
  );
  const [busy, setBusy] = useState("");
  const [filter, setFilter] = useState("all");
  const [query, setQuery] = useState("");
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
  const onCount = types.filter((item) => state[item.type]).length;
  const counts = { all: types.length, on: onCount, off: types.length - onCount };
  const needle = query.trim().toLowerCase();
  const shown = types.filter((item) => {
    const on = state[item.type];
    return (filter === "all" || filter === "on" === on) && (needle === "" || item.name.toLowerCase().includes(needle));
  });
  return /* @__PURE__ */ jsxDEV("div", { className: "lag", children: [
    /* @__PURE__ */ jsxDEV("div", { className: "lag-head", children: [
      /* @__PURE__ */ jsxDEV("span", { className: "lag-tile", "aria-hidden": "true", children: /* @__PURE__ */ jsxDEV("svg", { viewBox: "0 0 24 24", width: "22", height: "22", fill: "currentColor", children: /* @__PURE__ */ jsxDEV("path", { d: "M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z" }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 65,
        columnNumber: 85
      }, this) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 65,
        columnNumber: 17
      }, this) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 64,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("p", { className: "lag-lead", children: text("modules:lead") }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 67,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 63,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "lag-chips", role: "group", "aria-label": text("settings:modulestab"), children: ["all", "on", "off"].map((name) => /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: `lag-chip${filter === name ? " lag-chip-active" : ""}`,
        "aria-pressed": filter === name,
        onClick: () => setFilter(name),
        children: [
          text(`modules:filter_${name}`),
          " ",
          /* @__PURE__ */ jsxDEV("span", { className: "lag-chip-count", children: counts[name] }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 78,
            columnNumber: 50
          }, this)
        ]
      },
      name,
      true,
      {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 71,
        columnNumber: 63
      },
      this
    )) }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 70,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "lag-card", children: [
      types.length >= SEARCH_FROM && /* @__PURE__ */ jsxDEV("div", { className: "lag-search", children: /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "search",
          className: "lag-search-field",
          value: query,
          placeholder: text("modules:search"),
          "aria-label": text("modules:search"),
          onChange: (event) => setQuery(event.target.value)
        },
        void 0,
        false,
        {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 84,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 83,
        columnNumber: 45
      }, this),
      /* @__PURE__ */ jsxDEV("ul", { className: "lag-types", "aria-label": text("settings:modulestab"), children: [
        shown.map((item) => {
          const on = state[item.type];
          const label = text(on ? "setting:modules_disable" : "setting:modules_enable", item.name);
          return /* @__PURE__ */ jsxDEV("li", { className: `lag-type${on ? " lag-type-on" : ""}`, children: [
            /* @__PURE__ */ jsxDEV("span", { className: "lag-type-tile", children: /* @__PURE__ */ jsxDEV("img", { className: "lag-type-icon", src: item.iconurl, alt: "", width: 24, height: 24 }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 98,
              columnNumber: 57
            }, this) }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 98,
              columnNumber: 25
            }, this),
            /* @__PURE__ */ jsxDEV("span", { className: "lag-type-text", children: [
              /* @__PURE__ */ jsxDEV("span", { className: "lag-type-name", children: item.name }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 100,
                columnNumber: 29
              }, this),
              /* @__PURE__ */ jsxDEV("span", { className: "lag-type-reads", children: text(`modules:reads_${item.reads}`) || text("modules:reads_completion") }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 101,
                columnNumber: 29
              }, this)
            ] }, void 0, true, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 99,
              columnNumber: 25
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
                  lineNumber: 113,
                  columnNumber: 29
                }, this)
              },
              void 0,
              false,
              {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 103,
                columnNumber: 25
              },
              this
            )
          ] }, item.type, true, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 97,
            columnNumber: 28
          }, this);
        }),
        shown.length === 0 && /* @__PURE__ */ jsxDEV("li", { className: "lag-empty", children: text("modules:empty") }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 117,
          columnNumber: 40
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 93,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "lag-foot", children: text("modules:count", types.length) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 119,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 82,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "lag-note", children: text("modules:intro") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 122,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
    lineNumber: 62,
    columnNumber: 12
  }, this);
}
__name(Modules, "Modules");
export {
  Modules as default
};
//# sourceMappingURL=Modules.dev.js.map
