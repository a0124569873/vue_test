webpackJsonp([43],{V9V6:function(e,t,s){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var n=s("4YfN"),i=s.n(n),o=s("9rMa"),r={name:"permission",data:function(){return{switchRoles:""}},computed:i()({},Object(o.b)(["roles"])),watch:{switchRoles:function(e){var t=this;this.$store.dispatch("ChangeRoles",e).then(function(){t.$router.push({path:"/permission/index?"+ +new Date})})}}},a={render:function(){var e=this,t=e.$createElement,s=e._self._c||t;return s("div",{staticClass:"app-container"},[s("div",{staticStyle:{"margin-bottom":"15px"}},[e._v(e._s(e.$t("permission.roles"))+"： "+e._s(e.roles))]),e._v("\n  "+e._s(e.$t("permission.switchRoles"))+"：\n  "),s("el-radio-group",{model:{value:e.switchRoles,callback:function(t){e.switchRoles=t},expression:"switchRoles"}},[s("el-radio-button",{attrs:{label:"editor"}})],1)],1)},staticRenderFns:[]},l=s("/Xao")(r,a,!1,null,null,null);t.default=l.exports}});