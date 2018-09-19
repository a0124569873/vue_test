<template>
  <div class="order-infos">
    <secondBar :title='orderId' :hasBack='true' backUrl='/order'></secondBar>
    <el-card class="mb-lg">
      <div class="f-x f-jc f-ac">
        <div class="f-1">
          <div class="t-c">
            <img src="/static/img/order.png" width="35">
            <p class="mt-lg" v-if="orderType == 0 && order_status == 0">订单未支付</p>
            <p class="mt-lg" v-if="orderType == 1 && order_status == 1">订单已支付</p>
            <p class="mt-lg" v-if="orderType == 2 || order_status == 2">订单已作废</p>
          </div>
        </div>
        <div class="f-1">
          <ul>
            <li>订单号：{{orderData.oid}}</li>
            <li>订单金额：<span class="blue">{{orderData.fee | price}}</span></li>
            <li>订单类型：{{orderData.type == 1 ? '充值' : '消费'}}</li>
            <li>创建时间：{{orderData.create_time | formattime(true)}}</li>
            <li v-if="orderData.pay_time!=null">支付时间：{{orderData.pay_time | formattime(true)}}</li>
            <li class="mt-lg" v-if="orderType == 0">
              <template v-if="order_status == 0">
                <el-button type="primary" size="mini">立即支付</el-button>
                <el-button size="mini" @click="invalid" >作废订单</el-button>
              </template>
            </li>
          </ul>
        </div>
      </div>
    </el-card>
    <el-card v-if="orderData.type == 2">
      <el-table border
        :data="tableData"
        style="width:100%">
        <el-table-column
          align="center"
          label="消费项">
          <template slot-scope="{ row }">
            <div class="item">
              <span class="label">高防实例 </span>
            </div>
            <div class="item">
              <span class="label">实例名称：</span>
            </div>
            <div class="item">
              <span class="label" v-for="item in row.instance_id">{{item}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="数量">
          <template slot-scope="{ row }">
            <span>{{row.sp_num}}</span>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="具体配置">
          <template slot-scope="{ row }">
            <div class="item">
              <span class="label">类型：</span>
              <span class="value">{{row.product_id==4 ? '应用类' : '网站类'}}</span>
            </div>
            <div class="item">
              <span class="label">版本：</span>
              <span class="value">{{row.product_id | ipType}}</span>
            </div>
            <div class="item">
              <span class="label">线路：</span>
              <span class="value">{{row.instance_line | line}}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column
          align="center"
          label="付款方式">
          <template slot-scope="{ row }">
            <span v-if="row.ord_time < 12">按月（{{row.ord_time}}）月</span>
            <span v-if="row.ord_time >= 12">按年（{{Number(row.ord_time/12)}}）年</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="起止时间">
          <template slot-scope="{ row }">
            <span style="font-size:12px">开始时间：{{row.start_date | formattime(true)}}<br>结束时间：{{row.end_date | formattime(true)}}</span>
          </template>
        </el-table-column>
        <el-table-column
          align="center"
          label="金额">
          <template slot-scope="{ row }">
            <span class="blue">{{row.fee | price}}</span>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import orderService from 'services/orderService'
import userService from 'services/userService'
export default {
  components: {
    secondBar
  },
  computed: {
    orderId() {
      let oid = this.$route.params.order_id
      return `订单号 ${oid}`
    },
    orderType() {
      return this.$route.params.order_type
    }
  },
  data() {
    return {
      orderData: {},
      product_type: null,
      tableData: [],
      order_status: null
    }
  },
  created() {
    this.loadData()
  },
  methods: {
    loadData() {
      let oid = this.$route.params.order_id
      orderService.getOrder(oid).then(recvdata => {
        if (recvdata.errcode === 0) {
          this.orderData = recvdata.data
          this.tableData = [recvdata.data.detail]
          this.order_status = this.orderData.status
        } else {
          this.$message.error(recvdata.errmsg)
        }
      })
    },
    invalid () {
      let oid = this.$route.params.order_id
      this.$confirm(`您确定要作废ID为${oid}的订单吗？`, '作废订单确认', {
        type: 'warning'
      }).then(() => {
        userService.delList(oid).then((recvdata) => {
          if (recvdata.errcode === 0) {
            this.$message({
              showClose: true,
              message: '订单已作废！',
              type: 'success',
              duration: 1000,
              onClose: () => {
                this.loadData()
              }
            })
          } else {
            this.$message({
              showClose: true,
              message: recvdata.errmsg,
              type: 'warning'
            })
          }
        })
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';
  .order-infos {
    ul {
      li{
        line-height: 28px;
      }
    }
    .item {
      display: flex;
      align-items: center;
      font-size: 12px;
      line-height: 2;
    }
    .label {
      color: @text-gray-color;
    }
  }
</style>
