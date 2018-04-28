import {convertFlow, formatBps, convertNum} from './convert'

const install = function (Vue) {
  if (install.installed) return
  Vue.prototype.$convertFlow = convertFlow
  Vue.prototype.$formatBps = formatBps
  Vue.prototype.$convertNum = convertNum
}

export default {install}
