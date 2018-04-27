<template>
<div id="echarts">
  <div id="chart" style="width:600px;height:300px">
  </div>
  <el-button @click="toZxn('/')">home</el-button>
</div>
</template>
<script>

import getChartData from '../services/getXhrData'

export default {
  name:"echarts_components",
  data(){
      return{

      }
  },
  mounted(){
      this.drawCharts();
  },
  methods:{
      drawCharts(){
        let ccc = this.$echarts.init(document.getElementById("chart"));

        let op = {
            title: { text: '在Vue中使用echarts' },
            tooltip: {},
            xAxis: {
                data: ["衬衫","羊毛衫","雪纺衫","裤子","高跟鞋","袜子"]
            },
            yAxis: {},
            series: [{
                name: '销量',
                type: 'line',
                // smooth: true,
                data: [10,50,60,100,500,900,100],
                areaStyle: {}
            }]
        };
        ccc.setOption(op);

        // setInterval(
        //     function () {
        //         getChartData.getChartData().then(
        //             (res) => {
        //                 op.series[0].data = res.data;
        //             }
        //         )
        //         ccc.setOption(op);
        //     }
        //     ,60000);
      },
      toZxn(patth){
          this.$router.push(patth);
      }
  }
}
</script>
