import xhr from './xhr/'

/**
 * 对应后端涉及到用户认证的 API
 */
class LinkInfoService {
  getLinkInfo (page = 1, row = 20) {
    return xhr({
      url: `stats/dev?t=4&page=${page}&row=${row}`,
      method: 'GET'
    })
  }
}

// 实例化后再导出
export default new LinkInfoService()
