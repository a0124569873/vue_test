import xhr from './xhr/'
class LogService {
  getBlackHole (page = 1, row = 10, ip = '', time = '', clear) {
    if (clear) {
      return xhr({
        url: 'logs/black_hole',
        method: 'get',
        body: {
          page: page,
          row: row,
          ip: ip,
          time: time,
          clear: clear
        }
      })
    }
    return xhr({
      url: 'logs/black_hole',
      method: 'get',
      body: {
        page: page,
        row: row,
        ip: ip,
        time: time
      }
    })
  }
}
export default new LogService()
