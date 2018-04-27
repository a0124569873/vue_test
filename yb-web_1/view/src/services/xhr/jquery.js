import { rootPath } from './config'
import Vue from 'vue'
import messages from 'components/common/messages'
import store from '@/vuex/store'
Vue.prototype.$t = function (val) {
  let firstKey = val.split('.')[0]
  let secondeKey = val.split('.')[1].toString()
  return messages.zh[firstKey][secondeKey] ? messages.zh[firstKey][secondeKey] : undefined
}

const xhr = ({ url, body = null, method = 'get', contentType = 'application/x-www-form-urlencoded' }) => {
  const defer = $.Deferred()
  
  const devUrl = rootPath + debugUrl(url)
  const prodUrl = rootPath + url

  if (contentType.toLocaleLowerCase() === 'json') {
    if (method.toLocaleLowerCase() === 'post') {
      body = JSON.stringify(body)
    }
    contentType = 'application/json'
  }

  $.ajax({
    type: method,
    url: _DEV_ ? devUrl : prodUrl,
    contentType: contentType,
    dataType: 'json',
    data: body
  })
    .done(function (res) {
      Vue.prototype.$message.close()
      if (res.errcode === '0' || res.errcode === 0) {
        return defer.resolve({...res, errcode: 0})
      } else {
        if (res.errcode === '12001' || res.errcode === 12001) {
          window.sessionStorage.setItem('login', false)
          store.state.login = false
          store.state.status++
          if (url !== 'admin/islogin' && url !== 'user/login' && url !== 'user/islogin') {
            Vue.prototype.$message({
              dangerouslyUseHTMLString: true,
              showClose: true,
              message: '<a style="color:#fa5555" href="/login">需要登录, 点击登录</a>',
              type: 'error',
              duration: 0
            })
          }
        } else if (res.errcode === '11024' || res.errcode === 11024) {
          if (res.errmsg) {
            Vue.prototype.$message({
              showClose: true,
              message: res.errmsg,
              type: 'error',
              duration: 0
            })
          }
          return defer.resolve({...res, errcode: Number(res.errcode)})
        } else {
          if (url !== 'user/login' && url !== 'user/islogin') {
            Vue.prototype.$message({
              showClose: true,
              message: Vue.prototype.$t('error_code.' + res.errcode),
              type: 'error',
              duration: 0
            })
          }
        }
        return defer.resolve({...res, errcode: Number(res.errcode)})
      }
    })
    .fail(function (res) {
      Vue.prototype.$message.close()
      let message = '服务异常或者检查网络, 请刷新重试'
      let errcode = res.responseJSON ? res.responseJSON.errcode : res.errcode
      if (errcode) {
        message = Vue.prototype.$t('error_code.' + errcode)
      } 
      if (errcode === '12001' || errcode === 12001) {
        window.sessionStorage.setItem('login', false)
        store.state.login = false
        Vue.prototype.$message({
          showClose: true,
          message: message,
          type: 'error',
          duration: 0
        })
        return defer.reject(res)
      }

      if (errcode === '11024' || errcode === 11024) {
        return defer.reject(res)
      }

      Vue.prototype.$message({
        showClose: true,
        message: message,
        type: 'error',
        duration: 0
      })
      if (/^5\d\d$/.test(res.status)) {
        return defer.reject(res)
      } 
    })

  return defer.promise()
}

function debugUrl (url) {
  let ret = ''
  if (url.indexOf('?') !== -1) {
    ret = url + '&debug=true'
  } else {
    ret = url + '?debug=true'
  }
  return ret
}
export default xhr
