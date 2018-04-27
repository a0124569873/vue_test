import xhr from './xhr/'
class CurrentService {
  getState (type) {
    return xhr({
      url: 'stats/dev?t=1|2|3&r=' + type,
      method: 'get'
    })
  }
  getFlow (type, IP = '') {
    if (IP === '' || IP === 'all') {
      return xhr({
        url: 'stats/flow?r=' + type,
        method: 'get'
      })
    }
    return xhr({
      url: 'stats/flow?r=' + type + '&ip=' + IP,
      method: 'get'
    })
  }
  getBlackHole () {
    return xhr({
      url: 'stats/black_hole'
    })
  }
  setTraction (ip) {
    return xhr({
      url: 'guide/set',
      method: 'post',
      body: {
        ip: ip,
        do: '1'
      }
    })
  }
}
export default new CurrentService()
