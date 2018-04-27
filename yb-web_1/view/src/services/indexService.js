import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class IndexService {
  login (account, password, code) {
    return xhr({
      url: 'admin/login',
      method: 'post',
      body: {
        account: account,
        password: password,
        captcha: code
      }
    })
  }

  logout () {
    return xhr({url: 'admin/logout'})
  }

  islogin () {
    return xhr({url: 'admin/islogin'})
  }

  changePwd (oldPwd, newPwd) {
    return xhr({
      url: 'admin/chpwd',
      method: 'post',
      body: {
        old_pwd: oldPwd,
        new_pwd: newPwd 
      }
    })
  }

  homeData () {
    return xhr({
      url: 'stats/dev?t=1|2|3|5&r=1',
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
}

// 实例化后再导出
export default new IndexService()
