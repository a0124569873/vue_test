// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import ElementUI from 'element-ui'
import store from '@/vuex/store'
import indexService from 'services/indexService'
import Utils from '@/utils'
import '@/assets/icon/iconfont.css'
import '@/theme/index.scss'
import '@/theme/theme-blue.scss'

Vue.prototype.theme = 'theme-' + (__THEME__ || 'red')
if (window.localStorage.getItem('theme')) {
  Vue.prototype.theme = window.localStorage.getItem('theme')
} 
Vue.config.productionTip = false
Vue.use(ElementUI, { size: 'mini' })
Vue.use(Utils)
Vue.prototype.$message = ElementUI.Message

if (window.sessionStorage.getItem('login') === 'true') {
  bootApp()
  beforeRouteHandler()
  if (document.location.href.indexOf('login') !== -1) {
    router.push('/')
  } 
} else {
  indexService.islogin()
    .then((res) => {
      bootApp()
      beforeRouteHandler()
      if (res.errcode === 0) {
        store.state.login = res.isLogin
        window.sessionStorage.setItem('login', res.isLogin)
      } else if (res.errcode === 12001) {
        router.push('/login')
      }
    }).fail(() => {
      bootApp()
    })
}
// 界面初始化
function bootApp () {
  new Vue({
    el: '#app',
    router,
    store,
    template: '<App/>', 
    components: { App }
  })
}

function beforeRouteHandler () {
  router.beforeEach((to, from, next) => {
    if (!_DEV_) {
      store.state.login = store.state.login || window.sessionStorage.getItem('login') === 'true'
      if (!store.state.login) { // 没有登录
        if (to.path !== '/login') {
          next('/login')
        } else {
          next()
        }
      } else {
        if (to.path === '/login') { // 如果已经登录，却尝试访问登录页面，将继续保持原本的页面
          next(from.path)
        } else {
          next()
        }
      }
    } else {
      next()
    }
  })
}
