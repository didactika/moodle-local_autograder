import{useEffect as y,useRef as E,useState as r}from"react";import{useDebounced as P}from"./hooks/useDebounced";import{searchGraders as x}from"./services/api";import{jsx as a,jsxs as d}from"react/jsx-runtime";/**
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
 */function C({name:g,current:u,currentname:N,strings:t}){const p=E(null),[l,G]=r(u>0?{id:u,name:N}:null),[o,i]=r(!1),[m,f]=r(""),k=P(m.trim(),250),[b,v]=r([]),[s,c]=r(!1);y(()=>{if(!o)return;let e=!0;return c(!0),x(k).then(n=>(e&&(v(n),c(!1)),n)).catch(()=>{e&&(v([]),c(!1))}),()=>{e=!1}},[o,k]),y(()=>{const e=n=>{p.current?.contains(n.target)||i(!1)};return document.addEventListener("pointerdown",e,!0),()=>document.removeEventListener("pointerdown",e,!0)},[]);const h=e=>{G(e),i(!1),f("")};return d("div",{className:"lag lag-picker",ref:p,children:[a("input",{type:"hidden",name:g,value:l?.id??0}),d("div",{className:"lag-picker-box",children:[a("button",{type:"button",className:"lag-picker-button","aria-haspopup":"listbox","aria-expanded":o,onClick:()=>i(!o),children:l?l.name:t.none}),l&&a("button",{type:"button",className:"lag-picker-clear","aria-label":t.clear,onClick:()=>h(null),children:a("i",{className:"fa-solid fa-xmark","aria-hidden":"true"})})]}),o&&d("div",{className:"lag-picker-panel",children:[a("input",{type:"search",className:"lag-picker-field",value:m,placeholder:t.placeholder,"aria-label":t.placeholder,onChange:e=>f(e.target.value),onKeyDown:e=>{e.key==="Enter"&&e.preventDefault()}}),a("ul",{className:"lag-picker-list",role:"listbox","aria-busy":s,children:b.map(e=>a("li",{role:"option","aria-selected":l?.id===e.id,children:a("button",{type:"button",className:"lag-picker-option",onClick:()=>h(e),children:e.name})},e.id))}),s&&a("p",{className:"lag-picker-status",role:"status",children:t.loading}),!s&&b.length===0&&a("p",{className:"lag-picker-status",role:"status",children:t.nomatches})]})]})}export{C as default};
