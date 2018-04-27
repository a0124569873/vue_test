<template>
    <div class="bgcolor">                       
        <div id="memory" class="pic"></div>   
    </div>
</template>
<script>
let chart = null
export default {
  props: {
    picData: {
      type: Array,
      required: true
    },
    timeRange: {
      type: Number
    },
    status: {
      type: Boolean
    },
    total: {
      type: Number
    }
  },
  mounted () {
    this.loadSpline()
  },
  watch: {
    picData (newVal, oldVal) {
      let compare = JSON.stringify(newVal) === JSON.stringify(oldVal)
      if (compare) {
        return
      }
      if (this.status) {
        chart.changeData(this.picData)
        
      } else {
        chart.destroy()
        chart = new G2.Chart({
          id: 'memory',
          forceFit: true,
          height: 450,
          animate: false
        })
        chart.axis('timestamp', {
          title: null // 不展示 timestamp 对应坐标轴的标题
        })
        chart.legend({
          title: null
        })
        chart.axis('value', {
          title: null,
          formatter: (val) => {
            if (val > this.total) {
              val = this.total
            }
            return this.covertFlow(val)
          }
        })
        chart.on('tooltipchange', (ev) => {
          var item = ev.items[0] // 获取tooltip要显示的内容
          item.value = this.covertFlow(item.value)
        })
        chart.area().position('timestamp*value')
        // 查看7天
        if (this.timeRange === 7) {
          chart.source(this.picData, {
            timestamp: {
              tickInterval: 24 * 60 * 60 * 1000,
              type: 'time',
              mask: 'yyyy-mm-dd'
            },
            value: {
              max: this.total,
              min: 0
            }
          })
        } else if (this.timeRange === 9) {
          chart.source(this.picData, {
            timestamp: {
              tickInterval: 24 * 60 * 60 * 1000 * 5,
              type: 'time',
              mask: 'yyyy-mm-dd HH:MM:ss'
            },
            value: {
              max: this.total,
              min: 0
            }
          })
        } else {
          chart.source(this.picData, {
            timestamp: {
              tickCount: 12,
              type: 'time',
              mask: 'HH:MM:ss'
            },
            value: {
              max: this.total,
              min: 0
            }
          })
        }

        chart.render()
      }
    },
    // 这个暂时没有用到
    timeRange () {
      console.log('timeRange change')
    }
  },
  data () {
    return {
      flowdata: [],
      chart: {},
      cpuData: []
    }
  },
  methods: {
    covertFlow (bps) {
      let flow = ''
      if (bps < 1024) {
        flow = Math.ceil(bps) + ' B'
      }
      if (bps >= 1024 && bps < 1024 * 1024) {
        flow = bps / 1024
        flow = flow.toFixed(3) + ' KB'
      }
      if (bps >= 1024 * 1024 && bps < 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024
        flow = flow.toFixed(3) + ' MB'
      }
      if (bps >= 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024 / 1024
        flow = flow.toFixed(3) + ' GB'
      }
      return flow
    },
    loadSpline () {
      chart = new G2.Chart({
        id: 'memory',
        forceFit: true,
        height: 450,
        animate: false
      })
      chart.axis('timestamp', {
        title: null // 不展示 timestamp 对应坐标轴的标题
      })
      chart.legend({
        title: null
      })
      chart.on('tooltipchange', (ev) => {
        var item = ev.items[0] // 获取tooltip要显示的内容
        item.value = this.covertFlow(item.value)
      })
      chart.source(this.picData, {
        timestamp: {
          tickCount: 5,
          type: 'time',
          mask: 'HH:MM:ss'
        },
        value: {
          max: this.total,
          min: 0
        }
      })
      chart.area().position('timestamp*value')
      chart.render()
    } 
  }
}
</script>
<style scoped>
    .bgcolor{
        margin: 0 20px;
    }
    h3{
        font-size: 14px;
        margin-bottom: 20px;
    }
</style>
