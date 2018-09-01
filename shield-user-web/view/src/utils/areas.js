const data = [
  {
    value: 1,
    label: '华北',
    children: [{
      value: 11,
      label: '北京'
    }, {
      value: 12,
      label: '天津'
    }, {
      value: 13,
      label: '河北'
    }, {
      value: 14,
      label: '山西'
    }, {
      value: 15,
      label: '内蒙古'
    }]
  },
  {
    value: 2,
    label: '东北',
    children: [{
      value: 21,
      label: '辽宁'
    }, {
      value: 22,
      label: '吉林'
    }, {
      value: 23,
      label: '黑龙江'
    }]
  },
  {
    value: 3,
    label: '华东',
    children: [{
      value: 31,
      label: '上海'
    }, {
      value: 32,
      label: '江苏'
    }, {
      value: 33,
      label: '浙江'
    }, {
      value: 34,
      label: '安徽'
    }, {
      value: 35,
      label: '福建'
    }, {
      value: 36,
      label: '江西'
    }, {
      value: 37,
      label: '山东'
    }]
  },
  {
    value: 4,
    label: '华中',
    children: [{
      value: 41,
      label: '河南'
    }, {
      value: 42,
      label: '湖北'
    }, {
      value: 43,
      label: '湖南'
    }, {
      value: 44,
      label: '广东'
    }, {
      value: 45,
      label: '广西'
    }, {
      value: 46,
      label: '海南'
    }]
  },
  {
    value: 5,
    label: '西南',
    children: [{
      value: 51,
      label: '重庆'
    }, {
      value: 52,
      label: '四川'
    }, {
      value: 53,
      label: '贵州'
    }, {
      value: 54,
      label: '云南'
    }, {
      value: 55,
      label: '西藏'
    }]
  },
  {
    value: 6,
    label: '西北',
    children: [{
      value: 61,
      label: '山西'
    }, {
      value: 62,
      label: '甘肃'
    }, {
      value: 63,
      label: '青海'
    }, {
      value: 64,
      label: '宁夏'
    }, {
      value: 65,
      label: '新疆'
    }]
  },
  {
    value: 71,
    label: '台湾'
  },
  {
    value: 81,
    label: '香港'
  },
  {
    value: 91,
    label: '澳门'
  },
]

// 展平数据 { 1: '华北', ... }
const reduce = arr => arr.reduce((acc, item) => {
  acc[item.value] = item.label
  return {
    ...acc,
    ...reduce(item.children || [])
  }
}, {})

export const mapAreas = reduce(data)

export default data
