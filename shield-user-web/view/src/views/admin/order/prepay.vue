<template>
  <div>
    <secondBar title="确认订单" hasBack></secondBar>
    <el-row style="margin-top:20px;">
      <el-table border
        :data="tableData"
        style="width:100%">
        <el-table-column
          align="center"
          label="产品配置">
          <template slot-scope="{ row }">
            <div class="item">
              <span class="label">类型：</span>
              <span class="value">{{row.config.type | bType}}</span>
            </div>
            <div class="item" v-if="row.config.type !== 3">
              <span class="label">版本：</span>
              <span class="value">{{row.config.version | sType}}</span>
            </div>
            <!-- <div class="item">
              <span class="label">地域：</span>
              <span class="value">{{row.config.area | area}}</span>
            </div> -->
            <div class="item">
              <span class="label">线路：</span>
              <span class="value">{{row.config.line | line}}</span>
            </div>
            <div class="item">
              <span class="label">保底防护带宽：</span>
              <span class="value">{{row.config.base_bandwidth}}GB</span>
            </div>
            <div class="item">
              <span class="label">弹性防护带宽：</span>
              <span class="value">{{row.config.bandwidth}}GB</span>
            </div>
            <div class="item" v-if="row.config.type === 3">
              <span class="label">端口数：</span>
              <span class="value">{{row.config.port_count}}个</span>
            </div>
            <div class="item" v-else>
              <span class="label">防护域名数：</span>
              <span class="value">{{row.config.site_count}}个</span>
            </div>
            <div class="item">
              <span class="label">业务带宽：</span>
              <span class="value">{{row.config.normal_bandwidth}}M</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="购买周期">
          <template slot-scope="scope">
            <span>{{scope.row.config.period | period}}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="购买数量">
          <template slot-scope="scope">
            <span>{{scope.row.config.count}}个</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="资费">
          <template slot-scope="scope">
            <span class="price">{{scope.row.price | price}}</span>
          </template>
        </el-table-column>
        <!-- <el-table-column
          label="数量">
        </el-table-column>
        <el-table-column
          label="优惠">
        </el-table-column>
        <el-table-column
          label="资费">
        </el-table-column> -->
      </el-table>
    </el-row>
    <el-row style="text-align:right;">
      <div style="margin-top:20px;margin-bottom:20px;">
        <label>应付款：</label>
        <span style="font-size:24px;color:#f60;vertical-align:-1px;" v-if="tableData.length">{{tableData[0].price | price}}</span>
        <!-- <span style="margin-left:10px;font-size:12px;color:#390;">省:￥1003.20</span> -->
      </div>
      <el-button class="btn-pay" @click="createOrder" :loading="isSubmitting">去下单</el-button>
    </el-row>
  </div>
</template>

<script>
import secondBar from 'components/secondBar'
import OrderService from 'services/orderService'

export default {
  components: {
    secondBar
  },
  data () {
    return {
      tableData: [],
      isSubmitting: false
    }
  },
  created () {
    const orderJsonStr = window.sessionStorage.getItem(this.$route.query.t)
    if (orderJsonStr) {
      this.tableData = [JSON.parse(orderJsonStr)]
    }
  },
  methods: {
    createOrder () {
      const {config, price} = this.tableData[0]

      const params = {
        type: 2,
        fee: price,
        detail: {
          instance_line: config.line,
          base_bandwidth: config.base_bandwidth,
          bandwidth: config.bandwidth,
          ord_time: config.period,
          sp_num: config.count,
          normal_bandwidth: config.normal_bandwidth
        }
      }
      if (config.type !== 3) {
        params.detail.product_id = config.version
      } else {
        params.detail.product_id = config.type
      }

      if (config.type === 1) {
        params.detail.site_count = config.site_count
      } else {
        params.detail.port_count = config.port_count
      }

      this.isSubmitting = true
      OrderService.createOrder(params)
        .then((res) => {
          if (res.errcode === 0) {
            this.goPay(res.data.id)
          } else {
            this.$message.error(res.errmsg)
          }
        })
        .always(() => {
          this.isSubmitting = false
        })
    },
    goPay (orderId) {
      this.$router.push({
        path: `/order/pay/${orderId}`
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';
.item {
  display: flex;
  align-items: center;
  font-size: 12px;
  line-height: 2;
}
.label {
  min-width: 100px;
  color: @text-gray-color;
}
.price {
  color: @text-orange-color;
}
</style>
