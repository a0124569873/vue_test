import Vue from 'vue'
import Router from 'vue-router'
Vue.use(Router)
export default new Router({
  routes: [
    {
      path: '/',
      component: resolve => require(['@/components/main'], resolve),
      children: [
        {
          path: "/",
          component: resolve => require(['@/components/content/main'], resolve)
        },
        {
          path: "/main",
          component: resolve => require(['@/components/content/main'], resolve)
        },
        {
          path: "/leftbar",
          component: resolve => require(['@/components/content/leftbar'], resolve),
          children: [
            {
              path: '/',
              component: resolve => require(["@/components/content/leftbar/news"], resolve)
            },
            {
              path: '/news',
              component: resolve => require(["@/components/content/leftbar/news"], resolve)
            },
            {
              path: '/movie',
              component: resolve => require(["@/components/content/leftbar/movie"], resolve)
            },
            {
              path: '/article',
              component: resolve => require(["@/components/content/leftbar/article"], resolve)
            },
            {
              path: '/usersetting',
              component: resolve => require(["@/components/content/leftbar/usersetting"], resolve)
            },
          ]
        },
      ]
    }
  ]
})
