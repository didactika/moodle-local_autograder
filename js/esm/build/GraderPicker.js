import{useEffect as v,useLayoutEffect as E,useRef as P,useState as l}from"react";import{useDebounced as L}from"./hooks/useDebounced";import{searchGraders as g}from"./services/api";import{jsx as t,jsxs as w}from"react/jsx-runtime";/**
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
 */function D({name:k,current:p,currentname:N,strings:o}){const d=P(null),[r,G]=l(p>0?{id:p,name:N}:null),[a,i]=l(!1),[x,C]=l(!1),[u,m]=l(""),f=L(u.trim(),250),[b,h]=l([]),[s,c]=l(!1);v(()=>{if(!a)return;let e=!0;return c(!0),g(f).then(n=>(e&&(h(n),c(!1)),n)).catch(()=>{e&&(h([]),c(!1))}),()=>{e=!1}},[a,f]),v(()=>{const e=n=>{d.current?.contains(n.target)||i(!1)};return document.addEventListener("pointerdown",e,!0),()=>document.removeEventListener("pointerdown",e,!0)},[]),E(()=>{if(a&&d.current){const e=d.current.getBoundingClientRect(),n=window.innerHeight-e.bottom;C(n<288&&e.top>n)}},[a]);const y=e=>{G(e),i(!1),m("")};return w("span",{className:`lag-field ${x?"dropup":"dropdown"} d-inline-flex`,ref:d,children:[t("input",{type:"hidden",name:k,value:r?.id??0}),t("button",{type:"button",className:`form-select${r?" border-primary":""}`,"aria-haspopup":"listbox","aria-expanded":a,onClick:()=>i(!a),children:r?r.name:o.none}),r&&t("button",{type:"button",className:"btn btn-link p-0","data-menu-clear":!0,title:o.clear,"aria-label":o.clear,onClick:()=>y(null),children:t("i",{className:"icon fa-solid fa-xmark","aria-hidden":"true"})}),a&&w("div",{className:"dropdown-menu show",role:"listbox","aria-busy":s,"aria-label":o.placeholder,children:[t("div",{className:"px-2 pt-1 pb-2",children:t("input",{type:"search",className:"form-control form-control-sm",value:u,placeholder:o.placeholder,"aria-label":o.placeholder,autoComplete:"off",onChange:e=>m(e.target.value),onKeyDown:e=>{e.key==="Enter"&&e.preventDefault()}})}),b.map(e=>t("button",{type:"button",role:"option",className:"dropdown-item","aria-selected":r?.id===e.id,onClick:()=>y(e),children:e.name},e.id)),s&&t("span",{className:"dropdown-item disabled",role:"status",children:o.loading}),!s&&b.length===0&&t("span",{className:"dropdown-item disabled",role:"status",children:o.nomatches})]})]})}export{D as default};
