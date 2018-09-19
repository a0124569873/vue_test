import Vue from 'vue'
import Router from 'vue-router'
import header from '@/components/header'
import ZXN from '@/components/zxn'
import dialog from '@/components/dialog'
import charts from '@/components/charts'
import mainn from '@/components/mainn'
import leftbar from '@/components/leftbar'
import g2chart from '@/components/g2chart'
import bootstrap from '@/components/bootstrap'
import elui from '@/components/elui'
import heighchart from '@/components/heighchart'
import father from '@/components/father'
import testcss from '@/components/testcss'
import promise from '@/components/promise'
import menuitem from '@/components/menuitem'
import login from '@/components/login'
import gstatus from '@/components/gstatus'

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/login',
      component: login
    },
    {
      path: '/',
      name: '',
      component: mainn,
      children: [
        {
          path: '/',
          name: '',
          component: leftbar
        },
        {
          path: 'menuitem',
          name: '',
          component: menuitem,
          children:[
            {
              path:"/",
              component: dialog
            },
            {
              path:"/vvv",
              component: g2chart
            },
            {
              path: '/login',
              component: login
            }
          ]

        },
        {
          path: 'header',
          name: '',
          component: header
        },
        {
          path: 'zxn',
          name: '',
          component: ZXN
        },
        {
          path: 'dialog',
          name: '',
          component: dialog
        },
        {
          path: 'charts',
          name: '',
          component: charts
        },
        {
          path: 'leftbar',
          name: '',
          component: leftbar,
          children: [
            {
              path: '',
              name: '',
              component: dialog
            },
            {
              path: 'charts',
              name: '',
              component: charts
            },
            {
              path: 'dialog',
              name: '',
              component: dialog
            },
            {
              path: 'g2chart',
              name: '',
              component: g2chart
            },
            {
              path: 'bootstrap',
              name: '',
              component: bootstrap
            },
            {
              path: 'zxn',
              name: '',
              component: ZXN
            },
            {
              path: 'elui',
              name: '',
              component: elui
            },
            {
              path: 'heighchart',
              name: '',
              component: heighchart
            },
            {
              path: 'father',
              name: '',
              component: father
            },
            {
              path: 'testcss',
              name: '',
              component: testcss
            },
            {
              path: 'promise',
              name: '',
              component: promise
            },
            {
              path: 'gstatus',
              component: gstatus
            }
          ]
        }
      ]
    }
  ]
})
