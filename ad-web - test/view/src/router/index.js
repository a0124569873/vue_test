import Vue from 'vue'
import Router from 'vue-router'
import routes from './asyncRouterMap'
Vue.use(Router)

export default new Router({
  mode: 'history',
  routes: [
    {
      path: '/login',
      component: resolve => require(['@/components/login/'], resolve)
    },
    {
      path: '/errorpage',
      component: resolve => require(['@/components/error/'], resolve)
    },
    ...routes
  ]
})
