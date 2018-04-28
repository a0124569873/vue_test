<template>
    <div :id="id"></div>
</template>
<script>
export default {
  props: {
    id: {
      type: String,
      required: true
    },
    data: {
      type: Array,
      required: true
    }
  },
  mounted () {
    this.drawChart()
  },
  methods: {
    covertFlow (bps) {
      let flow = ''
      if (bps < 1024) {
        flow = Math.ceil(bps) + 'B'
      }
      if (bps >= 1024 && bps < 1024 * 1024) {
        flow = bps / 1024
        flow = flow.toFixed(1) + 'K'
      }
      if (bps >= 1024 * 1024 && bps < 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024
        flow = flow.toFixed(1) + 'M'
      }
      if (bps >= 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024 / 1024
        flow = flow.toFixed(1) + 'G'
      }
      return flow
    },
    drawChart () {
      let id = this.id
      let syn = [
        {x: 1499061861000, y: 2000, count: 11}, {x: 1499061862000, y: 250, count: 11}, {x: 1499061863000, y: 350, count: 11}, {x: 1499061864000, y: 230, count: 11},
        {x: 1499061865000, y: 2000, count: 11}, {x: 1499061866000, y: 250, count: 11}, {x: 1499061867000, y: 350, count: 11}, {x: 1499061868000, y: 230, count: 11},
        {x: 1499061869000, y: 2000, count: 11}, {x: 1499061872000, y: 250, count: 11}, {x: 1499061873000, y: 350, count: 11}, {x: 1499061874000, y: 230, count: 11}
      ]
      let udp = [
        {x: 1499061861000, y: 5000, count: 11}, {x: 1499061862000, y: 150, count: 11}, {x: 1499061863000, y: 550, count: 11}, {x: 1499061864000, y: 330, count: 11},
        {x: 1499061865000, y: 4000, count: 11}, {x: 1499061866000, y: 350, count: 11}, {x: 1499061867000, y: 250, count: 11}, {x: 1499061868000, y: 430, count: 11},
        {x: 1499061869000, y: 3000, count: 11}, {x: 1499061872000, y: 450, count: 11}, {x: 1499061873000, y: 150, count: 11}, {x: 1499061874000, y: 230, count: 11}
      ]
      let all = []
      for (let i = 0; i < syn.length; i++) {
        let obj = {
          x: syn[i].x,
          y: syn[i].y + udp[i].y,
          count: syn[i].count + udp[i].count
        }
        all.push(obj)
      }
      var _this = this
      $('#' + id).highcharts({
        chart: {
          type: 'area',
          backgroundColor: 'rgba(0,0,0,0)'
        },
        colors: ['#5F8DDE', '#36B3C3', '#4ECDA5', '#94E08A',
          '#E2F194', '#EDCC72', '#F8AB60', '#F9815C', '#EB4456'],
        title: {text: null},
        credits: {enabled: false},
        exporting: {enabled: false},
        // legend:{
        //     align:'right',
        //     floating:true
        // },
        xAxis: {
          type: 'datetime',
          minorGridLineWidth: 1,
          gridLineWidth: 1,
          alternateGridColor: null
        },
        yAxis: {
          title: {
            text: null
          },
          step: 1,
          labels: {
            formatter: function () {
              return _this.covertFlow(this.value)
            }
          }
        },
        tooltip: {
          shared: true,
          useHTML: true,
          pointFormatter: function () {
            return '<table><tr><td><span style="width:10px;height:10px;background:' + this.series.color + ';display:inline-block;border-radius:50%;margin-right:10px"></span></td><td>' + this.series.name + ' :</td><td> <b>' +
                                    _this.covertFlow(this.y) + '/' + this.count + '次</td></tr></table>'
          },
          xDateFormat: '%H:%M:%S',
          crosshairs: {
            width: 2,
            color: 'gray'
          }
        },
        plotOptions: {
          area: {
            pointStart: 1940,
            marker: {
              enabled: false,
              symbol: 'circle',
              radius: 2
            }
          }
        },
        series: [
          {
            name: '总流量',
            data: all
          },
          {
            name: 'SYN清洗流量及次数',
            data: syn
          }, {
            name: 'UDP清洗流量及次数',
            data: udp
          }]
      })
    }
  }
}
</script>