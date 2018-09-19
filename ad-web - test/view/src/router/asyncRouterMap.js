const asyncRouterMap = [
  {
    path: '/',
    component: resolve => require(['@/components/context/index'], resolve),
    meta: { admin: true, user: true },
    children: [
      {
        path: '',
        component: resolve => require(['@/components/context/homepage'], resolve),
        meta: { admin: true, user: true }
      }, 
      {
        path: 'control',
        component: resolve => require(['@/components/context/controlpage'], resolve),
        meta: { admin: true, user: true },
        children: [
          {
            path: '',
            component: resolve => require(['@/components/context/control/sysconfg/netaddress'], resolve),
            meta: { admin: true, user: false }
          },
          // 系统配置
          {
            path: 'netaddress',
            component: resolve => require(['@/components/context/control/sysconfg/netaddress'], resolve),
            meta: { admin: true, user: false }
          },
          // 系统配置
          {
            path: 'netaddress_c',
            component: resolve => require(['@/components/context/control/sysconfg/netaddress_c'], resolve),
            meta: { admin: true, user: false }
          },
          // 防御配置
          {
            path: 'globalconfig',
            component: resolve => require(['@/components/context/control/sysconfg/globalconfig'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'tcp',
            component: resolve => require(['@/components/context/control/sysconfg/tcp'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'udp',
            component: resolve => require(['@/components/context/control/sysconfg/udp'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'udpp',
            component: resolve => require(['@/components/context/control/sysconfg/udpp'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'blacklist',
            component: resolve => require(['@/components/context/control/sysconfg/blacklist'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'whitelist',
            component: resolve => require(['@/components/context/control/sysconfg/whitelist'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'dnswhitelist',
            component: resolve => require(['@/components/context/control/sysconfg/dnswhitelist'], resolve),
            meta: { admin: true, user: false }
          },
          {
            path: 'protectscope',
            component: resolve => require(['@/components/context/control/sysconfg/protectScope'], resolve),
            meta: { admin: true, user: false }
          },
          // 实时监控
          {
            path: 'realtime',
            component: resolve => require(['@/components/context/control/realtime'], resolve),
            meta: { admin: true, user: true }
          },
          // 实时监控
          {
            path: 'realtime_c',
            component: resolve => require(['@/components/context/control/realtime/realtime_c'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'realtime/:ip',
            component: resolve => require(['@/components/context/control/realtime'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'sysstatus',
            component: resolve => require(['@/components/context/control/realtime/sysstatus'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'netstatus',
            component: resolve => require(['@/components/context/control/realtime/netstatus'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'hoststatus',
            component: resolve => require(['@/components/context/control/realtime/hoststatus'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'hoststatus_c',
            component: resolve => require(['@/components/context/control/realtime/hoststatus_c'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'tmpblacklist',
            component: resolve => require(['@/components/context/control/realtime/blacklist'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'tmpwhitelist',
            component: resolve => require(['@/components/context/control/realtime/whitelist'], resolve),
            meta: { admin: true, user: true }
          },
          // 数据报表
          {
            path: 'attack',
            component: resolve => require(['@/components/context/control/log/attack'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'flow',
            component: resolve => require(['@/components/context/control/log/flow'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'connect',
            component: resolve => require(['@/components/context/control/log/connect'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'cleanlogs',
            component: resolve => require(['@/components/context/control/log/cleanlogs'], resolve),
            meta: { admin: true, user: true }
          },
          // 设备授权
          {
            path: 'userinfo',
            component: resolve => require(['@/components/context/userinfo/'], resolve),
            meta: { admin: true, user: true }
          },
          {
            path: 'sysinfo',
            component: resolve => require(['@/components/context/userinfo/sysinfo'], resolve),
            meta: { admin: true, user: true }
          }
        ]
      }
    ]
  },
  {
    path: '*',
    redirect: '/errorpage',
    meta: { admin: true, user: true }
  }
]
// let accessed = {
//   accessedRouters: [],
//   setRouterMap (name) {
//     this.accessedRouters = this.filterAsyncRouter(asyncRouterMap, name)
//   },
//   filterAsyncRouter (asyncRouterMap, roles) {
//     const accessedRouters = asyncRouterMap.filter(route => {
//       if (route.meta[roles]) {
//         if (route.children && route.children.length) {
//           route.children = this.filterAsyncRouter(route.children, roles)
//         }
//         return true
//       }
//       return false
//     })
//     return accessedRouters
//   }
// }
export default asyncRouterMap
