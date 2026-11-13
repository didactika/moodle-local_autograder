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
 * and Moodle (card, table, form-switch, activityiconcontainer) and the chips of the design system (dd-chips); the styles only adjust them inside the page.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useId, useMemo, useState, useLayoutEffect, useRef } from "react";
import { createText } from "./engine/text";
import { useFitHeight } from "./hooks/useFitHeight";
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
  const table = useRef(null);
  const fitbox = useRef(null);
  useFitHeight(fitbox);
  useStableColumns(table, shown.length > 0);
  return /* @__PURE__ */ jsxDEV("div", { className: "lag", children: [
    /* @__PURE__ */ jsxDEV("p", { className: "text-muted mb-0", children: text("modules:lead") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 71,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "dd-chips", role: "group", "aria-label": text("settings:modulestab"), children: ["all", "on", "off"].map((name) => /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "dd-chip",
        "aria-pressed": filter === name,
        onClick: () => setFilter(name),
        children: [
          text(`modules:filter_${name}`),
          " ",
          /* @__PURE__ */ jsxDEV("span", { className: "dd-chip-count", children: counts[name] }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 80,
            columnNumber: 50
          }, this)
        ]
      },
      name,
      true,
      {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 73,
        columnNumber: 63
      },
      this
    )) }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 72,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("div", { className: "card", children: [
      types.length >= SEARCH_FROM && /* @__PURE__ */ jsxDEV("div", { className: "table-toolbar", children: /* @__PURE__ */ jsxDEV("div", { className: "input-group", children: [
        /* @__PURE__ */ jsxDEV("label", { className: "visually-hidden sr-only", htmlFor: searchId, children: text("modules:search") }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 86,
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
            lineNumber: 87,
            columnNumber: 21
          },
          this
        )
      ] }, void 0, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 85,
        columnNumber: 17
      }, this) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 84,
        columnNumber: 45
      }, this),
      /* @__PURE__ */ jsxDEV("div", { ref: fitbox, className: "table-responsive", "data-region": "tablewrap", children: [
        shown.length === 0 && /* @__PURE__ */ jsxDEV("div", { className: "dd-empty", "data-region": "empty", role: "status", children: [
          /* @__PURE__ */ jsxDEV("span", { className: "dd-tile", "aria-hidden": "true", dangerouslySetInnerHTML: { __html: text("pix:a/search") } }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 100,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("p", { className: "dd-empty-text", children: text("modules:empty") }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 101,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 99,
          columnNumber: 40
        }, this),
        shown.length > 0 && /* @__PURE__ */ jsxDEV("table", { className: "table", ref: table, children: [
          /* @__PURE__ */ jsxDEV("caption", { className: "visually-hidden sr-only", children: text("settings:modulestab") }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 104,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("thead", { children: /* @__PURE__ */ jsxDEV("tr", { children: [
            /* @__PURE__ */ jsxDEV("th", { scope: "col", children: text("modules:name") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 107,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "d-none d-md-table-cell", children: text("modules:reads") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 108,
              columnNumber: 29
            }, this),
            /* @__PURE__ */ jsxDEV("th", { scope: "col", className: "text-end", children: text("modules:state") }, void 0, false, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 109,
              columnNumber: 29
            }, this)
          ] }, void 0, true, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 106,
            columnNumber: 25
          }, this) }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 105,
            columnNumber: 21
          }, this),
          /* @__PURE__ */ jsxDEV("tbody", { children: shown.map((item) => {
            const on = state[item.type];
            const label = text(on ? "setting:modules_disable" : "setting:modules_enable", item.name);
            const reads = text(`modules:reads_${item.reads}`) || text("modules:reads_completion");
            return /* @__PURE__ */ jsxDEV("tr", { children: [
              /* @__PURE__ */ jsxDEV("td", { children: /* @__PURE__ */ jsxDEV("span", { className: "d-inline-flex align-items-center gap-3", children: [
                /* @__PURE__ */ jsxDEV("span", { className: `activityiconcontainer ${item.purpose}`, children: /* @__PURE__ */ jsxDEV("img", { className: "activityicon", src: item.iconurl, alt: "", width: 24, height: 24 }, void 0, false, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 121,
                  columnNumber: 45
                }, this) }, void 0, false, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 120,
                  columnNumber: 41
                }, this),
                /* @__PURE__ */ jsxDEV("span", { className: "d-flex flex-column", children: [
                  /* @__PURE__ */ jsxDEV("strong", { children: item.name }, void 0, false, {
                    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                    lineNumber: 124,
                    columnNumber: 45
                  }, this),
                  /* @__PURE__ */ jsxDEV("span", { className: "small text-muted d-md-none", children: reads }, void 0, false, {
                    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                    lineNumber: 125,
                    columnNumber: 45
                  }, this)
                ] }, void 0, true, {
                  fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                  lineNumber: 123,
                  columnNumber: 41
                }, this)
              ] }, void 0, true, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 119,
                columnNumber: 37
              }, this) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 118,
                columnNumber: 33
              }, this),
              /* @__PURE__ */ jsxDEV("td", { className: "text-muted d-none d-md-table-cell", children: reads }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 129,
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
                  lineNumber: 132,
                  columnNumber: 41
                },
                this
              ) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 131,
                columnNumber: 37
              }, this) }, void 0, false, {
                fileName: "public/local/autograder/js/esm/src/Modules.tsx",
                lineNumber: 130,
                columnNumber: 33
              }, this)
            ] }, item.type, true, {
              fileName: "public/local/autograder/js/esm/src/Modules.tsx",
              lineNumber: 117,
              columnNumber: 36
            }, this);
          }) }, void 0, false, {
            fileName: "public/local/autograder/js/esm/src/Modules.tsx",
            lineNumber: 112,
            columnNumber: 21
          }, this)
        ] }, void 0, true, {
          fileName: "public/local/autograder/js/esm/src/Modules.tsx",
          lineNumber: 103,
          columnNumber: 38
        }, this)
      ] }, void 0, true, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 98,
        columnNumber: 13
      }, this),
      /* @__PURE__ */ jsxDEV("div", { className: "table-foot text-muted small", children: text("modules:count", types.length) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/Modules.tsx",
        lineNumber: 149,
        columnNumber: 13
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 83,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV("p", { className: "small text-muted mb-0", children: text("modules:intro") }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/Modules.tsx",
      lineNumber: 151,
      columnNumber: 9
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/autograder/js/esm/src/Modules.tsx",
    lineNumber: 70,
    columnNumber: 12
  }, this);
}
__name(Modules, "Modules");
function useStableColumns(table, ready) {
  useLayoutEffect(() => {
    const element = table.current;
    const box = element?.parentElement;
    if (!element || !box || !ready) {
      return void 0;
    }
    const freeze = /* @__PURE__ */ __name(() => {
      const heads = Array.from(element.tHead?.rows[0]?.cells ?? []);
      element.style.tableLayout = "";
      heads.forEach((head) => {
        head.style.width = "";
      });
      const widths = heads.map((head) => head.getBoundingClientRect().width);
      heads.forEach((head, index) => {
        head.style.width = `${widths[index]}px`;
      });
      element.style.tableLayout = "fixed";
    }, "freeze");
    freeze();
    let width = box.clientWidth;
    const observer = new ResizeObserver(() => {
      if (Math.abs(box.clientWidth - width) > 1) {
        width = box.clientWidth;
        freeze();
      }
    });
    observer.observe(box);
    return () => observer.disconnect();
  }, [ready]);
}
__name(useStableColumns, "useStableColumns");
export {
  Modules as default
};
//# sourceMappingURL=Modules.dev.js.map
