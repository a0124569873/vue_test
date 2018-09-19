import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class ControlService {
  getGlobalData(type, gether) {
    if (gether != null) {
      return xhr({
        url: `protect/get?t=${type}&gether=${gether}`,
        method: 'GET'
      })
    } else {
      return xhr({
        url: `protect/get?t=${type}`,
        method: 'GET'
      })
    }
  }
  setGlobalData(type, data) {
    return xhr({
      url: `protect/update?t=${type}`,
      method: 'post',
      body: data
    })
  }
  getTcpProt(data) {
    return xhr({
      url: `protect/get?t=${data.type}&gether=${data.gether}`,
      method: 'GET'
    })
  }
  addTcpPort(data) {
    return xhr({
      url: `protect/add?t=4`,
      method: 'post',
      body: data
    })
  }
  updateTcpPort(data) {
    return xhr({
      url: `protect/update?t=4`,
      method: 'post',
      body: data
    })
  }
  delTcpPort(data) {
    return xhr({
      url: `protect/del?t=4`,
      method: 'post',
      body: data
    })
  }
  getUdpProt(data) {
    return xhr({
      url: `protect/get?t=${data.type}&gether=${data.gether}`,
      method: 'GET'
    })
  }
  addUdpPort(data) {
    return xhr({
      url: `protect/add?t=5`,
      method: 'post',
      body: data
    })
  }
  updateUdpPort(data) {
    return xhr({
      url: `protect/update?t=5`,
      method: 'post',
      body: data
    })
  }
  delUdpPort(data) {
    return xhr({
      url: `protect/del?t=5`,
      method: 'post',
      body: data
    })
  }

  // 获取黑白名单, type= 1: 黑名单，0：白名单
  getBlackWhiteList({page = 1, row = 10, ip = '', filterIp = '', gether = 0, type = '1'} = {}) {
    return xhr({
      url: `protect/get?t=6&page=${page}&row=${row}&gether=${gether}&filter_ip=${filterIp}&list_type=${type}`,
      method: 'get'
    })
  }

  getDnsWhiteList({page = 1, row = 10, ip = '', filterIp = '', gether = 0} = {}) {
    return xhr({
      url: `protect/get?t=8&page=${page}&row=${row}&gether=${gether}&filter_ip=${filterIp}`,
      method: 'get'
    })
  }

  getIpRangeDetail({page = 1, row = 10, ip = '', gether = 0, type = '1'} = {}) {
    return xhr({
      url: `protect/get?t=6&page=${page}&row=${row}&gether=${gether}&ip_range=${ip}&list_type=${type}`
    })
  }

  addBlackWhiteList(params) {
    return xhr({
      url: 'protect/add?t=6',
      method: 'post',
      body: params
    })
  }
  //添加dns白名单
  addDnsWhiteList(params) {
    return xhr({
      url: 'protect/add?t=8',
      method: 'post',
      body: params
    })
  }

  /**
   * ips 是以，分隔的字符串
   * @param {*} ips 
   */
  delBlackWhiteList(params) {
    return xhr({
      url: 'protect/del?t=6',
      method: 'post',
      body: params
    })
  }
  //删除dns白名单
  delDnsWhiteList(params) {
    return xhr({
      url: 'protect/del?t=8',
      method: 'post',
      body: params
    })
  }
  // 清空黑白名单
  clearBlackWhiteList(params) {
    return xhr({
      url: 'protect/clear?t=6',
      method: 'post',
      body: params
    })
  }

  //清空dns白名单
  clearDnsWhiteList (params) {
    return xhr({
      url: 'protect/clear?t=8',
      method: 'post',
      body: params
    })
  }

  // 用户管理
  getUsers() {
    return xhr({
      url: 'admin/users',
      method: 'get'
    })
  }

  addUsers(username, password) {
    return xhr({
      url: 'admin/users?oper=add',
      method: 'POST',
      body: {
        username: username,
        password: password
      }
    })
  } 

  delUsers(ids) {
    return xhr({
      url: 'admin/users?oper=del',
      method: 'POST',
      body: {
        ids: ids
      }
    })
  } 
  getProtectScope({page = 1, row = 10, ip = ''} = {}) {
    return xhr({
      url: `protect/get?t=7&row=${row}&page=${page}&ip=${ip}`,
      method: 'GET'
    })
  }

  getProtectDetail({ipRange = '', page = 1, row = 10}) {
    return xhr({
      url: `protect/get?t=7&ip_range=${ipRange}&page=${page}&row=${row}`,
      method: 'GET'
    })
  }

  addProtect(params) {
    return xhr({
      url: 'protect/add?t=7',
      method: 'POST',
      body: params
    })
  }

  editProtect(params, type) {
    let url = ''
    if (type === 'multi') {
      url = 'protect/update?t=7&multi=true'
    } else {
      url = 'protect/update?t=7'
    }
    return xhr({
      url: url,
      method: 'POST',
      body: params
    })
  }

  delProtect(params) {
    return xhr({
      url: 'protect/del?t=7',
      method: 'POST',
      body: params
    })
  }
  // 网络地址配置
  getNetAddr() {
    return xhr({
      url: 'sysconf/get?t=1',
      method: 'get'
    })
  }
  setNetAddrData(params) {
    return xhr({
      url: 'sysconf/update?t=1',
      method: 'post',
      body: params
    })
  }

  // 获取网卡汇聚
  getEthNet() {
    return xhr({
      url: 'sysconf/get?t=2',
      method: 'get'
    })
  }

  // 更新，添加汇聚网卡，物理网卡
  updateOrCreateEthNet(params) {
    return xhr({
      url: 'sysconf/update?t=2',
      method: 'post',
      body: params
    })
  }

  //获取管理口
  getMgp() {
    return xhr({
      url: 'sysconf/get?t=3',
      method: 'get'
    })
  }
  //设置管理口
  setMgp(params, type) {
    return xhr({
      url: `sysconf/update?t=3&type=${type}`,
      method: 'post',
      body: params
    })
  }

}

// 实例化后再导出
export default new ControlService()
