import {convertFlow} from './convert'
import Pagination from './pagination'
import Format from './format'

const install = function (Vue) {
  if (install.installed) return
  Vue.prototype.$convertFlow = convertFlow
  Vue.prototype.$pagination = Pagination
  Vue.prototype.$format = Format
}
export default {install, convertFlow, Pagination, Format}
