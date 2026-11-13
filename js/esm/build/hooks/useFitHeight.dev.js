var __defProp = Object.defineProperty;
var __name = (target, value) => __defProp(target, "name", { value, configurable: true });
/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The height of the box of a table, as the design system asks: what the window leaves it, so the table and its filters fit it.
 *
 * @module     local_autograder/hooks/useFitHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import { useLayoutEffect } from "react";
const TABLE_MIN = 288;
const TABLE_ROOM = 24;
function useFitHeight(box) {
  useLayoutEffect(() => {
    const fit = /* @__PURE__ */ __name(() => {
      const element = box.current;
      if (!element) {
        return;
      }
      const rect = element.getBoundingClientRect();
      const top = rect.top + window.scrollY;
      const card2 = element.closest(".card")?.getBoundingClientRect();
      const below = card2 ? Math.max(0, card2.bottom - rect.bottom) : 0;
      const height = Math.max(TABLE_MIN, window.innerHeight - top - below - TABLE_ROOM);
      (element.closest(".card") ?? element).style.setProperty("--dd-table-h", height + "px");
    }, "fit");
    fit();
    const observer = new ResizeObserver(() => fit());
    observer.observe(document.body);
    const card = box.current?.closest(".card");
    if (card) {
      observer.observe(card);
    }
    window.addEventListener("resize", fit);
    return () => {
      observer.disconnect();
      window.removeEventListener("resize", fit);
    };
  }, []);
}
__name(useFitHeight, "useFitHeight");
export {
  useFitHeight
};
//# sourceMappingURL=useFitHeight.dev.js.map
