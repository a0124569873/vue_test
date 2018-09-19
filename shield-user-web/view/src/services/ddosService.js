import xhr from './xhr'

const route = 'ddos'

const list = query => xhr({
  url: `${route}`,
  query
})

const active = (id, area) => xhr({
  url: `${route}/${id}/active`,
  method: 'post',
  body: {
    area
  }
})

const ips = query => xhr({
  url: `${route}/ips`,
  query
})

const availArea = (id) => xhr({
  url: `${route}/${id}/available-areas`,
  method: 'get',
})

const attacks = (id, params) => xhr({
  url: `${route}/${id}/report/attacks`,
  method: 'get',
  body: params
})

export default {
  list,
  active,
  ips,
  availArea,
  attacks
}
