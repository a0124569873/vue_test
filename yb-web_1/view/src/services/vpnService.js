import xhr from './xhr'
class VPNService {
  getVPN (page, row) {
    return xhr({
      url: `vpnmap/get?page=${page}&row=${row}`,
      method: 'get'
    })
  }
  
  addVPN (vpn) {
    return xhr({
      url: 'vpnmap/add',
      method: 'POST',
      contentType: 'JSON',
      body: {
        'vpn': vpn
      }
    })
  }

  delVPN (ids) {
    return xhr({
      url: 'vpnmap/del',
      method: 'POST',    
      body: {
        'ids': ids
      }
    })
  }
 
  clearVPN () {
    return xhr({
      url: 'vpnmap/del?clear=true',
      method: 'POST',    
      body: {
        'clear': true
      }
    })
  }

  getVPNServer (page = 1, row = 10) {
    return xhr({
      url: `settings/get?type=2&page=${page}&row=${row}`,
      method: 'get'
    })
  }
  
  createVPNServer (params) {
    return xhr({
      url: 'settings/set?type=2',
      method: 'POST',
      contentType: 'json',
      body: params
    })
  }

  updateVPNserver (params) {
    return xhr({
      url: 'settings/set?type=2&update=true',
      method: 'POST',
      contentType: 'json',
      body: params
    })
  }
  
  delVPNServer (params) {
    return xhr({
      url: 'settings/del?type=2',
      method: 'POST',
      contentType: 'json',   
      body: params
    })
  }

  clearVPNServer () {
    return xhr({
      url: 'settings/del?type=2',
      method: 'POST'
    })
  }
}

export default new VPNService()
