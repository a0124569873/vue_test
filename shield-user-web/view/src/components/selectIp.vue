<template>
  <div class="g-select-ip" v-loading="loading">
    <template v-if="!!ipList.length">
      <table>
        <thead>
          <tr>
            <th>实例ID</th><th>地区</th><th>高防IP</th>
          </tr>
        </thead>
        <tr v-for="(item, i) in ipList" class="item" :key="i">
          <td>{{item.ddos_id}}</td>
          <td>{{item.area | area}}</td>
          <td>
            <el-checkbox-group v-model="ipsValue[item.ddos_id]">
              <el-checkbox v-for="it in item.ips"
                :label="it.ip"
                :key="it.ip"
                @change="selectIp(item.ddos_id, it)"
                >
                {{it.line_text}} {{it.ip}}
              </el-checkbox>
            </el-checkbox-group>
          </td>
      </tr>
      </table>
      <div class="mt-md f-x f-ac f-js">
        <div>已选择 <strong class="counter">{{value.length}}</strong> 个IP</div>
        <el-pagination
          background
          layout="total, prev, pager, next"
          :page-size="pageSize"
          :current-page.sync="pageIndex"
          :total="total"
          @current-change="pageChange"
          style="line-height:1">
        </el-pagination>
      </div>
    </template>
    <div class="el-alert el-alert--info" v-if="showBuy">
      <i class="el-alert__icon el-icon-info"></i>
      <div class="el-alert__content">暂无可用实例，
        <router-link to="/buy" class="btn-link">去购买 &gt;&gt;</router-link></div>
    </div>
  </div>
</template>
<script>
import ddosService from 'services/ddosService'
import { mapAreas } from 'utils/areas'

export default {
  props: {
    value: Array,
    type: [Number, String],
  },
  filters: {
    area (value) {
      return mapAreas[value]
    }
  },
  data() {
    return {
      ddos: [],
      ipList: [],
      pageIndex: 1,
      pageSize: 5,
      total: 1,
      showBuy: false,
      loading: false,
      ipsValue: {} // 每个ddos_id对应IP数组，{ddos_id: [xxx, xxx]}
    }
  },
  created() {
    this.serverList()
    this.initIpsValue()
  },
  watch: {
    type(newVal, oldVal) {
      this.pageIndex = 1
      if (oldVal) {
        this.serverList()
      }
    },
    value() {
      // this.ipsValue = this.value ? this.value.map(item => item.ip) : []
      this.initIpsValue()
    }
  },
  methods: {
    initIpsValue() {
      let ddosArr = this.value.reduce((ddosVals, item) => {
        return ddosVals.add(item.ddos_id)
      }, new Set())

      ddosArr.forEach(ddos_id => {
        this.ipsValue[ddos_id] = this.value.filter(item => item.ddos_id == ddos_id).map(item => item.ip)
      })
    },
    serverList() {
      this.loading = true
      const query = {
        _from: (this.pageIndex - 1) * 5,
        _size: this.pageSize
      }
      if(this.type) query.type = this.type
      ddosService.ips(query).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.ipList = res.list
        if (this.ipList.length > 0) {
          this.showBuy = false
        } else {
          this.showBuy = true
        }

        this.total = res.total
        this.loading = false
      })
    },
    isChecked(ddos_id, ip) {
      let result = this.value.some(item => {
        return (ip === item.ip && ddos_id === item.ddos_id)
      })
      return result
    },
    selectIp(ddos_id, { ip, line }) {
      const checked = this.isChecked(ddos_id, ip)
      const data = !checked ? this.value.concat({
        ddos_id,
        ip,
        line
      }) : this.value.filter(item => ip !== item.ip || ddos_id !== item.ddos_id)
      this.$emit('input', data)
    },
    pageChange(cur) {
      this.pageIndex = cur
      this.serverList()
    }
  },
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.g-select-ip {
  min-height: 100px;
  table {
    // min-width: 750px;
    width: 100%;
    border: 1px solid #e8e8e8
  }
  th {
    background-color: #efefef;
  }
  td {
    min-width: 100px;
    height: 36px;
    padding: 0 16px;
    border-left: 1px solid #e8e8e8;
    border-bottom: 1px solid #e8e8e8;
    text-align: center;
    &:last-child {
      text-align: left;
      // background: #f3f3f3
    }
  }
  .item {
    margin-top: -1px;
    padding: 8px 12px;
    border: 1px solid #e8e8e8;
  }
  .counter {
    color: @brand-color
  }
  .el-checkbox {
    margin-left: 0;
    margin-right: 30px
  }
}

</style>

