import xhr from './xhr'

const route = 'port'

const nonSiteList = (query = {}) => xhr({
  url: `${route}`,
  method: 'get',
  query
})

const removeNonList = id => xhr({
  url: `${route}/${id}`,
  method: 'delete',
})
const addNonList = body => xhr({
  url: `${route}`,
  method: 'post',
  body
})

const updateLinkUp = (id, body) => xhr({
  url: `${route}/${id}/linkup-update`,
  method: 'post',
  body
})

const getConfig = id => xhr({
  url: `${route}/${id}`,
  method: 'get'
})
const updateAllConfig = (id, body) => xhr({
  url: `${route}/${id}`,
  method: 'put',
  body
})

const updateConfig = (id, body) => xhr({
  url: `${route}/${id}/conf-update`,
  method: 'post',
  body
})

const batRemove = ids => xhr({
  url: `${route}/bundle/delete`,
  method: 'delete',
  body: {
    ids
  }
})

export const whiteBlackList = {
  get: id => xhr({
    url: `${route}/${id}/ip-white-black-list`,
  }),
  setBlack: (id, body) => xhr({
    url: `${route}/${id}/ip-black-list`,
    method: 'post',
    body
  }),
  setWhite: (id, body) => xhr({
    url: `${route}/${id}/ip-white-list`,
    method: 'post',
    body
  }),
}

// 生成CName
export const generateCName = (id, params) => xhr({
  url: `${route}/${id}/cname-generate`,
  method: 'post',
  body: params
})
// 启动CName
export const activeCName = (id, params) => xhr({
  url: `${route}/${id}/cname-active`,
  method: 'post',
  body: params
})

export default {
  nonSiteList,
  addNonList,
  removeNonList,
  updateLinkUp,
  updateConfig,
  batRemove,
  getConfig,
  updateAllConfig,
  generateCName,
  activeCName
}
