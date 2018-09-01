<template>
  <el-card class="wrap">
    <el-form slot="header" size="mini" inline class="search">
      <el-form-item label="查询时间：" prop="search">
        <el-date-picker
          v-model="daterange"
          type="daterange"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期">
        </el-date-picker>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" icon="el-icon-search">查询</el-button>
      </el-form-item>
      <el-form-item label="快速查询：" style="margin-left: 50px">
        <el-select v-model="fastSearch" class="select">
          <el-option label="全部" value="0"></el-option>
        </el-select>
      </el-form-item>
    </el-form>
    <div class="f-x f-ja">
      <div>总请求数：426 次</div>
      <div>总流量：188.8KB</div>
      <div>网站浏览人数（IP）：76 次</div>
    </div>
    <div ref="chart" class="chart"></div>
  </el-card>
</template>
<script>
import echarts from 'echarts'

export default {
  data() {
    return {
      daterange: '',
      fastSearch: '',
      chart: null
    }
  },
  methods: {
    initChart() {
      this.chart = echarts.init(this.$refs.chart)
      this.setOptions()
      window.addEventListener('resize', this.resize)
    },
    setOptions() {
      this.chart.setOption({
        xAxis: {
          type: 'category',
          boundaryGap: false,
          data: ['0:00', '1:00', '2:00', '3:00', '4:00', '5:00', '6:00']
        },
        yAxis: {
          type: 'value'
        },
        tooltip: {
          trigger: 'axis',
          backgroundColor: '#4DD0E1',
          formatter: '{b}: {c}'
        },
        series: [{
          data: [820, 932, 901, 934, 1290, 1330, 1320],
          type: 'line',
          areaStyle: {},
          symbolSize: 10
        }],
        color: ['#8fa5f0']
      })
    },
    resize() {
      this.chart.resize()
    }
  },
  mounted() {
    this.initChart()
  },
  destroyed() {
    if(this.chart) {
      this.chart.dispose()
      window.removeEventListener('resize', this.resize)
    }
  }
}
</script>
<style lang="less" scoped>
.search {
  margin-bottom: -20px;
  padding: 10px 0;
}
.select {
  width: 100px
}
.chart {
  height: 500px;
}
</style>


