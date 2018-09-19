import { rootPath, errHandler } from './config'

const xhr = ({ url, body = null, query = {}, method = 'get', other = {} }) => {
  const defer = $.Deferred()
  const params = _DEV_ ? {
    ...query,
    debug: true
  } : query

  let paramsStr = ''
  if (method.toLowerCase() === 'get') {
    params._ = Date.now()
    for(let k in params) {
      const dot = paramsStr ? '&' : '?'
      paramsStr += dot + `${k}=${params[k]}`
    }
  }

  if (method.toLowerCase() === 'post') {
    if (method.toLocaleLowerCase() === 'post') {
      body = JSON.stringify(body)
    }
    other.contentType = 'application/json'
  }

  $.ajax({
    type: method,
    url: rootPath + url + paramsStr,
    data: body,
    ...other
    // xhrFields: { // 跨域允许带上 cookie
    //   withCredentials: [域名]
    // },
    // crossDomain: true
  })
  .done(defer.resolve)
  .fail(errHandler)
  return defer.promise()
}

export default xhr
