<template>
  <div class="ip-config-block" style="height: 100%;">
    <strong class="name">基本配置</strong>
    <configItem label="防护类型">
      <xRadio name="type" v-model="type" :label="1">网站类</xRadio>
      <xRadio name="type" v-model="type" :label="3">应用类</xRadio>
    </configItem>
    <configItem label="版本" v-if="type !== 3">
      <xRadio name="version" v-model="version" :label="1">共享</xRadio>
      <xRadio name="version" v-model="version" :label="2">独享</xRadio>
      <template slot="desc">独享：实例为购买者专属；共享：多个用户共享实例资源  </template>
    </configItem>
    <configItem label="线路">
      <xRadio v-for="(item, i) in lineOptions" name="line" v-model="line"
        :label="item.value" :key="item.value" @change="checkLine(i)">{{item.text}}</xRadio>
    </configItem>
    <configItem label="IP个数">
      <xRadio name="ip_count" v-model="ip_count"
        :label="currentLine.ip_count.value || 0">{{currentLine.ip_count.text}}</xRadio>
    </configItem>
    <configItem label="保底防护带宽">
      <xRadio v-for="item in currentLine.base_bandwidth" name="base_bandwidth" v-model="base_bandwidth" :label="item.value" :key="item.value">{{item.text}}</xRadio>
      <template slot="desc">此部分为保底带宽，预付费</template>
    </configItem>
    <configItem label="弹性防护带宽">
      <xRadio v-for="item in flexBanWidth" :key="item.value" name="bandWidth" v-model="bandwidth" :label="item.value">{{item.text}}</xRadio>
      <template slot="desc">
        <span>弹性防护带宽值跟保底防护带宽值设置相同，则不会产生后付费；弹性带宽值设置高于保底带宽值，则超过的部分在清洗防护时会产生弹性费用</span>
      </template>
    </configItem>
    <configItem v-if="type === 3" label="端口数">
      <el-input-number size="mini" controls-position="right" :step="5" :min="currentLine.port_count.min" :max="currentLine.port_count.max" v-model="port_count" label="个"></el-input-number>
    </configItem>
    <configItem v-else label="防护域名数">
      <el-input-number size="mini" controls-position="right" :step="5" :min="currentLine.site_count.min" :max="currentLine.site_count.max" v-model="site_count"></el-input-number>
    </configItem>
    <configItem label="业务带宽">
      <xRadio v-for="item in currentLine.normal_bandwidth" name="normal_bandwidth" v-model="normal_bandwidth" :label="item.value" :key="item.value">{{item.text}}</xRadio>
    </configItem>
  </div>
</template>
<script>
import configItem from './configItem'
import xRadio from 'components/xRadio'
import areas from 'utils/areas'

const bindField = key => ({
  get() {
    return this.config[key]
  },
  set(val) {
    this.$emit('change', key, val)
    if(key === 'base_bandwidth' && this.bandwidth < val) {
      // 如果修改了基础带宽，且弹性带宽小于基础带宽，重置弹性带宽值
      this.$emit('change', 'bandwidth', val)
    }
  }
})

export default {
  components: {
    configItem,
    xRadio
  },
  props: {
    config: {
      type: Object,
      default() {
        return {}
      }
    },
    configOptions: {
      type: Object,
      default() {
        return {}
      }
    },
  },
  data() {
    return {
      areas,
      lineIndex: 0
    }
  },
  computed: {
    type: bindField('type'),
    version: bindField('version'),
    line: bindField('line'),
    ip_count: bindField('ip_count'),
    base_bandwidth: bindField('base_bandwidth'),
    bandwidth: bindField('bandwidth'),
    port_count: bindField('port_count'),
    site_count: bindField('site_count'),
    normal_bandwidth: bindField('normal_bandwidth'),
    flexBanWidth() {
      return this.currentLine.bandwidth.filter(item => Number(item.value) >= Number(this.base_bandwidth))
    },
    periodOptions() {
      return this.configOptions.ord_time || []
    },
    lineOptions() {
      return this.configOptions.line || []
    },
    currentLine() {
      return this.lineOptions[this.lineIndex] || {
        bandwidth: [],
        base_bandwidth: [],
        ip_count: {},
        port_count: {},
        site_count: {}
      }
    }
  },
  watch: {
    lineIndex(val) {
      this.$emit('change', 'ip_count', this.currentLine.ip_count.value)
    }
  },
  methods: {
    checkLine(i) {
      this.lineIndex = i
    }
  },
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.ip-config {
  &-block {
    position: relative;
    padding: 10px 10px 10px 30px;
    background: #fff;
    border-left: 30px solid #f3f3f3;
    box-shadow: 0 0 8px rgba(0, 0, 0, .2);

    .name {
      position: absolute;
      top: 50%;
      left: -21px;
      width: 1em;
      text-align: center;
      line-height: 1.2;
      color: #999;
      transform: translateY(-50%)
    }
    .x-radio {
      margin-right: 2px;
      margin-bottom: 2px;
    }
    .el-cascader--mini {
      height: 28px;
      width: 120px;
      .el-input__inner {
        color: #fff;
        background: @brand-color;
        border-color: @brand-color;
        border-radius: 0
      }
    }
    .el-input__suffix,
    .el-cascader__label,
    .el-cascader__label span {
      color: #fff
    }

  }
}
</style>
