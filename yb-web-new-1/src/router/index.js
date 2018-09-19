import Vue from 'vue'
import Router from 'vue-router'
import HelloWorld from '@/components/HelloWorld'
import layout from '@/views/layout'
import leftbar from '@/views/layout/leftbar'
import mainPage from '@/views/mainPage'
import commonPage from '@/views/commonPage'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      component: layout,
      children: [
        {
          path: '/',
          component: mainPage
        },
        {
          path: '/mainPage',
          component: mainPage
        },
        {
          path: '/leftbar',
          component: leftbar
        }
      ]
    }
  ]
})
