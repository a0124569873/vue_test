import xhr from './xhr/'
class FlowService {
  getFlow () {
    return xhr({
      url: 'report/get?t=2',
      method: 'get'
    })
  }
}

export default new FlowService()
