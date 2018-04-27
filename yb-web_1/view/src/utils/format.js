export function formatTime (time) {
  time = Number(time)
  if (time < 60) {
    return `${time} 秒`
  }
  if (time >= 60 && time < 3600) {
    return `${Math.floor(time / 60)} 分  ${time % 60} 秒`
  }
  if (time >= 3600 && time < 24 * 3600) {
    return `${Math.floor(time / 3600)} 小时 ${Math.floor(time % 3600 / 60)} 分 ${Math.floor(time % 60)} 秒`
  }
  if (time >= 24 * 3600) {
    return `${Math.floor(time / 24 / 3600)} 天 ${Math.floor(time % 86400 / 3600)} 小时 ${Math.floor(time % 86400 % 3600 / 60)} 分 ${Math.floor(time % 60)} 秒`
  }
}

export function formatSeconds (days, hours, minutes, seconds) {
  days = String(days).replace(/^(0)+/, '')
  hours = String(hours).replace(/^(0)+/, '')
  minutes = String(minutes).replace(/^(0)+/, '')
  seconds = String(seconds).replace(/^(0)+/, '')
  return Number(days) * 24 * 60 * 60 + Number(hours) * 60 * 60 + Number(minutes) * 60 + Number(seconds)
}

export function tranformSecondsToArr (seconds) {
  return {
    day: Math.floor(seconds / (24 * 60 * 60)),
    hour: Math.floor(seconds % (24 * 60 * 60) / (60 * 60)),
    minute: Math.floor(seconds % (60 * 60) / 60),
    second: seconds % 60
  }
}

export default {
  formatTime,
  formatSeconds,
  tranformSecondsToArr
}
