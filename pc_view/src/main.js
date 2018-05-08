// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
// import './theme/theme-blue.scss'
// import './theme/theme-red.scss'
import Vue from 'vue'
import App from '@/App'
import router from '@/router'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

Vue.use(ElementUI)

// import ''

Vue.prototype.theme = 'theme-blue'

Vue.prototype.login = true
Vue.prototype.jumpTo = function(patth){
  this.$router.push(patth);
}

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  components: { App },
  template: '<App/>'
})
