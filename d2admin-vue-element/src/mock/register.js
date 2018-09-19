import Mock from 'mockjs'

import '@/mock/ajax-demo'

import '@/mock/login'

import '@/mock/chart/register.js'

// 设置全局延时 没有延时的话有时候会检测不到数据变化 建议保留
Mock.setup({
  timeout: '300-600'
})
