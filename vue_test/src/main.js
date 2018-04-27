// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
// import G2 from 'g2'
import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'
import echarts from 'echarts'
// import $ from 'jquery'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.min'

import './theme/elui.less';

import store from './vuex/Stroe_test'




// console.log($)


// let aa = "aaa|1111";
// console.log(aa.split("|"))


// window.G2 = G2;

Vue.prototype.$echarts = echarts 

Vue.prototype.jumpTo = function(patth){
  this.$router.push(patth);
}

Vue.config.productionTip = false

Vue.use(ElementUI)


/* eslint-disable no-new */

let mixin = {
  methods:{
    test_mixin:function () {
      console.log("test_mixin ")
    }
  }
}

new Vue({
  mixins:[mixin],
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>',
  mounted(){
    this.test_mixin() 
  }
})

router.push("/login")
