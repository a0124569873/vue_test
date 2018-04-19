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

Vue.use(Router)

export default new Router({
  routes: [
    {
      path: '/',
      name: 'mainn',
      component: mainn,
      children: [
        {
          path: '/',
          name: 'leftbar',
          component: leftbar
        },
        {
          path: 'header',
          name: 'header',
          component: header
        },
        {
          path: 'zxn',
          name: 'zxn',
          component: ZXN
        },
        {
          path: 'dialog',
          name: 'dialog',
          component: dialog
        },
        {
          path: 'charts',
          name: 'charts',
          component: charts
        },
        {
          path: 'leftbar',
          name: 'leftbar',
          component: leftbar,
          children: [
            {
              path: '',
              name: 'dialog',
              component: dialog
            },
            {
              path: 'charts',
              name: 'charts',
              component: charts
            },
            {
              path: 'dialog',
              name: 'dialog',
              component: dialog
            },
            {
              path: 'g2chart',
              name: 'g2chart',
              component: g2chart
            },
            {
              path: 'bootstrap',
              name: 'bootstrap',
              component: bootstrap
            },
            {
              path: 'zxn',
              name: 'zxn',
              component: ZXN
            },
            {
              path: 'elui',
              name: 'elui',
              component: elui
            },
            {
              path: 'heighchart',
              name: 'heighchart',
              component: heighchart
            },
            {
              path: 'father',
              name: 'father',
              component: father
            },
            {
              path: 'testcss',
              name: 'testcss',
              component: testcss
            },
            {
              path: 'promise',
              name: 'promise',
              component: promise
            }
          ]
        }
      ]
    }
  ]
})
