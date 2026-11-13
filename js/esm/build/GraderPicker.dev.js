var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
import { jsxDEV } from "react/jsx-dev-runtime";
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The field of the fallback grader: a search box that asks the server for users as the administrator types, because a site can
 * have a hundred thousand teachers and none of them is sent to the page. What is chosen is written in the field that the settings
 * page saves.
 *
 * Mounted by core/react_autoinit from the data-react-component that the setting writes.
 *
 * @module     local_autograder/GraderPicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useEffect, useRef, useState } from "react";
import { useDebounced } from "./hooks/useDebounced";
import { searchGraders } from "./services/api";
function GraderPicker({ name, current, currentname, strings }) {
  const root = useRef(null);
  const [chosen, setChosen] = useState(current > 0 ? { id: current, name: currentname } : null);
  const [open, setOpen] = useState(false);
  const [query, setQuery] = useState("");
  const typed = useDebounced(query.trim(), 250);
  const [found, setFound] = useState([]);
  const [loading, setLoading] = useState(false);
  useEffect(() => {
    if (!open) {
      return void 0;
    }
    let latest = true;
    setLoading(true);
    searchGraders(typed).then((users) => {
      if (latest) {
        setFound(users);
        setLoading(false);
      }
      return users;
    }).catch(() => {
      if (latest) {
        setFound([]);
        setLoading(false);
      }
    });
    return () => {
      latest = false;
    };
  }, [open, typed]);
  useEffect(() => {
    const outside = /* @__PURE__ */ __name((event) => {
      if (!root.current?.contains(event.target)) {
        setOpen(false);
      }
    }, "outside");
    document.addEventListener("pointerdown", outside, true);
    return () => document.removeEventListener("pointerdown", outside, true);
  }, []);
  const pick = /* @__PURE__ */ __name((user) => {
    setChosen(user);
    setOpen(false);
    setQuery("");
  }, "pick");
  return /* @__PURE__ */ jsxDEV("span", { className: "lag dd-menu dd-select-filter", ref: root, children: [
    /* @__PURE__ */ jsxDEV("input", { type: "hidden", name, value: chosen?.id ?? 0 }, void 0, false, {
      fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
      lineNumber: 76,
      columnNumber: 9
    }, this),
    /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: `form-select dd-select${chosen ? " dd-select-set border-primary" : ""}`,
        "aria-haspopup": "listbox",
        "aria-expanded": open,
        onClick: () => setOpen(!open),
        children: chosen ? chosen.name : strings.none
      },
      void 0,
      false,
      {
        fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
        lineNumber: 77,
        columnNumber: 9
      },
      this
    ),
    chosen && /* @__PURE__ */ jsxDEV(
      "button",
      {
        type: "button",
        className: "dd-select-clear",
        title: strings.clear,
        "aria-label": strings.clear,
        onClick: () => pick(null),
        children: /* @__PURE__ */ jsxDEV("i", { className: "icon fa-solid fa-xmark", "aria-hidden": "true" }, void 0, false, {
          fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
          lineNumber: 88,
          columnNumber: 13
        }, this)
      },
      void 0,
      false,
      {
        fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
        lineNumber: 86,
        columnNumber: 20
      },
      this
    ),
    open && /* @__PURE__ */ jsxDEV("div", { className: "dd-select-menu", role: "listbox", "aria-busy": loading, "aria-label": strings.placeholder, children: [
      /* @__PURE__ */ jsxDEV("div", { className: "dd-menu-form", children: /* @__PURE__ */ jsxDEV(
        "input",
        {
          type: "search",
          className: "form-control form-control-sm",
          value: query,
          placeholder: strings.placeholder,
          "aria-label": strings.placeholder,
          autoComplete: "off",
          onChange: (event) => setQuery(event.target.value),
          onKeyDown: (event) => {
            if (event.key === "Enter") {
              event.preventDefault();
            }
          }
        },
        void 0,
        false,
        {
          fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
          lineNumber: 92,
          columnNumber: 17
        },
        this
      ) }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
        lineNumber: 91,
        columnNumber: 13
      }, this),
      found.map((user) => /* @__PURE__ */ jsxDEV(
        "button",
        {
          type: "button",
          role: "option",
          className: "dropdown-item",
          "aria-selected": chosen?.id === user.id,
          onClick: () => pick(user),
          children: user.name
        },
        user.id,
        false,
        {
          fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
          lineNumber: 108,
          columnNumber: 34
        },
        this
      )),
      loading && /* @__PURE__ */ jsxDEV("span", { className: "dropdown-item disabled", role: "status", children: strings.loading }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
        lineNumber: 116,
        columnNumber: 25
      }, this),
      !loading && found.length === 0 && /* @__PURE__ */ jsxDEV("span", { className: "dropdown-item disabled", role: "status", children: strings.nomatches }, void 0, false, {
        fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
        lineNumber: 117,
        columnNumber: 48
      }, this)
    ] }, void 0, true, {
      fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
      lineNumber: 90,
      columnNumber: 18
    }, this)
  ] }, void 0, true, {
    fileName: "public/local/autograder/js/esm/src/GraderPicker.tsx",
    lineNumber: 75,
    columnNumber: 12
  }, this);
}
__name(GraderPicker, "GraderPicker");
export {
  GraderPicker as default
};
//# sourceMappingURL=GraderPicker.dev.js.map
