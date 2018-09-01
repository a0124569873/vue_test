<template>
  <div>
    <secondBar title="支付" hasBack></secondBar>
    <div v-loading="loading" style="position:relative;">
      <el-row style="margin-top:20px;">
        <el-card>
          <div slot="header">
            <span style="font-size:16px;">订单信息</span>
            <span style="float:right;color:#999;font-size:18px;" v-if="order.price">{{order.price | price}}</span>
          </div>
          <div v-if="order.config">
            类型：{{order.config.product_id | product}}
            <template v-if="order.config.product_id != 0">，
              线路：{{order.config.instance_line | line}}，
              保底防护带宽：{{order.config.base_bandwidth}}GB，
              弹性防护带宽：{{order.config.bandwidth}}GB，
              <template v-if="order.config.product_id == 3">
                端口数：{{order.config.port_count}}个，
              </template>
              <template v-else>
                防护域名数：{{order.config.site_count}}个，
              </template>
              业务带宽：{{order.config.normal_bandwidth}}M
            </template>
          </div>
        </el-card>
      </el-row>
      <el-row>
        <div class="pay-type-header">
          <span>支付方式</span>
          <div style="float:right">
            <span style="font-size:12px;vertical-align: 2px;">支付</span>
            <span class="pay-text" v-if="order.price">{{order.price | price}}</span>
          </div>
        </div>
        <div style="margin-top: 20px;padding-left:40px;">
          <el-radio v-model="payType" :label="1">
            <img src="../../../assets/img/pay-zhifubao.png" height="26"></el-radio>
          <el-radio v-model="payType" :label="2">
            <img src="../../../assets/img/pay-weixin.png" height="26">
            <span style="color:#333;vertical-align: -1px;">微信支付</span>
          </el-radio>
          <el-radio v-model="payType" :label="3">
            <span style="color:#333;vertical-align: -1px;">余额支付</span>
          </el-radio>
        </div>
      </el-row>
      <el-row style="margin-top:40px;" type="flex" justify="end">
        <el-button class="btn-pay" @click="goResult">确认支付</el-button>
      </el-row>
    </div>
  </div>
</template>

<script>
import secondBar from 'components/secondBar'
import OrderService from 'services/orderService'
import PayService from 'services/payService'

export default {
  components: {
    secondBar
  },
  data () {
    return {
      payType: 1,
      order: {},
      loading: true
    }
  },
  created () {
    OrderService.getOrder(this.$route.params.order_id)
      .then((res) => {
        this.loading = false
        if (res.errcode === 0) {
          const { detail, fee } = res.data
          this.order = {
            config: detail,
            price: fee
          }
          // if () {
          //   this.order = {
          //     config: Number(detail.product_id) === 0 ? {
          //       type: 0
          //     } : detail,
          //     price: fee
          //   }
          // } else {
          //   this.order = {
          //     config: {
          //       type: detail.product_id,
          //       line: detail.instance_line,
          //       base_bandwidth: detail.base_bandwidth,
          //       bandwidth: detail.bandwidth,
          //       period: detail.ord_time,
          //       port_count: detail.port_count,
          //       site_count: detail.site_count,
          //       normal_bandwidth: detail.normal_bandwidth
          //     },
          //     price: fee
          //   }
          // }
        }
      })
  },
  methods: {
    goResult () {
      PayService.pay(this.$route.params.order_id).then((res) => {
        if (res.errcode === 0) {
          this.$router.push('/order/pay_result')
        } else {
          this.$message.error(res.errmsg)
        }
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';
.pay-type-header {
  margin-top: 40px;
  padding: 10px 15px;
  border-left: 2px solid #ff9a00;
  background-color: #f2f2f2;
}
.pay-text {
  color: @text-orange-color;
  font-size: 18px;
  line-height: 23px;
}
</style>
