<template>
  <div class="app-flow">
    <el-card style="margin-bottom: 20px;" v-loading="loading">
      <lines-chart
        :id="'chart1'"
        :title="'总流量/攻击流量趋势'"
        :legend="['网络接受流量', '网络攻击流量']"
        :data="chartData"
        :showPie="true"
        :styles="'width: 70%;height: 500px;'">
        <div slot="query">
          <el-form inline>
            <el-form-item label="高防IP" style="margin-right:20px;">
              <el-select v-model="highIp" placeholder="请选择" size="mini" style="width:180px;">
                <el-option
                  :key="'全部'"
                  :label="'全部'"
                  :value="-1"
                >
                </el-option>
                <el-option v-for="item in highIpOptions"
                  :label="`${item.area_text}${item.line_text}-${item.ip}`"
                  :value="item.ip"
                  :key="item.ip"
                >
                </el-option>
              </el-select>
            </el-form-item>

            <el-form-item label="查询时间" style="margin-right:10px;">
              <el-date-picker
                v-model="startTime"
                type="datetime"
                size="mini"
                placeholder="选择日期"
                style="width:175px;">
              </el-date-picker>
              -
              <el-date-picker
                v-model="endTime"
                type="datetime"
                size="mini"
                placeholder="选择日期"
                style="width:175px;">
              </el-date-picker>
            </el-form-item>

            <el-form-item style="margin-right:20px;">
              <el-button type="primary" icon="el-icon-search" size="mini" @click="search">查询</el-button>
            </el-form-item>

            <el-form-item label="快速查询" style="">
              <el-select
                v-model="quickTime"
                placeholder="请选择"
                size="mini"
                style="width:100px;"
                @change="quickSearch">
                <el-option
                  v-for="item in quickTimeOptions"
                  :key="item.label"
                  :label="item.label"
                  :value="item.value"
                >
                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
        </div>
      </lines-chart>
    </el-card>
    <el-row :gutter="20" class="attack-item">
      <el-col :span="12">
        <el-card style="margin-bottom: 20px;" v-loading="loading">
          <h3>攻击类型</h3>
          <el-row>
            <el-col :span="12" class="t-c" style="font-size: 24px;color:#f86b4f;"><span style="font-size: 140px;color:#f86b4f;">0</span>种</el-col>
            <el-col :span="12" class="t-c"><img src="/static/img/attackType.png" style="margin-top:50px;"></el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="12">
        <el-card style="margin-bottom: 20px;" v-loading="loading">
          <h3>攻击次数</h3>
          <el-row>
            <el-col :span="12" class="t-c" style="font-size: 24px;color:#f86b4f;"><span style="font-size: 140px;color:#f86b4f;">0</span>次</el-col>
            <el-col :span="12" class="t-c"><img src="/static/img/attackCount.png" style="margin-top:50px;"></el-col>
          </el-row>
        </el-card>
      </el-col>
    </el-row>

  </div>
</template>

<style lang="less">
.app-flow {
  .el-form-item {
    margin: 0;
  }
  .attack-item {
    h3 {
      font-size: 18px;
      font-weight: normal;
    }
  }
}
</style>

<script>
import linesChart from 'components/charts/linesChart'
import siteService from 'services/siteService'

export default {
  name: 'flow',
  components: {
    linesChart
  },
  data() {
    return {
      highIp: -1,
      highIpOptions: [],
      startTime: '',
      endTime: '',
      quickTime: '15:min',
      quickTimeOptions: [
        {
          label: '15分钟',
          value: '15:min'
        },
        {
          label: '30分钟',
          value: '30:min'
        },
        {
          label: '1小时',
          value: '1:hour'
        },
        {
          label: '24小时',
          value: '24:hour'
        }
      ],
      chartData: [
        ['0:00', '1:00', '2:00', '3:00', '4:00', '5:00'],
        [500, 1000, 800, 900, 1500, 1000],
        [200, 1000, 600, 1000, 1500, 2000],
      ],
      loading: false
    }
  },
  computed: {
    siteId() {
      return this.$route.params.id
    }
  },
  created() {
    this.getHighIps()
    this.fetchData()
  },
  methods: {
    getHighIps() {
      siteService.proxyIps(this.siteId)
        .then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }

          this.highIpOptions = res.proxyIps
        })
    },
    getQueryParams() {
      let ret = {}

      if (this.highIp != -1) {
        ret.ip = this.highIp
      }

      if (this.startTime) {
        ret.start_date = new Date(this.startTime).getTime() / 1000
      }

      if (this.endTime) {
        ret.end_date = new Date(this.endTime).getTime() / 1000
      }

      return ret
    },
    search() {
      this.fetchData(this.getQueryParams())
    },
    quickSearch() {
      this.fetchData({quick_time: this.quickTime})
    },
    fetchData(params = {}) {
      this.loading = true
      siteService.attacks(this.siteId, params)
        .then(res => {
          this.loading = false

          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }

          this.chartData = [
            res.data.attacks.map(item => item.time),
            res.data.attacks.map(item => item.max_flow),
            res.data.attacks.map(item => item.max_attack_flow)
          ]
        }, (err) => {
          this.loading = false
        })
    }
  }
}
</script>
