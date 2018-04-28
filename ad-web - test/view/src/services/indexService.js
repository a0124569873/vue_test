import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class IndexService {
  login (account, password, code) {
    return xhr({
      url: 'user/login',
      method: 'post',
      body: {
        username: account,
        password: password,
        captcha: code
      }
    })
  }

  logout () {
    return xhr({url: 'user/logout'})
  }

  islogin () {
    return xhr({url: 'user/islogin'})
  }
  online (data) {
    return xhr({
      url: 'sysconf/online_status',
      method: 'post',
      body: {
        oper: data.oper,
        status: data.status 
      }
    })
  }

  changePwd (oldPwd, newPwd) {
    return xhr({
      url: 'user/chpwd',
      method: 'post',
      body: {
        old_pwd: oldPwd,
        new_pwd: newPwd 
      }
    })
  }

  getEmail () {
    return xhr({
      url: 'settings/get?type=9',
      method: 'GET'
    })
  }
  setEmail (email) {
    return xhr({
      url: 'settings/set?type=9',
      method: 'POST',
      body: {
        '9': email
      }
    })
  }
  
  getCert () {
    return xhr({
      url: 'license/read',
      method: 'GET'
    })
  }

  homeData () {
    return xhr({
      url: 'system/get?t=1|2|3|4|5|7|8',
      method: 'GET'
    })
  }
  //首页实时流量监控
  senseData (){
    return xhr({
      url: 'system/get?t=6',
      method: 'GET'
    })
  }
  // 获取总阈值
  getHostshold (type) {
    return xhr({
      url: `settings/get?type=5`,
      method: 'GET'
    })
  }
  getToken () {
    return xhr({
      url: 'user/gettoken'
    })
  }
  setToken () {
    return xhr({
      url: 'user/freshtoken'
    })
  }
}

// 实例化后再导出
export default new IndexService()
