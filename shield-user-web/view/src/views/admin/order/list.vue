<template>
<div class="order-protect">
 <secondBar title="订单管理"></secondBar>
  <el-form class="order-title" :model="searchData" ref="form" size="mini" inline>
    <el-form-item label="业务类型" prop="type">
      <el-select v-model="searchData.type" placeholder="请选择">
        <el-option
          v-for="item in options"
          :key="item.value"
          :label="item.label"
          :value="item.value">
          </el-option>
      </el-select>
    </el-form-item>
    <el-form-item label="支付状态" prop="status">
      <el-select v-model="searchData.status" placeholder="请选择">
        <el-option
          v-for="item in state"
          :key="item.value"
          :label="item.label"
          :value="item.value">
          </el-option>
      </el-select>
    </el-form-item>
    <el-form-item label="日期范围" prop="timeSpan">
      <el-date-picker
          v-model="searchData.timeSpan"
          type="daterange"
          align="center"
          range-separator="-"
          start-placeholder="开始日期"
          end-placeholder="结束日期"
          value-format="timestamp"
         >
      </el-date-picker>
    </el-form-item>
    <el-form-item>
      <el-button type="primary" @click="reset" icon="el-icon-search">查询</el-button>
    </el-form-item>
  </el-form>
  <div>
    <el-table border :data="tableData">
      <el-table-column label="订单号" prop="oid"></el-table-column>
      <el-table-column label="类型">
        <template slot-scope="{ row }">
          {{Number(row.type) === 1 ? '充值': '消费'}}
        </template>
      </el-table-column>
      <el-table-column label="创建时间" prop="create_time">
          <template slot-scope="{ row }">{{row.create_time | formattime(true)}}</template>
      </el-table-column>
      <el-table-column label="状态">
        <template slot-scope="{ row }">
          <router-link class="btn-link" :to="`/order/pay/${row.oid}`" v-if='row.status === 0'>未支付</router-link>
          <span v-else-if='row.status === 1' class="green">已支付</span>
          <span class="red" v-else>已作废</span>
        </template>
      </el-table-column>
      <el-table-column label="金额">
        <span slot-scope="{ row }" class="orange">
          {{row.fee | price}}
        </span>
      </el-table-column>
      <el-table-column label="操作">
        <template slot-scope="{ row }">
          <div class="f-x f-jc handle">
            <span @click="checkDetails(row.oid,row.status)">详情</span>
            <span @click="payDetails(row)" v-if='row.status === 0'>支付</span>
            <span @click="invalid(row.oid)" v-if='row.status === 0'>作废</span>
          </div>
        </template>
      </el-table-column>
      <template slot="empty">
        <i class="el-icon-info"></i> 未找到对应交易记录
      </template>
    </el-table>
  </div>
  <el-dialog title='订单详情' :visible.sync='visible' width='800px' center class="detail-cont">
    <el-alert v-if="seletedRow.status === 0" title="等待支付" type="warning" center show-icon :closable="false"></el-alert>
    <el-alert v-else-if="seletedRow.status === 1" title="已支付" type="success" center show-icon :closable="false"></el-alert>
    <el-alert v-else title="已作废" type="info" center show-icon :closable="false"></el-alert>
    <el-table border :data="[seletedRow]" class="mt-lg">
      <el-table-column label="产品">
        <template slot-scope="{ row }">{{row.detail.product_id | product}}</template>
      </el-table-column>
      <!-- <el-table-column label="数量"></el-table-column> -->
      <el-table-column label="配置">
        <template slot-scope="{ row }">
          <div>基础带宽： {{row.detail.base_bandwidth}}GB</div>
          <div>弹性带宽： {{row.detail.bandwidth}}GB</div>
          <div>线路： {{row.detail.instance_line | line}}</div>
          <div v-if="row.detail.site_count">站点数量： {{row.detail.site_count}}个</div>
          <div v-if="row.detail.port_count">端口数量： {{row.detail.port_count}}个</div>
          <div>业务带宽： {{row.detail.normal_bandwidth}}M</div>
        </template>
      </el-table-column>
      <el-table-column label="起止时间" width="300">
        <template slot-scope="{ row }">
          {{row.detail.start_date | formattime}} - {{row.detail.end_date | formattime}}
        </template>
      </el-table-column>
      <el-table-column label="金额">
        <span slot-scope="{ row }" class="orange">{{row.fee | price}}</span>
      </el-table-column>
    </el-table>
  </el-dialog>
  <div class="f-x f-je page-style">
    <el-pagination background
      @current-change="pageChange"
      :current-page.sync="pageIndex"
      :page-size="pageSize"
      layout="total, prev, pager, next"
      :total="total"></el-pagination>
  </div>
