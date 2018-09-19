import xhr from './xhr'

const route = 'site'
const list = (query = {}) => xhr({
  url: `${route}`,
  method: 'get',
  query
})

const create = body => xhr({
  url: `${route}`,
  method: 'post',
  body
})

const remove = id => xhr({
  url: `${route}/${id}`,
  method: 'delete',
})

const batRemove = ids => xhr({
  url: `${route}/bundle/delete`,
  method: 'delete',
  body: {
    ids
  }
})

const textCode = id => xhr({
  url: `${route}/${id}/txtcode`
})

const verifyText = id => xhr({
  url: `${route}/${id}/txtcode-verify`,
  method: 'post'
})

const linkup = (id, body) => xhr({
  url: `${route}/${id}/linkup`,
  method: 'post',
  body
})

const updateLinkup = (id, body) => xhr({
  url: `${route}/${id}/linkup-update`,
  method: 'post',
  body
})

const verifyCname = id => xhr({
  url: `${route}/${id}/cname-verify`,
  method: 'post'
})

const uploadCert = (id, { certificate, certificate_key }) => xhr({
  url: `${route}/${id}/https-cert/upload`,
  method: 'post',
  body: {
    certificate: window.btoa(certificate),
    certificate_key: window.btoa(certificate_key),
  }
})

const httpsCert = id => xhr({
  url: `${route}/${id}/https-cert`,
  method: 'post'
})

const setCacheExpire = (id, body) => xhr({
  url: `${route}/${id}/cache-expire`,
  method: 'post',
  body
})

const cacheExpire = id => xhr({
  url: `${route}/${id}/cache-expire`
})

const updateConfig = (id, body) => xhr({
  url: `${route}/${id}`,
  method: 'put',
  body
})

const cacheWhiteList = {
  get: id => xhr({
    url: `${route}/${id}/cache-whitelist`,
  }),
  add: (id, body) => xhr({
    url: `${route}/${id}/cache-whitelist`,
    method: 'post',
    body
  }),
  delete: (id, body) => xhr({
    url: `${route}/${id}/cache-whitelist`,
    method: 'delete',
    body
  })
}

const cacheBlackList = {
  get: id => xhr({
    url: `${route}/${id}/cache-blacklist`,
  }),
  add: (id, body) => xhr({
    url: `${route}/${id}/cache-blacklist`,
    method: 'post',
    body
  }),
  delete: (id, body) => xhr({
    url: `${route}/${id}/cache-blacklist`,
    method: 'delete',
    body
  })
}

export const urlWhiteList = {
  get: id => xhr({
    url: `${route}/${id}/url-whitelist`,
  }),
  set: (id, body) => xhr({
    url: `${route}/${id}/url-whitelist`,
    method: 'post',
    body
  }),
}
export const urlBlackList = {
  get: id => xhr({
    url: `${route}/${id}/url-blacklist`,
  }),
  set: (id, body) => xhr({
    url: `${route}/${id}/url-blacklist`,
    method: 'post',
    body
  }),
}
export const ipWhiteList = {
  get: id => xhr({
    url: `${route}/${id}/ip-whitelist`,
  }),
  set: (id, body) => xhr({
    url: `${route}/${id}/ip-whitelist`,
    method: 'post',
    body
  }),
}
export const ipBlackList = {
  get: id => xhr({
    url: `${route}/${id}/ip-blacklist`,
  }),
  set: (id, body) => xhr({
    url: `${route}/${id}/ip-blacklist`,
    method: 'post',
    body
  }),
}

const STATUS = {
  '0': '用户已提交，待审核',
  '1': '已审核，待接入',
  '2': '正在接入...',
  '3': '已接入，未修改DNS解析',
  '4': '线路正常',
  '5': '接入错误',
  '6': '正在删除...',
  '7': '删除异常',
}

const TYPE = {
  '0': '未设置',
  '1': '共享型',
  '2': '独享型'
}

export const attacks = (id, body) => xhr({
  url: `${route}/${id}/report/attacks`,
  method: 'get',
  body
})

export const proxyIps = (id) => xhr({
  url: `${route}/${id}/proxy-ips`,
  method: 'get'
})

export default {
  list,
  create,
  remove,
  batRemove,
  textCode,
  verifyText,
  verifyCname,
  linkup,
  updateLinkup,
  uploadCert,
  httpsCert,
  setCacheExpire,
  cacheExpire,
  updateConfig,
  cacheWhiteList,
  cacheBlackList,
  STATUS,
  TYPE,
  attacks,
  proxyIps
}
