import{useEffect as v,useRef as x,useState as r}from"react";import{useDebounced as C}from"./hooks/useDebounced";import{searchGraders as E}from"./services/api";import{jsx as a,jsxs as k}from"react/jsx-runtime";/**
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
 */function P({name:N,current:p,currentname:w,strings:t}){const c=x(null),[o,G]=r(p>0?{id:p,name:w}:null),[n,d]=r(!1),[u,m]=r(""),f=C(u.trim(),250),[b,h]=r([]),[i,s]=r(!1);v(()=>{if(!n)return;let e=!0;return s(!0),E(f).then(l=>(e&&(h(l),s(!1)),l)).catch(()=>{e&&(h([]),s(!1))}),()=>{e=!1}},[n,f]),v(()=>{const e=l=>{c.current?.contains(l.target)||d(!1)};return document.addEventListener("pointerdown",e,!0),()=>document.removeEventListener("pointerdown",e,!0)},[]);const y=e=>{G(e),d(!1),m("")};return k("span",{className:"lag-field dropdown d-inline-flex",ref:c,children:[a("input",{type:"hidden",name:N,value:o?.id??0}),a("button",{type:"button",className:`form-select${o?" border-primary":""}`,"aria-haspopup":"listbox","aria-expanded":n,onClick:()=>d(!n),children:o?o.name:t.none}),o&&a("button",{type:"button",className:"btn btn-link p-0","data-menu-clear":!0,title:t.clear,"aria-label":t.clear,onClick:()=>y(null),children:a("i",{className:"icon fa-solid fa-xmark","aria-hidden":"true"})}),n&&k("div",{className:"dropdown-menu show",role:"listbox","aria-busy":i,"aria-label":t.placeholder,children:[a("div",{className:"px-2 pt-1 pb-2",children:a("input",{type:"search",className:"form-control form-control-sm",value:u,placeholder:t.placeholder,"aria-label":t.placeholder,autoComplete:"off",onChange:e=>m(e.target.value),onKeyDown:e=>{e.key==="Enter"&&e.preventDefault()}})}),b.map(e=>a("button",{type:"button",role:"option",className:"dropdown-item","aria-selected":o?.id===e.id,onClick:()=>y(e),children:e.name},e.id)),i&&a("span",{className:"dropdown-item disabled",role:"status",children:t.loading}),!i&&b.length===0&&a("span",{className:"dropdown-item disabled",role:"status",children:t.nomatches})]})]})}export{P as default};
