export function convertFlow (bps, type = 'b', digit = 2) {
  /**
   * 保留小数位数
   * @augments num {Number} 
   * @augments digit {Number} 需要保留的位数
   * @returns {Number} 如果是整数，直接返回数字，否则返回截取过后的字符串
   */
  const fixedNumber = function (num, digit) {
    if (Number.isNaN(num)) {
      return num
    }
    let numStr = String(num)
    let dotIndex = numStr.indexOf('.')
    if (dotIndex !== -1) {
      if (digit === 0) {
        return Number(numStr.slice(0, dotIndex))
      }
      return Number(numStr.slice(0, dotIndex + digit + 1))
    } else {
      return num
    }
  }

  const [Kb, Mb, Gb] = [1024, 1024 * 1024, 1024 * 1024 * 1024]
  const units = {
    b: ['b/s', 'Kb/s', 'Mb/s', 'Gb/s'],
    B: ['B/S', 'KB/S', 'MB/S', 'GB/S']
  }
  if (bps < Kb) {
    return bps + ' ' + units[type][0]
  }
  if (bps >= Kb && bps < Mb) {
    return fixedNumber(bps / Kb, digit) + ' ' + units[type][1]
  }
  if (bps >= Mb && bps < Gb) {
    return fixedNumber(bps / Mb, digit) + ' ' + units[type][2]
  }
  if (bps >= Gb) {
    return fixedNumber(bps / Gb, digit) + ' ' + units[type][3]
  }
  throw new Error('数字转换失败！')
}

export default { convertFlow }
