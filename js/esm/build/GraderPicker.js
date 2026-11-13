import{useEffect as v,useRef as C,useState as r}from"react";import{useDebounced as E}from"./hooks/useDebounced";import{searchGraders as P}from"./services/api";import{jsx as t,jsxs as N}from"react/jsx-runtime";/**
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
 */function x({name:k,current:c,currentname:G,strings:a}){const u=C(null),[o,w]=r(c>0?{id:c,name:G}:null),[n,d]=r(!1),[p,m]=r(""),f=E(p.trim(),250),[b,h]=r([]),[s,i]=r(!1);v(()=>{if(!n)return;let e=!0;return i(!0),P(f).then(l=>(e&&(h(l),i(!1)),l)).catch(()=>{e&&(h([]),i(!1))}),()=>{e=!1}},[n,f]),v(()=>{const e=l=>{u.current?.contains(l.target)||d(!1)};return document.addEventListener("pointerdown",e,!0),()=>document.removeEventListener("pointerdown",e,!0)},[]);const y=e=>{w(e),d(!1),m("")};return N("span",{className:"lag dd-menu dd-select-filter",ref:u,children:[t("input",{type:"hidden",name:k,value:o?.id??0}),t("button",{type:"button",className:`form-select dd-select${o?" dd-select-set border-primary":""}`,"aria-haspopup":"listbox","aria-expanded":n,onClick:()=>d(!n),children:o?o.name:a.none}),o&&t("button",{type:"button",className:"dd-select-clear",title:a.clear,"aria-label":a.clear,onClick:()=>y(null),children:t("i",{className:"icon fa-solid fa-xmark","aria-hidden":"true"})}),n&&N("div",{className:"dd-select-menu",role:"listbox","aria-busy":s,"aria-label":a.placeholder,children:[t("div",{className:"dd-menu-form",children:t("input",{type:"search",className:"form-control form-control-sm",value:p,placeholder:a.placeholder,"aria-label":a.placeholder,autoComplete:"off",onChange:e=>m(e.target.value),onKeyDown:e=>{e.key==="Enter"&&e.preventDefault()}})}),b.map(e=>t("button",{type:"button",role:"option",className:"dropdown-item","aria-selected":o?.id===e.id,onClick:()=>y(e),children:e.name},e.id)),s&&t("span",{className:"dropdown-item disabled",role:"status",children:a.loading}),!s&&b.length===0&&t("span",{className:"dropdown-item disabled",role:"status",children:a.nomatches})]})]})}export{x as default};
