const routes = [
  {
    path: '/error',
    component: resolve => require(['views/error'], resolve)
  },
  {
    path: '/',
    component: resolve => require(['views/admin'], resolve),
    children: [
      {
        path: '',
        component: resolve => require(['views/admin/home'], resolve)
      },
      {
        path: '/site',
        component: resolve => require(['views/admin/site'], resolve)
      },
      {
        path: '/site/:id/control',
        component: resolve => require(['views/admin/site/control'], resolve),
        children: [{
          path: '',
          name: 'siteBwlist',
          component: resolve => require(['views/admin/site/control/bwlist'], resolve)
        }, {
          path: 'speed',
          name: 'siteSpeed',
          component: resolve => require(['views/admin/site/control/speed'], resolve)
        }, {
          path: 'https',
          name: 'siteHttps',
          component: resolve => require(['views/admin/site/control/https'], resolve)
        }]
      },
      {
        path: '/site/:id/report',
        component: resolve => require(['views/admin/site/report'], resolve),
        children: [{
          path: '',
          name: 'siteOverview',
          component: resolve => require(['views/admin/site/report/overview'], resolve)
        }, {
          path: 'banWidth',
          name: 'siteBanWidth',
          component: resolve => require(['views/admin/site/report/banWidth'], resolve)
        }, {
          path: 'flow',
          name: 'siteFlow',
          component: resolve => require(['views/admin/site/report/flow'], resolve)
        }]
      },
      {
        path: '/app',
        component: resolve => require(['views/admin/app'], resolve)
      },
      {
        path: '/app/:id/control',
        component: resolve => require(['views/admin/app/control'], resolve),
        children: [{
          path: '',
          name: 'appConf',
          component: resolve => require(['views/admin/app/control/config'], resolve)
        }, {
          path: 'bwlist',
          name: 'appBwlist',
          component: resolve => require(['views/admin/app/control/bwlist'], resolve)
        }, {
          path: 'cname',
          name: 'appCname',
          component: resolve => require(['views/admin/app/control/cname'], resolve)
        }]
      },
      {
        path: '/app/:id/report',
        component: resolve => require(['views/admin/app/report'], resolve),
        children: [{
          as: '',
          path: 'banWidth',
          name: 'appBanWidth',
          component: resolve => require(['views/admin/app/report/banWidth'], resolve)
        }, {
          path: 'flow',
          name: 'appFlow',
          component: resolve => require(['views/admin/app/report/flow'], resolve)
        }]
      },
      {
        path: '/high',
        component: resolve => require(['views/admin/high'], resolve)
      },
      {
        path: '/high/:id/report',
        component: resolve => require(['views/admin/high/report'], resolve),
        children: [{
          as: '',
          path: 'banWidth',
          name: 'highBanWidth',
          component: resolve => require(['views/admin/high/report/banWidth'], resolve)
        }, {
          path: 'flow',
          name: 'highFlow',
          component: resolve => require(['views/admin/high/report/flow'], resolve)
        }]
      },
      {
        path: '/user',
        component: resolve => require(['views/admin/user'], resolve)
      },
      {
        name: 'buy',
        path: '/buy',
        component: resolve => require(['views/admin/buy'], resolve)
      }, // end new
      {
        name: 'order',
        path: '/order',
        component: resolve => require(['views/admin/order'], resolve),
        children: [
          {
            path: '/',
            component: resolve => require(['views/admin/order/list'], resolve)
          },
          {
            name: 'prepay',
            path: 'prepay',
            component: resolve => require(['views/admin/order/prepay'], resolve)
          },
          {
            path: 'pay/:order_id',
            component: resolve => require(['views/admin/order/pay'], resolve)
          },
          {
            path: 'payInfo/:order_id/:order_type',
            component: resolve => require(['views/admin/order/payInfo'], resolve)
          },
          {
            path: 'pay_result',
            component: resolve => require(['views/admin/order/pay_result'], resolve)
          }
        ]
      },
    ]
  },
  {
    path: '*',
    component: resolve => require(['views/error'], resolve)
  }
]

if(_DEV_) {  // 开发模式下使用登录路由，生产模式下有单独的 html 页面
  routes.push({
    path: '/login',
    component: resolve => require(['views/login'], resolve)
  })
}

export default routes
