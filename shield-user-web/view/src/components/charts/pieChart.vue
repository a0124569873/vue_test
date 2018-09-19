<template>
  <div>
    <div :id="id" style="height: 250px;"></div>
  </div>
</template>
<script>
  import Highcharts from 'highcharts/highstock'
  import HighchartsMore from 'highcharts/highcharts-more'
  import HighchartsDrilldown from 'highcharts/modules/drilldown'
  import Highcharts3D from 'highcharts/highcharts-3d'
  HighchartsMore(Highcharts)
  HighchartsDrilldown(Highcharts)
  Highcharts3D(Highcharts)
  export default {
    props: {
      id: {
        type: String
      },
      title: {
        type: String
      },
      legend: {
        type: Array
      }
    },
    name: 'highcharts',
    data() {
      return {
        chart: null
      }
    },
    mounted() {
      this.initChart()
    },
    methods: {
      initChart() {
        this.chart = $('#' + this.id).highcharts({
          title: {
            text: this.title,
            align: 'center',
            verticalAlign: 'middle',
            y: 50
          },
          credits: {
            enabled: false
          },
          colors: [
            '#42b7f8',
            '#3075ef'
          ],
          tooltip: {
            headerFormat: '{series.name}<br>',
            pointFormat: '{point.name}: <b>{point.percentage:.1f}%</b>'
          },
          plotOptions: {
            pie: {
              dataLabels: {
                enabled: true,
                distance: -50,
                style: {
                  fontWeight: 'bold',
                  color: 'white',
                  // textShadow: '0px 1px 2px black'
                }
              },
              startAngle: -90, // 圆环的开始角度
              endAngle: 90,    // 圆环的结束角度
              center: ['50%', '75%']
            }
          },
          series: [{
            type: 'pie',
            // name: '浏览器占比',
            innerSize: '80%',
            data: [
              [this.legend[0], 50],
              [this.legend[1], 50]
            ]
          }
          ]
        })
      }
    }
  }
</script>
<style lang="less">

</style>
