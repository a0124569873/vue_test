<template>
<div>
  <div class="query-form">
    <slot name="query"></slot>
  </div>
  <div class="ban-item">
    <slot name="bandata"></slot>
  </div>
  <div class="f-x">
    <div :id="id" class="mt-lg" :style='styles'></div>
    <div style="wdth: 30%;" v-if="showPie">
      <div>
        <pie-chart
        :id="'container1'"
        :legend="['接收流量', '攻击流量']"
        :title="'流量占比'"></pie-chart>
      </div>
      <div>
        <pie-chart
        :id="'container2'"
        :legend="['接收数据包', '攻击数据包']"
        :title="'数据包占比'"></pie-chart>
      </div>
    </div>
  </div>
</div>
</template>

<style scoped lang="less">
.query-form {
  padding: 0 0 15px 0;
  border-bottom: 1px solid #eeeeee;
}
</style>

<script>
import pieChart from 'components/charts/pieChart'
const echarts = require('echarts/lib/echarts')
require('echarts/lib/chart/line')
require('echarts/lib/component/tooltip')
require('echarts/lib/component/title')
require('echarts/lib/component/legend')
require('echarts/lib/component/toolbox')

export default {
  name: 'linesChart',
  props: {
    id: {
      type: String
    },
    showPie: Boolean,
    title: String,
    legend: Array,
    xKey: String,
    yKey: Array,
    styles: String,
    data: {
      type: Array,
      default: () => {
        return []
      }
    },
  },
  components: {
    pieChart
  },
  data() {
    return {
      timeArr: ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00'],
      chart: null
    }
  },
  watch: {
    data() {
      this.chart.setOption({
        xAxis: {
          data: this.data[0]
        },
        series: [
          {
            data: this.data[1]
          },
          {
            data: this.data[2]
          }
        ]
      })
    }
  },
  mounted() {
    this.chart = echarts.init(document.getElementById(this.id))

    $(window).on('resize', () => {
      this.chart.resize()
    })
    var option = {
      color: ['#ebd4f4', '#f1bfb4'],
      title: {
        text: this.title
      },
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          // type: 'cross',
          // label: {
          //   backgroundColor: '#6a7985'
          // },
          animation: false
        }
      },
      legend: {
        data: this.legend
      },
      toolbox: {
        feature: {
          saveAsImage: {}
        }
      },
      grid: {
        left: '3%',
        right: '3%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: [
        {
          type: 'time',
          boundaryGap: false,
          data: this.data[0],
          splitLine: {
            show: false
          }
        }
      ],
      yAxis: [
        {
          type: 'value'
        }
      ],
      series: [
        {
          name: this.legend[0],
          type: 'line',
          // stack: '总量',
          areaStyle: {normal: {}},
          smooth: true,
          data: this.data[1]
        },
        {
          name: this.legend[1],
          type: 'line',
          // stack: '总量',
          areaStyle: {normal: {}},
          smooth: true,
          data: this.data[2]
        }
      ]
    }

    this.chart.setOption(option)

    // 定时更新图表数据
    // setInterval(() => {
    //   this.timeArr.shift()
    //   this.timeArr.push('06:00')
    //   myChart.setOption({
    //     xAxis: {
    //       data: this.timeArr
    //     },
    //     series: [
    //       {
    //         data: [53, 20, 36, 130, 10, 230]
    //       },
    //       {
    //         data: [153, 20, 2, 130, 4, 60]
    //       }
    //     ]
    //   })
    // }, 1000)
  }
}
</script>
