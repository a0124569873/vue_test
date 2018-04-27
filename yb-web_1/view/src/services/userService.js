import xhr from './xhr/'
class UserService {
  getUsers (page = 1, row = 10) {
    return xhr({
      url: 'uconnect/get',
      method: 'get',
      body: {
        page,
        row
      }
    })
  }

  addUser (params) {
    return xhr({
      url: 'uconnect/add',
      method: 'post',
      body: params
    })
  }

  updateUser (params) {
    return xhr({
      url: 'uconnect/update',
      method: 'post',
      body: params
    })
  }
  
  delUser (params) {
    return xhr({
      url: 'uconnect/del',
      method: 'post',
      body: params
    })
  }

  getUids () {
    return xhr({
      url: 'uconnect/getalluid',
      method: 'get'
    })
  }

  getMaskPool () {
    return xhr({
      url: 'uconnect/getalluid',
      method: 'get'
    })
  }

  // 获取VPN server
  getVPNServers () {
    return xhr({
      url: 'uconnect/getAllVpnServerIp',
      method: 'get'
    })
  }
  
  // 获取伪装原型IP
  getMaskIp () {
    return xhr({
      url: 'maskpools/getall',
      method: 'get'
    })
  }
}
export default new UserService()
