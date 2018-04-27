export default class Pagination {
  /**
   * @param {Array} data 原始数据 
   * @param {Number} pageSize 每页显示条数
   */
  constructor (data, pageSize) {
    let ret = []
    for (let ip in data) {
      ret.push(data[ip])
    }
    this.data = ret
    this.pageSize = pageSize
    this.totalPage = Math.ceil(this.data.length / pageSize)
  }

  // @param {Number} page 页码
  page (page) {
    if (!Array.isArray(this.data) || this.totalPage < 1 || page > this.totalPage || page < 1) {
      return []
    }

    let index = this.pageSize * (page - 1)
    return this.data.slice(index, index + this.pageSize)
  }
}
