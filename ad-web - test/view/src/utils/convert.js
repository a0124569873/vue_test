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
    B: ['B/S', 'KB/S', 'MB/S', 'GB/S'],
    bps: ['', 'K', 'M', 'G']
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

export function convertNum (count, digit = 2) {
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

  const [k, w, kw] = [1000, 1000 * 10, 1000 * 1000 * 10]
  const units = ['', 'k', 'w', 'kw']
  if (count < k) {
    return count + ' ' + units[0]
  }
  if (count >=k && count < w) {
    return fixedNumber(count / k, digit) + ' ' + units[1]
  }
  if (count >= w && count < kw) {
    return fixedNumber(count / w, digit) + ' ' + units[2]
  }
  if (count >= kw) {
    return fixedNumber(count / kw, digit) + ' ' + units[3]
  }
  throw new Error('数字转换失败！')
}

export function formatBps (val) {
  val = Number(val) / (1024 * 1024)
  let dotIndex = String(val).indexOf('.')
  if (dotIndex !== -1) {
    return String(val).slice(0, dotIndex + 3)
  } else {
    return val
  }
}

export default { convertFlow, convertNum, formatBps }
