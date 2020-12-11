/*!
 * @project        retour
 * @name           import.js
 * @author         Andrew Welch
 * @build          Fri Dec 11 2020 03:13:48 GMT+0000 (Coordinated Universal Time)
 * @copyright      Copyright (c) 2020 ©2020 nystudio107.com
 *
 */
(self.webpackChunkretour=self.webpackChunkretour||[]).push([[6],{4260:function(t,a,e){"use strict";e(8309);var r=e(9980),i={components:{draggable:e.n(r)()},computed:{extraFields:function(){var t=this.dataArray.length-this.fieldsArray.length;return t>=0?t:0},extraData:function(){var t=this.fieldsArray.length-this.dataArray.length;return t>=0?t:0}},props:{data:Array},methods:{remove:function(t){this.dataArray[t].name=""}},created:function(){for(var t=this.dataArray.length;this.extraData;)this.dataArray.push({id:t,name:""}),t++},data:function(){return{dataArray:this.data,options:{group:"fields"},fieldsArray:["Legacy URL Pattern","Redirect To","Match Type","HTTP Status","Site ID","Legacy URL Match Type","Hits"]}}},n=(0,e(1900).Z)(i,(function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"flex overflow-hidden w-full flex-no-wrap items-start",staticStyle:{"max-width":"80%"}},[e("div",{staticClass:"flex-grow"},[e("div",[e("h2",[t._v("CSV File Fields")]),t._v(" "),e("draggable",{staticClass:"retour-import-dragArea",attrs:{list:t.dataArray,options:t.options},on:{start:function(a){t.drag=!0},end:function(a){t.drag=!1}}},[t._l(t.dataArray,(function(a,r){return e("div",{key:a.id,staticClass:"retour-import-list-group-item whitespace-no-wrap clearafter",class:{"retour-empty-item":""===a.name}},[e("a",{staticClass:"move icon mr-2"}),t._v(" "),e("span",{staticClass:"truncate inline-block leading-tight align-bottom",staticStyle:{width:"80%"}},[t._v(t._s(a.name))]),t._v(" "),""!==a.name?e("div",{staticClass:"float-right"},[e("a",{staticClass:"delete icon",on:{click:function(a){return t.remove(r)}}})]):t._e()])})),t._v(" "),t._l(t.extraData,(function(a){return e("div",{staticClass:"retour-import-field-group-item clearafter retour-empty-item"},[t._v("\n                     \n                ")])}))],2),t._v(" "),t._l(t.dataArray,(function(t,a){return e("input",{attrs:{name:"columns["+a+"]",type:"hidden"},domProps:{value:t.name}})}))],2)]),t._v(" "),e("div",{staticClass:"flex-shrink"},[e("div",[e("h2",[t._v(" ")]),t._v(" "),e("div",t._l(t.fieldsArray,(function(a,r){return e("div",{staticClass:"retour-import-arrow-item clearafter"},[t._v("\n                    →\n                ")])})),0)])]),t._v(" "),e("div",{staticClass:"flex-grow"},[e("div",[e("h2",[t._v("Retour Redirect Fields")]),t._v(" "),e("div",[t._l(t.fieldsArray,(function(a,r){return e("div",{staticClass:"retour-import-field-group-item clearafter"},[t._v("\n                    "+t._s(a)+"\n                ")])})),t._v(" "),t._l(t.extraFields,(function(a){return e("div",{staticClass:"retour-import-field-group-item clearafter retour-empty-item"},[t._v("\n                     \n                ")])}))],2)])])])}),[],!1,null,null,null).exports;new Vue({el:"#cp-nav-content",components:{"import-draggable":n},data:{},methods:{},mounted:function(){}})}},0,[[4260,666,216]]]);
//# sourceMappingURL=import.js.map