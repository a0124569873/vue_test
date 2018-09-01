import xhr from './xhr/'

const route = 'order'

const createOrder = body => xhr({
  url: `${route}`,
  method: 'post',
  body
})

const getOrder = oid => xhr({
  url: `${route}/${oid}`
})

export default {
  createOrder,
  getOrder
}
