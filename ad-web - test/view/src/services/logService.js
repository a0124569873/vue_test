import xhr from './xhr/'
class LogService {
  toType (obj) {
    return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
  }
  // 参数过滤函数
  filterNull (o) {
    for (var key in o) {
      if (!o[key]) {
        delete o[key]
      }
      if (this.toType(o[key]) === 'string') {
        o[key] = o[key].trim()
      } else if (this.toType(o[key]) === 'object') {
        o[key] = this.filterNull(o[key])
      } else if (this.toType(o[key]) === 'array') {
        o[key] = this.filterNull(o[key])
      }
    }
    return o
  }
  getAttackLogs (data) {
    return xhr({
      url: 'logs/get?t=1',
      method: 'get',
      body: this.filterNull(data)
    })
  }
  getFlowlogs (data) {
    return xhr({
      url: 'logs/get?t=2',
      method: 'get',
      body: this.filterNull(data)
    })
  }
  getConnlogs (data) {
    return xhr({
      url: 'logs/get?t=3',
      method: 'get',
      body: this.filterNull(data)
    })
  }
  getCleanlogs (data) {
    return xhr({
      url: 'logs/get?t=4',
      method: 'get',
      body: this.filterNull(data)
    })
  }
}
export default new LogService()
