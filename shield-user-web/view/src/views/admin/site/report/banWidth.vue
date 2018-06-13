<template>
  <div class="app-flow">
    <el-card  style="margin-bottom: 20px;">
      <lines-chart :id="'chart1'"
      :legend="['in', 'out']"
      :title="'in/out带宽趋势（bps）'"
      :data="chartData"
      :styles="'width: 100%;height: 500px;'">
        <div slot="query">
          <el-form inline>
            <el-form-item label="高防IP" style="margin-right:40px;">
              <el-select v-model="query.ip" placeholder="请选择" size="mini" style="width:100px;">
                <el-option
                  :key="'全部'"
                  :label="'全部'"
                  :value="'全部'"
                >
                </el-option>
              </el-select>
            </el-form-item>

            <el-form-item label="查询时间" style="margin-right:10px;">
              <el-date-picker
                type="date"
                size="mini"
                v-model="query.start_date"
                placeholder="选择日期"
                style="width:130px;">
              </el-date-picker>
              -
              <el-date-picker
                type="date"
                size="mini"
                v-model="query.end_date"
                placeholder="选择日期"
                style="width:130px;">
              </el-date-picker>
            </el-form-item>

            <el-form-item style="margin-right:40px;">
              <el-button type="primary" icon="el-icon-search" size="mini">查询</el-button>
            </el-form-item>

            <el-form-item label="快速查询" style="">
              <el-select v-model="query.time" placeholder="请选择" size="mini" style="width:100px;">
                <el-option
                  :key="'30分钟'"
                  :label="'30分钟'"
                  :value="'30分钟'"
                >
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </div>
        <div slot="bandata">
          <ul class="f-x ban-style">
            <li>网络in带宽峰值：<span style="color:#dea6f4;">14.00Kbps</span></li>
            <li>网络out带宽峰值：<span style="color:#f1bfb4;">0.67Kbps</span></li>
          </ul>
        </div>
      </lines-chart>
    </el-card>

    <el-card>
      <lines-chart :id="'chart2'"
      :legend="['', '']"
      :title="'连接数（个）'"
      :data="chartData"
      :styles="'width: 100%;height: 500px;'">
        <div slot="query" class="f-x">
          <div class="mr-lg">
            <el-select v-model="query.ip" placeholder="请选择" size="mini" style="width:100px;">
              <el-option
                :key="'全部'"
                :label="'全部'"
                :value="'全部'"
              >
              </el-option>
            </el-select>
          </div>
          <div>
            <el-radio label='1' border size='mini' v-model="query.links">开发连接数</el-radio>
            <el-radio label='2' border size='mini' v-model="query.links">新建连接数</el-radio>
          </div>
        </div>
      </lines-chart>
    </el-card>
  </div>
</template>

<style lang="less">
.app-flow {
  .el-form-item {
    margin: 0;
  }
  .ban-style {
    padding: 50px 0;
    li {
      padding-right: 56px;
      span {
        font-size: 26px;
      }
    }
  }
}
</style>

<script>
import linesChart from 'components/charts/linesChart'

export default {
  name: 'flow',
  components: {
    linesChart
  },
  data() {
    return {
      query: {
        ip: '',
        time: '',
        start_date: '',
        end_date: '',
        links: ''
      },
      chartData: [
        ['0:00', '1:00', '2:00', '3:00', '4:00', '5:00'],
        [53, 20, 36, 130, 10, 230],
        [43, 10, 46, 230, 50, 130]
      ]
    }
  }
}
</script>

