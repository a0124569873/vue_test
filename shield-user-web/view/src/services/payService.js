import xhr from './xhr/'

const pay = oid => xhr({
  url: `/pay/${oid}`,
  method: 'put'
})

export default {
  pay
}
