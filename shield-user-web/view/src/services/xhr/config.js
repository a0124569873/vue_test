// 此处配置 根访问路径 以及 全局错误处理
// 更多配置请根据业务逻辑自行实现

// 后端 API 地址，最好以 http(s):// 打头

export const rootPath = '/v1/'

export const errHandler = (e) => {
  console.error(e)
}
