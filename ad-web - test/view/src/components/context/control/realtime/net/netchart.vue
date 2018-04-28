<template>
<div class="port-pic">
  <div class="ey">
    <span class="ey1"></span><span class="span1"><b>上行</b></span>
    <span class="ey2"></span><span class="span2"><b>下行</b></span>
  </div>  
  <span class="port-title">接口名称</span>     
  <div id="portpic"></div>
</div>
  
</template>
<script>
export default {
  props: {
    eths: {
      type: Array,
      required: true
    },
    length: {
      type: Number,
      required: true
    }
  },
  computed: {
    tx: function () {
      let temp_tx = []
      if (this.eths.length > 0) {
        for (let i = 0; i < this.eths.length; i++) {
          temp_tx.push(Math.ceil(this.eths[i].out_bps))
        }
      }
      
      return temp_tx
    },
    rx: function () {
      let temp_rx = []
      if (this.eths.length > 0) {
        for (let i = 0; i < this.eths.length; i++) {
          temp_rx.push(Math.ceil(this.eths[i].in_bps))
        }
      }
      return temp_rx
    },
    xaxis: function () {
      let categories = []
      if (this.eths.length > 0) {
        for (let i = 0; i < this.eths.length; i++) {
          categories.push(this.eths[i].name)
        }
      }
      return categories
    }
  },
  watch: {
    tx: function () {
      let chart = $('#portpic').highcharts()
      chart.series[0].setData(this.tx)
      let dataLabelNode = $('.highcharts-data-labels').find('g')// 获取数据节点
      for (let i = 0; i < dataLabelNode.length; i++) {
        dataLabelNode[i].style.visibility = 'visible'
        dataLabelNode[i].style.opacity = 1
      }
    },
    rx: function () {
      let chart = $('#portpic').highcharts()
      chart.series[1].setData(this.rx)
      let dataLabelNode = $('.highcharts-data-labels').find('g')// 获取数据节点
      for (let i = 0; i < dataLabelNode.length; i++) {
        dataLabelNode[i].style.visibility = 'visible'
        dataLabelNode[i].style.opacity = 1
      }
    },
    xaxis: function () {
      let chart = $('#portpic').highcharts()
      chart.xAxis[0].setCategories(this.xaxis)
    },
    length: function () {
      let chart = $('#portpic').highcharts()
      let height = this.length * 62
      $('#portpic').height(height + 'px')
      chart.reflow()
    }
    
  },
  mounted: function () {
    if (this.theme === 'theme-blue') {
      this.color = ['#69b6ff', '#f5a623']
    } else if (this.theme === 'theme-red') {
      this.color = ['#d8252f', '#f39800']
    }
    this.loadBar()
  },
  data: function () {
    return {
      status: true
    }
  },
  methods: {
    covertFlow (bps) {              
      let flow = ''
      if (bps < 1024) {
        flow = Math.ceil(bps) + 'Bps'
      }
      if (bps >= 1024 && bps < 1024 * 1024) {
        flow = bps / 1024
        flow = flow.toFixed(0) + 'KBps'
      }
      if (bps >= 1024 * 1024 && bps < 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024
        flow = flow.toFixed(0) + 'MBps'
      }
      if (bps >= 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024 / 1024
        flow = flow.toFixed(0) + 'GBps'
      }
      return flow
    },
    loadBar () {
      let _this = this
      Highcharts.setOptions({
        colors: this.color,
        credits: {
          enabled: false // 禁用版权信息
        },
        exporting: {
          enabled: false
        },
        legend: {
          enabled: false
        }

      })
      Highcharts.chart('portpic', {
        chart: {
          type: 'bar'
        },
        title: {
          text: null
        },
        xAxis: {
          tickPixelInterval: 120, // 刻度线的像素间隔
          gridLineWidth: 1
          // labels: {
          //    formatter: function () {
          //       //获取到刻度值
          //       var labelVal = this.value;
          //       //实际返回的刻度值
          //       var reallyVal = labelVal;
          //       //判断刻度值的长度
          //       console.log(labelVal)
          //       // console.log(labelVal.length)
          //       if (labelVal.length > 10)
          //       {
          //           //截取刻度值
          //           reallyVal = abelVal.substr(0, 10)  + '<br/>'+belVal.substr(10, 20) ;
          //           console.log(reallyVal)
          //       }
          //       return reallyVal;
          //     }
          // }
        },
        yAxis: {
          title: {
            text: null
          },
          tickPixelInterval: 120,
          tickAmount: 5,//指定y轴刻度总数，该配置会覆盖 tickPixelInterval 参数，只对线性轴有效，对时间轴、对数轴、分类轴无效。
          gridLineWidth: 1,
          labels: {
            formatter: function () {
              return _this.covertFlow(this.value)
            }
          }
        },
        plotOptions: {
          series: {
            dataLabels: {
              enabled: true,
              style: {
                color: '#666666',
                fontWeight: '500'
              },
              formatter: function () {
                return _this.covertFlow(this.y)
              }  
            }
          }
        },
        tooltip: {
          enabled: true,
          formatter: function () {
            return '<b>接口：' + this.x + '</b><br/>' + index(this.point.colorIndex) + _this.covertFlow(this.y)
            function index (index) {
              if (index === 1) {
                return '<b>下行:</b>'
              } else {
                return '<b>上行:</b>'
              }
            }
          }
        },
        series: [{
          name: '上行',
          data: []
        }, {
          name: '下行',
          data: []
        }],
        lang: {
          noData: '暂无数据'
        }
      })
      console.log(Highcharts.xAxis)
    }
  }
}
</script>