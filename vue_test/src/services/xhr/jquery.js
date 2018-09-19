// import { rootPath } from './config'
import Vue from 'vue'
// import messages from 'components/common/messages'
// import store from '@/vuex/store'
Vue.prototype.$t = function (val) {
  let firstKey = val.split('.')[0]
  let secondeKey = val.split('.')[1].toString()
  // return messages.zh[firstKey][secondeKey] ? messages.zh[firstKey][secondeKey] : undefined
  return undefined;
}

const xhr = ({ url, body = null, method = 'get', other = {} }) => {
  const defer = $.Deferred()
  
  // const devUrl = rootPath + debugUrl(url)
  // const prodUrl = rootPath + url

  if (method.toLocaleLowerCase() === 'post') {
    body = JSON.stringify(body)
  }

  $.ajax({
    type: method,
    // url: _DEV_ ? devUrl : prodUrl,
    url:url,
    contentType: 'application/json;',
    dataType: 'json',
    data: body,
    ...other
  })
    .done(function (res) {
      if (Number(res.errcode) === 0) {
        return defer.resolve({...res, errcode: 0})
      } else {
        if (Number(res.errcode) === 12001) {
          window.sessionStorage.setItem('login', false)
          // store.state.login = false
          if (url !== 'user/login' && url !== 'user/islogin') {
            Vue.prototype.$msgbox({
              title: '会话超时',
              message: `${Vue.prototype.cryImg}<a style="color:#fa5555;font-size:16px" href="/login">需要登录, 点击登录</a>`,
              closeOnClickModal: false,
              showConfirmButton: false,
              showCancelButton: false,
              lockScroll: true,
              showClose: false,
              dangerouslyUseHTMLString: true
            })
            return
          }
        } else {
          Vue.prototype.$msgbox({
            title: '提示',
            message: `${Vue.prototype.cryImg}${Vue.prototype.$t('error_code.' + res.errcode)}`,
            lockScroll: true,
            showCancelButton: false,
            dangerouslyUseHTMLString: true
          })
        }
        return defer.resolve({...res, errcode: Number(res.errcode)})
      }
    })
    .fail(function (res) {
      let message = '服务异常或者检查网络, 请刷新重试'
      let errcode = res.responseJSON ? res.responseJSON.errcode : res.errcode
      if (Number(errcode) === 12001) {
        window.sessionStorage.setItem('login', false)
        // store.state.login = false
        return defer.reject(res)
      }

      if (errcode) {
        message = Vue.prototype.$t('error_code.' + errcode)
      } 

      Vue.prototype.$msgbox({
        title: '提示',
        message: `${Vue.prototype.cryImg}${message}`,
        lockScroll: true,
        showCancelButton: false,
        dangerouslyUseHTMLString: true
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
