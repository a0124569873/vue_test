export default class PageData {
  /**
   * @param {Array} list 原始数据 
   * @param {Number} row 每页显示条数
   */
  constructor (list, row) {
    this.list = list
    this.column = row
    this.totalpage = Math.ceil(this.list.length / this.column)
  }

  /**
   * @param {Number} page 页码
   */
  data (page) {
    if (!Array.isArray(this.list) || page > Math.ceil(this.list.length / this.column) || page < 1) {
      return []
    }

    let i = this.column * (page - 1)

    return this.list.slice(i, i + this.column)
  }
}
