import { mapAreas } from 'utils/areas'
import store from 'src/vuex/store'

/**
 * 全局过滤器
 */

const fillZero = value => value < 10 ? '0' + value : value

const formattime = (value, isStamp) => {
  let date = new Date(isStamp ? value * 1000 : value)
  let Y = date.getFullYear()
  let M = fillZero(date.getMonth() + 1)
  let D = fillZero(date.getDate())
  let h = fillZero(date.getHours())
  let m = fillZero(date.getMinutes())
  let s = fillZero(date.getSeconds())
  return `${Y}-${M}-${D} ${h}:${m}:${s}`
}

const net = value => ['电信', '联通', '移动'][value - 1]

// const line = value => {
//   const [city, netVal] = value.split('_')
//   return `${mapAreas[city]} - ${net(netVal)}`
// }

const price = value => `￥${Number(value).toFixed(2)}`

export default {
  install(Vue) {
    // 时间格式化 YYYY-MM-DD hh:mm:ss
    Vue.filter('formattime', formattime)

    // 网络
    Vue.filter('net', net)

    // 线路
    Vue.filter('line', value => {
      const mapLine = store.getters.linesMap
      return mapLine[value] ? mapLine[value].text : ''
    })

    // 地域  example: 华北 - 北京
    Vue.filter('area', value => value.map(val => mapAreas[val]).join(' - ').replace(/^[ ]-/, ''))

    // 价格显示  $10.00
    Vue.filter('price', price)

    // 大类型
    Vue.filter('bType', value => value !== 1 ? '应用类' : '网站类')

    // 小类型
    Vue.filter('sType', value => value === 1 ? '共享' : '独享')

    // 购买时长
    Vue.filter('period', value => value.replace(/:month/, '个月').replace(/:year/, '年'))

    // 所有 IP 类型
    Vue.filter('ipType', value => ['共享网站类', '独享网站类', '应用类'][value - 1])

    // 订单类型
    Vue.filter('product', value => ['充值', '共享型高防IP', '独享型高防IP', '应用防护'][value])
  }
}
