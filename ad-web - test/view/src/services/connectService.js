import xhr from './xhr/'
class ConnectService {
  getReport () {
    return xhr({
      url: 'report/get?t=2',
      method: 'get'
    })
  }
}

export default new ConnectService()
