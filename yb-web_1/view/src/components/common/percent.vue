<template>
    <div class="bgcolor">                       
        <div id="flow" class="pic"></div>   
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
    }
  },
  mounted () {
    // this.reset()
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
          id: 'flow',
          forceFit: true,
          height: 450,
          animate: false
        })
        chart.axis('timestamp', {
          title: null // 不展示 timestamp 对应坐标轴的标题
        })
        chart.legend({
          title: null,
          position: 'bottom'
        })
        chart.on('tooltipchange', (ev) => {
          var item = ev.items[0] // 获取tooltip要显示的内容
          item.value = item.value + '%'
          var itemSc = ev.items[1]
          itemSc.value = itemSc.value + '%'
        })
        chart.line().position('timestamp*value').shape('smooth').color('type').size(2)
        // 查看7天
        if (this.timeRange === 7) {
          let tickInterval = this.timestamp === 7 ? 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000
          chart.source(this.picData, {
            timestamp: {
              tickInterval: tickInterval,
              type: 'time',
              mask: 'yyyy-mm-dd HH:MM:ss'
            },
            value: {
              alias: 'CPU使用率(%)',
              max: 100,
              min: 0
            }
          })
        } else if (this.timeRange === 9) {
          let tickInterval = this.timestamp === 7 ? 24 * 60 * 60 * 1000 : 24 * 60 * 60 * 1000 * 5
          chart.source(this.picData, {
            timestamp: {
              tickInterval: tickInterval,
              type: 'time',
              mask: 'yyyy-mm-dd HH:MM:ss'
            },
            value: {
              alias: 'CPU使用率(%)',
              max: 100,
              min: 0
            }
          })
        } else {
          chart.source(this.picData, {
            timestamp: {
              // tickCount: 12,
              type: 'time',
              mask: 'HH:MM:ss'
            },
            value: {
              alias: 'CPU使用率(%)',
              max: 100,
              min: 0
            }
          })
        }

        chart.render()
      }
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
    reset () {
      this.flowdata = []
      let now = (new Date()).getTime() - 1000 * 70
      for (let i = 0; i <= 10; i++) {
        let time = now + 1000 * 7 * i
        let inPkt = {time: time, yaxis: 0, type: 'flow_in'}
        let outPkt = {time: time, yaxis: 0, type: 'flow_out'}
        this.flowdata.push(inPkt)
        this.flowdata.push(outPkt)
      }
    },
    loadSpline () {
      chart = new G2.Chart({
        id: 'flow',
        forceFit: true,
        height: 450,
        animate: false
      })
      chart.axis('timestamp', {
        title: null // 不展示 timestamp 对应坐标轴的标题
      })
      chart.legend({
        title: null,
        position: 'bottom'
      })
      chart.on('tooltipchange', (ev) => {
        if (ev.items.length > 1) {
          let item = ev.items[0] // 获取tooltip要显示的内容
          item.value = item.value + '%'
          let itemSc = ev.items[1]
          itemSc.value = itemSc.value + '%'
        } else {
          let item = ev.items[0] // 获取tooltip要显示的内容
          item.value = item.value + '%'
        }
      })
      chart.source(this.picData, {
        timestamp: {
          // tickCount: 5,
          type: 'time',
          mask: 'HH:MM:ss'
        },
        value: {
          alias: 'CPU使用率(%)',
          max: 100,
          min: 0
        }
      })
      chart.line().position('timestamp*value').shape('smooth').color('type').size(2)
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
