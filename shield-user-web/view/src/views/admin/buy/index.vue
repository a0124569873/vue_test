<template>
  <div class="buy-ip">
    <secondBar title="购买实例" hasBack></secondBar>
    <el-row :gutter="20" type="flex" v-loading="loading">
      <el-col :span="18">
        <config :config="value" @change="onChange"
          :configOptions="configOptions || {}"></config>
      </el-col>
      <el-col :span="6">
        <result :config="value" :price="price" :loading="priceLoading"></result>
      </el-col>
    </el-row>
    <div class="ip-config-block" style="margin-top: 20px">
      <strong class="name">购买量</strong>
      <configItem label="购买数量">
        <el-input-number size="mini" controls-position="right" :min="1" :max="10" v-model="count"></el-input-number>
      </configItem>
      <configItem label="购买时长">
        <el-radio-group size="mini" v-model="period">
          <el-radio-button v-for="item in periodOptions" :key="item.value" :label="item.value">{{item.text}}</el-radio-button>
        </el-radio-group>
      </configItem>
    </div>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import config from './config'
import result from './result'
import configItem from './configItem'
import serverService from 'services/serverService'

export default {
  name: 'buyIp',
  components: {
    secondBar,
    config,
    result,
    configItem
  },
  data() {
    return {
      value: {
        type: 1,
        version: 1,
        area: [1, 11],
        count: 1,
        // net: '0',
        // base_bandwidth: '10',
        // bandwidth: '10',
        // port_count: 50,
        // site_count: 50,
        // period: '1:month'
      },
      price: 0,
      loading: false,
      priceLoading: false,
      configOptions: null
    }
  },
  computed: {
    count: {
      get() {
        return this.value.count
      },
      set(val) {
        this.onChange('count', val)
      }
    },
    period: {
      get() {
        return this.value.period
      },
      set(val) {
        this.onChange('period', val)
      }
    },
    periodOptions() {
      return this.configOptions ? (this.configOptions.ord_time || []) : []
    },
  },
  methods: {
    onChange(key, value) {
      if(value === 'undefined' || this.value[key] === value) return
      this.value = {
        ...this.value,
        [key]: value
      }
      this.calcPrice()
    },
    createModel() {
      const { type, version, line, ip_count, base_bandwidth, bandwidth, port_count: portCount, site_count: siteCount, normal_bandwidth, count, period } = this.value

      const body = {
        line,
        ip_count,
        base_bandwidth,
        bandwidth,
        normal_bandwidth,
        count,
        period,
      }
      if(type !== 3) {
        body.type = version
        body.site_count = siteCount
      } else {
        body.type = type
        body.port_count = portCount
      }
      return body
    },
    calcPrice() {
      this.priceLoading = true
      const body = this.createModel()
      serverService.price(body).then(res => {
        this.priceLoading = false
        if(res.errcode !== 0) {
          return
        }
        this.price = res.amount
      })
    },
    fetchOptions() {
      if(this.configOptions) return Promise.resolve()
      this.loading = true
      return serverService.config().then(res => {
        this.loading = false
        if(res.errcode !== 0) {
          this.$message(res.errmsg)
          return
        }
        this.configOptions = res.data
      })
    },
    initValue() {
      const initLine = this.configOptions.line[0]
      this.value = {
        ...this.value,
        line: initLine.value,
        ip_count: initLine.ip_count.value,
        base_bandwidth: initLine.base_bandwidth[0].value,
        bandwidth: initLine.bandwidth[0].value,
        port_count: initLine.port_count.min,
        site_count: initLine.site_count.min,
        period: this.configOptions.ord_time[0].value,
        normal_bandwidth: initLine.normal_bandwidth[0].value,
      }
    }
  },
  created() {
    this.fetchOptions().then(() => {
      this.initValue()
      this.calcPrice()
    })
  }
}
</script>
<style lang="less">
.buy-ip {
  &-content {
    padding: 20px 0
  }
}
</style>

