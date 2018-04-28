<template>                  
  <div :id="id" class="pic"></div>   
</template>
<script>
export default {
  props: {
    picData: {
      type: Array,
      required: true
    },
    id: {
      type: String
    },
    tickInterval: {
      type: Number
    },
    DataStyle: {
      type: String
    }
  },
  mounted () {
    this.initSplineChart()
  },
  watch: {
    picData () {
      this.splinechart.changeData(this.picData)
    }
  },
  data () {
    return {
    }
  },
  methods: {
    initSplineChart () {
      this.splinechart = new G2.Chart({
        id: this.id,
        forceFit: true,
        height: 450,
        animate: false,
        plotCfg: {
          margin: [ 50, 120, 80, 120 ],
          border: {
            fill: '#fff'
          }
        }
      })
      this.splinechart.axis('timestamp', {
        title: null // 不展示 timestamp 对应坐标轴的标题
      })
      this.splinechart.legend({
        title: null,
        position: 'bottom'
      })
      this.splinechart.off('tooltipchange').on('tooltipchange', (ev) => {
        for (let i = 0; i < ev.items.length; i++) {
          if (ev.items[i].name !== 'Memory' && ev.items[i].name !== 'Disk') {
            ev.items[i].value = ev.items[i].value + ' %'
          } else {
            let value = ev.items[i].point._origin.total
            ev.items[i].value = Number(value / 1024 / 1024).toFixed(2) + 'Mb total, ' + ev.items[i].value + ' % used'
          }
        }
      })
      this.drawSpline(this.splinechart, this.flow)
    },
    drawSpline (chart, data) { 
      let _this = this 
      chart.source(data, {
        timestamp: {
          type: 'time',
          mask: _this.DataStyle ? _this.DataStyle : 'HH:MM:ss',
          tickInterval: this.tickInterval ? this.tickInterval : null
        },
        value: {
          alias: '%',
          min: 0,
          max: 100
        }
      })
      this.splinechart.line().shape('smooth').position('timestamp*value').color('type', [ '#000', 'rgb(124, 181, 236)', '#9AD681' ]).size(2)
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