</div>
</template>

<script>
import secondBar from 'components/secondBar'
import userService from 'services/userService'

export default {
  components: {
    secondBar
  },
  data () {
    return {
      options: [{
        value: -1,
        label: '全部'
      }, {
        value: 1,
        label: '充值'
      }, {
        value: 2,
        label: '消费'
      }],
      state: [{
        value: -1,
        label: '全部'
      }, {
        value: 0,
        label: '未支付'
      }, {
        value: 1,
        label: '已支付'
      }, {
        value: 2,
        label: '已作废'
      }],
      visible: false,
      searchData: {
        type: -1,
        status: -1,
        timeSpan: [],
      },
      tableData: [],
      seletedRow: {},
      pageSize: 8,
      pageIndex: 1,
      total: null
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    loadData () {
      const { type, status, timeSpan } = this.searchData
      const [start_date, end_date] = timeSpan || []
      const senddata = {
        type: type !== -1 ? type : undefined,
        status: status !== -1 ? status : undefined,
        start_date,
        end_date,
        _from: (this.pageIndex - 1) * 8,
        _size: this.pageSize
      }
      userService.getOrderList(senddata).then((recvdata) => {
        if (recvdata.errcode === 0) {
          this.tableData = recvdata.data
          this.total = recvdata.total
        }
      })
    },
    reset () {
      // this.$refs.form.resetFields()
      this.loadData()
    },
    checkDetails (order_id, order_type) {
      this.$router.push({
        path: `/order/payInfo/${order_id}/${order_type}`
      })
    },
    payDetails(row) {
      this.$router.push({
        path: `/order/pay/${row.oid}`
      })
    },
    invalid (row) {
      this.$confirm(`您确定要作废ID为${row}的订单吗？`, '作废订单确认', {
        type: 'warning'
      }).then(() => {
        userService.delList(row).then((recvdata) => {
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
    },
    pageChange(val) {
      this.pageIndex = val
      this.loadData()
    },
  }

}
</script>
<style lang="less">
.order-protect {
  background: #fff;
  .order-title {
    .el-select {
      width: 100px
    }

  }
  .detail-cont {
    .el-dialog__title {
      font-size:20px;
    }
    .el-dialog__body {
      font-size:16px;
      .el-col {
        line-height: 30px;
      }
      .el-col.left {
        div {
          text-align:center;
        }
        .el-icon-time {
          font-size:60px;
        }
        .el-icon-check {
          width: 70px;
          height: 70px;
        }
        .el-icon-close {
          width: 70px;
          height: 70px;
        }
      }
      .el-col.right {
        border-left: 1px solid #ddd;
        padding-left: 30px;
      }
    }
  }
  .handle {
    span {
      cursor: pointer;
      position: relative;
      padding: 0 10px;
      color: #409eff;
      &::after {
            content: '';
            position: absolute;
            right: 0;
            top: 20%;
            margin-left: 10px;
            display: inline-block;
            width: 1px;
            height: 60%;
            background: #CCCCCC;
        }
    }
    &>span:last-child {
       &::after{
        background: none;
       }
      }
  }
  .page-style {
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #efefef;
  }
  .el-message-box__content {
    padding: 30px !important;
  }
}

</style>
