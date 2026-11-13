import{useLayoutEffect as a}from"react";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The height of the box of a table, as the design system asks: what the window leaves it, so the table and its filters fit it.
 *
 * @module     local_autograder/hooks/useFitHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */const m=288,u=24;function f(n){a(()=>{const t=()=>{const e=n.current;if(!e)return;const c=e.getBoundingClientRect(),i=c.top+window.scrollY,s=e.closest(".card")?.getBoundingClientRect(),d=s?Math.max(0,s.bottom-c.bottom):0,l=Math.max(m,window.innerHeight-i-d-u);(e.closest(".card")??e).style.setProperty("--dd-table-h",l+"px")};t();const o=new ResizeObserver(()=>t());o.observe(document.body);const r=n.current?.closest(".card");return r&&o.observe(r),window.addEventListener("resize",t),()=>{o.disconnect(),window.removeEventListener("resize",t)}},[])}export{f as useFitHeight};
