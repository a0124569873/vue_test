// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
// import asyncRouter from '@/router/asyncRouterMap'
import ElementUI from 'element-ui'
import store from '@/vuex/store'
import indexService from 'services/indexService'
import Utils from '@/utils'
import '@/assets/icon/iconfont.css'
import '@/theme/default/index.scss'
import '@/theme/theme-blue/index.scss'

window.code_version = __COMMIT__

Vue.prototype.cryImg = `<img src="${require('@/components/img/cry.png')}" style="margin-right: 10px"/>`
Vue.prototype.theme = 'theme-' + (__THEME__ || 'red')// 主题

if (window.localStorage.getItem('theme')) {
  Vue.prototype.theme = window.localStorage.getItem('theme')
} 

Vue.config.productionTip = false
Vue.use(ElementUI, { size: 'mini' })
Vue.use(Utils)
Vue.prototype.__ID__ = {// 用户信息
  id: window.sessionStorage.getItem('groupId'),
  name: window.sessionStorage.getItem('name'),
  setId: function (newId) {
    this.id = newId
  },
  setName: function (newName) {
    this.name = newName
  }
}
Vue.prototype.$checkPermisstion = function (name) { // 权限校验
  if (!name) {
    return false
  }
  name = name.toString()
  if (name.includes('保存') || name.includes('添') || name.includes('删') || name.includes('清') || name.includes('牵引') || name.includes('上')) {
    if (Number(Vue.prototype.__ID__.id) === 1) {
      return true
    } else {
      return false
    }
  } else {
    if (Number(name) === 1) {
      return true
    } else {
      return false
    }
  }
}

store.state.login = true
window.sessionStorage.setItem('login', true)
window.sessionStorage.setItem('groupId', 1)
window.sessionStorage.setItem('name', "admin")
Vue.prototype.__ID__.id = 10
Vue.prototype.__ID__.name = "admin"

var cert = {'status': 'valid', 'id': '7b2d69413d410e8b', 'desc': '幻盾清洗设备测试证书', 'lang': 'chinese', 'licence_owner': '卫达安全', 'copy_right': '北京卫达信息技术有限公司', 'type': 'official', 'device_id': 'de0146dd308ac4f5', 'user': '卫达安全', 'model': '幻盾清洗设备', 'create_time': 1516263110, 'start_time': 1516263095, 'end_time': 1546272000, 'max_hosts': 111, 'max_flows': 100000, 'alive_time': 29734709, 'tick_count': 20558, 'utc_timestamp': 1516863193}
store.state.cert = cert


bootApp()
beforeRouteHandler()







// if (window.sessionStorage.getItem('login') === 'true') {
if (1) {
  store.state.login = true
  // getCertStatus()
} else {
  store.state.login = true
  window.sessionStorage.setItem('login', true)
  window.sessionStorage.setItem('groupId', 10)
  window.sessionStorage.setItem('name', "admin")
  Vue.prototype.__ID__.id = 10
  Vue.prototype.__ID__.name = "admin"
}
// 界面初始化
function bootApp () {
  Vue.prototype.VM = new Vue({
    el: '#app',
    router,
    store,
    template: '<App/>', 
    components: { App }
  })
}

function beforeRouteHandler () {
  router.beforeEach((to, from, next) => {
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
  })
}

function getCertStatus () {
  // indexService.getCert()
  //   .then((res) => {
      // if (res.errcode === 0) { 
      if (1) { 
        var res = {}
        res.cert = {'status': 'valid', 'id': '7b2d69413d410e8b', 'desc': '幻盾清洗设备测试证书', 'lang': 'chinese', 'licence_owner': '卫达安全', 'copy_right': '北京卫达信息技术有限公司', 'type': 'official', 'device_id': 'de0146dd308ac4f5', 'user': '卫达安全', 'model': '幻盾清洗设备', 'create_time': 1516263110, 'start_time': 1516263095, 'end_time': 1546272000, 'max_hosts': 111, 'max_flows': 100000, 'alive_time': 29734709, 'tick_count': 20558, 'utc_timestamp': 1516863193}
        if (res.cert.status !== 'valid') {
          router.push('/')
        } else {
          store.state.cert = res.cert
          // if (store.state.login === true) {
            // router.push('/')
          // }
        }
      }
    // }).fail(() => {
    //   bootApp()
    //   beforeRouteHandler() 
    //   router.push('/sysinfo')
    // })
}
