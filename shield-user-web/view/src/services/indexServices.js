import xhr from './xhr/'

class IndexService {
  login (username, password, usercode) {
    return xhr({
      url: 'login',
      method: 'POST',
      body: {
        email: username,
        password: password,
        captcha: usercode
      }
    })
  }

  logout () {
    return xhr({url: 'logout', method: 'delete'})
  }

  isloginfo () {
    return xhr({url: 'loginfo', method: 'get'})
  }

  getPersonalInfo () {
    return xhr({url: 'personal/get_profile'})
  }
  getManage () {
    return xhr({
      url: '/manage',
      method: 'get'
    })
  }
}

export default new IndexService()
