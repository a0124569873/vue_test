import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class ControlService {
  getData (type) {
    return xhr({
      url: `settings/get?type=${type}`,
      method: 'GET'
    })
  }
  setData (type, sentdata) {
    return xhr({
      url: `settings/set?type=${type}`,
      method: 'POST',
      body: sentdata
    })
  }

  getSSHConfig () {
    return xhr({
      url: 'settings/get?type=3',
      method: 'GET'
    })
  }
  setSSHConfig (params) {
    return xhr({
      url: 'settings/set?type=3',
      method: 'POST',
      body: params
    })
  }
  delSSHConfig (params) {
    return xhr({
      url: 'settings/del?type=3',
      method: 'POST',
      body: params
    })
  }
  getRank ({orderBy = 'flow_in', limit = 10}) {
    return xhr({
      url: 'stats/rank',
      method: 'GET',
      body: {
        orderby: orderBy,
        limit: limit
      }
    })   
  }
  // 手动牵引
  delGuide (ip) {
    return xhr({
      url: 'guide/set',
      method: 'POST',
      body: {
        ip: ip,
        do: '0'
      }
    }) 
  }
  getHostConfig (page, pageSize, likeIp = '') {
    if (likeIp) {
      return xhr({
        url: 'settings/get?type=4&row=' + pageSize + '&page=' + page + '&ip=' + likeIp
      })
    }
    return xhr({
      url: 'settings/get?type=4&row=' + pageSize + '&page=' + page
    })
  }
  setHostConfig (config) {
    return xhr({
      url: 'settings/set?type=4',
      method: 'POST',
      body: {
        4: config
      }
    }) 
  } 
  delHostConfig (ids) {
    return xhr({
      url: 'settings/del?type=4',
      method: 'POST',
      body: {
        4: ids
      }
    }) 
  }
  clearHostConfig () {
    return xhr({
      url: 'settings/del?type=4',
      method: 'GET'
    }) 
  }
  getWhiteList (page, size = 10, like = '') {
    if (like) {
      return xhr({
        url: 'settings/get?type=7&row=' + size + '&page=' + page + '&ip=' + like
      })
    }
    return xhr({
      url: 'settings/get?type=7&row=' + size + '&page=' + page
    })
  }
  setWhiteList (ips) {
    return xhr({
      url: 'settings/set?type=7',
      method: 'post',
      body: {
        7: ips
      }
    })
  }
  /**
   * ips 是以，分隔的字符串
   * @param {*} ids 
   */
  delWhiteList (ids) {
    return xhr({
      url: 'settings/del?type=7',
      method: 'post',
      body: {
        7: ids
      }
    })
  }

  // 用户管理
  getUsers(){
    return xhr({
      url: 'admin/users',
      method: 'get'
    })
  }

  addUsers(username,password){
    return xhr({
      url: 'admin/users?oper=add',
      method: 'POST',
      body:{
        username:username,
        password:password,
      }
    })
  } 

  delUsers(ids){
    return xhr({
      url: 'admin/users?oper=del',
      method: 'POST',
      body:{
        ids:ids
      }
    })
  } 
}

// 实例化后再导出
export default new ControlService()
