// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import routes from 'src/router/index'
import VueRouter from 'vue-router'
import VueI18n from 'vue-i18n'
import Filters from './utils/filters'
import indexServices from 'services/indexServices'
import store from 'src/vuex/store'
import messages from './i18n/messages'
import ElementUI from 'element-ui'
import './less/app.less'

Vue.use(Filters)
Vue.use(ElementUI)
Vue.use(VueRouter)
Vue.use(VueI18n)

const router = new VueRouter({
  mode: 'history',
  routes: routes
})
const i18n = new VueI18n({
  locale: 'zh',
  messages
})
indexServices.isloginfo().then(recvdata => {
  store.commit('EDIT_NOTE', {
    is_login: recvdata.is_login,
    uemail: recvdata.user_email
  })
  router.beforeEach((to, from, next) => {
    // if(!_DEV_){
    if (!store.state.login.is_login) {
      // 没有登录，并且不是注册和修改密码相关的页面，跳转到登录页面
      if (
        to.path !== '/login' &&
        to.path !== '/register' &&
        to.path !== '/password/forget' &&
        to.path !== '/password/sendemail' &&
        to.path !== '/password/change'
      ) {
        next('/login')
      } else {
        next()
      }
    } else {
      if (to.path === '/login' && store.state.login.is_login) {
        // 如果已经登录，却尝试访问登录页面，将继续保持原本的页面
        next(from.path)
      } else {
        next()
      }
    }
    // }else{
    //   next()
    // }
  })

  new Vue({
    el: '#app',
    router,
    store,
    i18n,
    template: '<App/>',
    components: { App }
  })
})
