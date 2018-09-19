import xhr from './xhr'

const route = 'server'

const price = body => xhr({
  url: `${route}/price`,
  method: 'post',
  body
})

const config = () => xhr({
  url: `${route}/config`,
})

const areas = id => xhr({
  url: `${route}/${id}/areas`
})

const lines = () => xhr({
  url: `${route}/lines`
})

export default {
  price,
  config,
  areas,
  lines
}
