import xhr from './xhr'
class BacklinkService {
  getBacklink (page, row) {
    return xhr({
      url: `relink/get?page=${page}&row=${row}`,
      method: 'get'
    })
  }

  addBacklink (link) {
    return xhr({
      url: 'relink/set',
      method: 'POST',
      contentType: 'json',
      body: {
        'link': link
      }
    })
  }

  delBacklink (ids) {
    return xhr({
      url: 'relink/del',
      method: 'POST',
      body: {
        'uids': ids
      }
    })
  }

  clearBacklink () {
    return xhr({
      url: 'relink/del?clear=true',
      method: 'POST',
      body: {
        'clear': true
      }
    })
  }

  getPriIP () {
    return xhr({
      url: 'uconnect/getpip',
      method: 'get'
    })
  }
}
export default new BacklinkService()
