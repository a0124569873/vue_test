export function ipExistsInRange(ip, range) {
  range = range.trim()
  ip = ip.trim()
  let index = range.indexOf('-')

  let beginIp = range.substring(0, index)
  let endIp = range.substring(index + 1)

  return getIp2Long(beginIp) <= getIp2Long(ip) && getIp2Long(ip) <= getIp2Long(endIp)
}

export function isIpRangeValid(range) {
  let index = range.indexOf('-')

  let beginIp = range.substring(0, index)
  let endIp = range.substring(index + 1)

  return getIp2Long(endIp) > getIp2Long(beginIp)
}

export function getIp2Long(ip) {
  ip = ip.trim()
  let ips = ip.split('.')
  let ip2Long = 0

  for (let i = 0; i < 4; i++) {
    ip2Long = ip2Long << 8 | parseInt(ips[i])
  }

  return ip2Long
}

export default {
  getIp2Long,
  ipExistsInRange,
  isIpRangeValid
}
