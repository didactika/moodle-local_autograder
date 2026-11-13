var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The page of the activity types: the types that take the work of students and have a grade that a teacher gives, each with the icon
 * and the colour that Moodle gives its purpose, what Autograder reads in it and a switch that saves the moment it is flipped.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes. It is written with the classes of Bootstrap
 * and Moodle (card, table, form-switch, btn-group, activityiconcontainer); the styles only adjust them inside the page.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useId, useMemo, useState } from "react";
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
  const searchId = useId();
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
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted mb-3", children: text("modules:lead") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 64,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
      /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar", children: [
        types.length >= SEARCH_FROM && /* @__PURE__ */ jsxDEV("div", { className: "input-group", children: [
          /* @__PURE__ */ jsxDEV("label", { className: "visually-hidden sr-only", htmlFor: searchId, children: text("modules:search") }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 68,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV(
            "input",
            {
              id: searchId,
              type: "search",
              className: "form-control",
              value: query,
              placeholder: `${text("modules:search")}\u2026`,
              autoComplete: "off",
              onChange: (event) => setQuery(event.target.value)
            },
            void 0,
            false,
            {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 69,
              columnNumber: 21
            },
            this
          )
        ] }, void 0, true, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 67,
          columnNumber: 49
        }, this),
        /* @__PURE__ */ jsxDEV("div", { className: "btn-group", role: "group", "aria-label": text("settings:modulestab"), children: ["all", "on", "off"].map((name) => /* @__PURE__ */ jsxDEV(
          "button",
          {
            type: "button",
            className: `btn btn-outline-secondary btn-sm${filter === name ? " active" : ""}`,
            "aria-pressed": filter === name,
            onClick: () => setFilter(name),
            children: [
              text(`modules:filter_${name}`),
              " ",
              /* @__PURE__ */ jsxDEV("span", { className: "badge badge-secondary bg-secondary rounded-pill", children: counts[name] }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 87,
                columnNumber: 58
              }, this)
            ]
          },
          name,
          true,
          {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 80,
            columnNumber: 71
          },
          this
        )) }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 79,
          columnNumber: 17
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 66,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "table-responsive", children: [
        /* @__PURE__ */ jsxDEV("table", { className: "table", children: [
          /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden sr-only", children: text("settings:modulestab") }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 93,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("modules:name") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 96,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("modules:reads") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 97,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "text-end", children: text("modules:state") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 98,
              columnNumber: 29
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 95,
            columnNumber: 25
          }, this) }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 94,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("tbody", { children: shown.map((item) => {
            const on = state[item.type];
            const label = text(on ? "setting:modules_disable" : "setting:modules_enable", item.name);
            return /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: "d-inline-flex align-items-center gap-3", children: [
                /* @__PURE__ */ jsxDEV("span", { className: `activityiconcontainer ${item.purpose}`, children: /* @__PURE__ */ jsxDEV("img", { className: "activityicon", src: item.iconurl, alt: "", width: 24, height: 24 }, void 0, false, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 109,
                  columnNumber: 45
                }, this) }, void 0, false, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 108,
                  columnNumber: 41
                }, this),
                /* @__PURE__ */ jsxDEV("strong", { children: item.name }, void 0, false, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 111,
                  columnNumber: 41
                }, this)
              ] }, void 0, true, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 107,
                columnNumber: 37
              }, this) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 106,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "text-muted", children: text(`modules:reads_${item.reads}`) || text("modules:reads_completion") }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 114,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "text-end", children: /* @__PURE__ */ jsxDEV("div", { className: "form-check form-switch d-inline-block mb-0", children: /* @__PURE__ */ jsxDEV(
                "input",
                {
                  type: "checkbox",
                  role: "switch",
                  className: "form-check-input",
                  checked: on,
                  "aria-label": label,
                  title: label,
                  disabled: busy === item.type,
                  onChange: () => flip(item.type, item.name, !on)
                },
                void 0,
                false,
                {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 117,
                  columnNumber: 41
                },
                this
              ) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 116,
                columnNumber: 37
              }, this) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 115,
                columnNumber: 33
              }, this)
            ] }, item.type, true, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 105,
              columnNumber: 36
            }, this);
          }) }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 101,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 92,
          columnNumber: 17
        }, this),
        shown.length === 0 && /* @__PURE__ */ jsxDEV("p", { className: "text-center text-muted p-5 mb-0", role: "status", children: text("modules:empty") }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 133,
          columnNumber: 40
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 91,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "table-foot text-muted small", children: text("modules:count", types.length) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 135,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 65,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "small text-muted mt-3", children: text("modules:intro") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 137,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
    lineNumber: 63,
    columnNumber: 12
  }, this);
}
__name(Modules, "Modules");
export {
  Modules as default
};
//# sourceMappingURL=Modules.dev.js.map
