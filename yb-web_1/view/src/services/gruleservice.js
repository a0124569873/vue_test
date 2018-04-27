import xhr from './xhr'
class GRuleService {
  getGRule () {
    return xhr({
      url: 'settings/get?type=1',
      method: 'get'
    })
  }
  
  setGRule (params) {
    return xhr({
      url: 'settings/set?type=1',
      method: 'post',
      contentType: 'json',
      body: params
    })
  }
}

export default new GRuleService()
