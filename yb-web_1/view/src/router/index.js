import Vue from 'vue'
import Router from 'vue-router'
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
    {
      path: '/',
      component: resolve => require(['@/components/context/index'], resolve),
      children: [
        {
          path: '/',
          component: resolve => require(['@/components/context/homepage'], resolve)
        }, 
        {
          path: '/control',
          component: resolve => require(['@/components/context/controlpage'], resolve),
          children: [
            {
              path: '/',
              component: resolve => require(['@/components/context/control/sysconfg/userlink'], resolve)
            },
            {
              path: '/user_info',
              component: resolve => require(['@/components/context/control/sysconfg/userlink'], resolve)
            },          
            {
              path: '/netaddr',
              component: resolve => require(['@/components/context/control/sysconfg/netaddr'], resolve)
            },
            {
              path: '/vpnserver',
              component: resolve => require(['@/components/context/control/sysconfg/vpnserver'], resolve)
            },
            {
              path: '/vpn_maped',
              component: resolve => require(['@/components/context/control/sysconfg/vpninfo'], resolve)
            },
            {
              path: '/connect_back',
              component: resolve => require(['@/components/context/control/sysconfg/backlink'], resolve)
            },
            {
              path: '/camouflage',
              component: resolve => require(['@/components/context/control/sysconfg/maskpool'], resolve)
            },
            {
              path: '/grule',
              component: resolve => require(['@/components/context/control/sysconfg/grule'], resolve)
            },
            {
              path: '/realtime',
              component: resolve => require(['@/components/context/control/realtime/'], resolve)
            },
            {
              path: '/sys_status',
              component: resolve => require(['@/components/context/control/realtime/equipment'], resolve)
            },
            {
              path: '/linkinfo',
              component: resolve => require(['@/components/context/control/realtime/linkinfo'], resolve)
            },
            {
              path: '/g_status',
              component: resolve => require(['@/components/context/control/realtime/topo'], resolve)
            },
            {
              path: '/account_manage',
              component: resolve => require(['@/components/context/userinfo/'], resolve)
            }
          ]
        }
        
      ]
    }
  ]
})
