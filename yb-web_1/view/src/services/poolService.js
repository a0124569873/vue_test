import xhr from './xhr/'

// 伪装原型池
class PoolService {
  getPool (page = 1, row = 10) {
    return xhr({
      url: `maskpool/get?page=${page}&row=${row}`,
      method: 'GET'
    })
  }
  addPool (params) {
    return xhr({
      url: 'maskpool/add',
      method: 'POST',
      contentType: 'json',
      body: params
    })
  }
  delPool (params) {
    return xhr({
      url: 'maskpool/del',
      method: 'POST',
      body: params
    })
  }
}

// 实例化后再导出
export default new PoolService()
