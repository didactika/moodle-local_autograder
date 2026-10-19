import{useMemo as f,useState as p}from"react";import{createText as h}from"./engine/text";import{addToast as u}from"./engine/toast";import{describeError as N,setModuleType as k}from"./services/api";import{jsx as l,jsxs as m}from"react/jsx-runtime";/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The activity types that Autograder may be switched on for: one row and one switch for each gradeable type. A switch saves the
 * moment it is flipped and says so, and goes back where it was if the server refuses.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */function w({types:i,strings:c}){const o=f(()=>h(c),[c]),[b,n]=p(()=>Object.fromEntries(i.map(e=>[e.type,e.enabled]))),[g,d]=p(""),y=(e,s,t)=>{d(e),n(a=>({...a,[e]:t})),k(e,t).then(a=>(n(r=>({...r,[e]:a.enabled})),u(o(a.enabled?"setting:modules_enabled":"setting:modules_disabled",s),{type:"success"}))).catch(a=>(n(r=>({...r,[e]:!t})),u(o("modules:failed",N(a)),{type:"danger"}))).finally(()=>d(""))};return m("div",{className:"lag",children:[l("p",{className:"lag-intro",children:o("modules:intro")}),l("ul",{className:"lag-types","aria-label":o("settings:modulestab"),children:i.map(e=>{const s=b[e.type],t=o(s?"setting:modules_disable":"setting:modules_enable",e.name);return m("li",{className:`lag-type${s?" lag-type-on":""}`,children:[l("img",{className:"lag-type-icon",src:e.iconurl,alt:"",width:28,height:28}),l("span",{className:"lag-type-name",children:e.name}),l("button",{type:"button",role:"switch",className:"lag-switch","aria-checked":s,"aria-label":t,title:t,disabled:g===e.type,onClick:()=>y(e.type,e.name,!s),children:l("span",{className:"lag-switch-knob"})})]},e.type)})})]})}export{w as default};
