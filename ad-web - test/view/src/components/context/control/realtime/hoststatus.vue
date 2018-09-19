<template>
  <el-row :element-loading-text="loading_text">
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content">           
            <el-form :inline="true" style="text-align:left" ref="myForm" :model="formData" :rules="rules">                 
              <el-form-item label="排名显示个数：" prop="limit">
                <el-input type="number" size="mini" v-model.number="formData.limit" auto-complete="off"></el-input>
              </el-form-item>                
              <el-form-item>
                <el-button type="primary" style="padding:7px 12px" @click="onSubmit"> 查 询 </el-button>
                <el-button type="primary" style="padding:5px 12px;vertical-align:top;" @click="refresh">
                    <i v-if='!monitorStatus' class="icon-vd-play"></i>
                    <i v-else class="icon-vd-pause-20"></i>
                    <span class="monitor-text">{{monitorStatus ? '暂停' : '监控'}}</span>
                </el-button>
              </el-form-item>
            </el-form>
            <el-table :data='rank' border :default-sort="{prop: 'in_bps', order: 'descending'}" @sort-change="sortType">
              <el-table-column type='index' label='序号' width="80" align="center"></el-table-column>
              <el-table-column label='IP' align="center">
                <template slot-scope="scope">
                  <el-button @click="handleIPClick(scope.row.ip)" type="text" size="small">{{scope.row.ip}}</el-button>
                </template>
              </el-table-column>
              <el-table-column prop='in_bps' label='输入流量(Mbps)' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='in_bps_after_clean' label='滤后输入流量(Mbps)' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='in_pps' label='输入包速率(pps)' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='in_pps_after_clean' label='滤后输入包速率(pps)' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='tcp_conn' label='TCP连接数' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='udp_conn' label='UDP连接数' align="center" sortable='custom'></el-table-column>
            </el-table>
          </div>
      </el-col>
  </el-row>
</template>
<script>
import currentService from 'services/currentService'

const THRESHHOLD = 2000
let checkLimit = function (rule, value, callback) {
  if (!(Number.isInteger(value) && Number(value) > 0)) {
    return callback(new Error('排名必须是正整数'))
  }

  callback()
}

export default {
  data () {
    return {
      load: false,
      loading_text: '',  
      orderBy: 'in_bps',
      order: 'descending',
      limit: 10,
      rank: [],
      monitorStatus: false,
      params: {},
      formData: {
        limit: 10
      },
      rules: {
        limit: [
          { validator: checkLimit, trigger: 'blur' }
        ]
      },
      timer: null
    }
  },
  created () { 
    this.loading_text = '正在努力加载中...'
    // 排序组件会自动调用接口，所以这里不需要再次调用，否则会一开始出现两次调用
    let isDesc = false
    this.order === 'ascending' ? isDesc = false : isDesc = true
    this._loadData(this.orderby, this.limit, isDesc)
  },
  methods: {
    _loadData (orderby, limit, desc) {
      currentService.getHostStatus(orderby, limit, desc)
        .then(
          res => {
            if (res.errcode === 0) {
              this.rank = res['18'].map((item) => {
                item.in_bps = this.$formatBps(item.in_bps)
                item.in_bps_after_clean = this.$formatBps(item.in_bps_after_clean)
                item.in_pps = item.in_pps
                item.in_pps_after_clean = item.in_pps_after_clean
                item.tcp_conn = item.tcp_conn
                item.udp_conn = item.udp_conn
                return item
              })
              if (this.monitorStatus) {
                this.$nextTick(() => {
                  this.timer && clearTimeout(this.timer) 
                  this.timer = setTimeout(() => {
                    this._loadData(orderby, limit, desc)
                  }, THRESHHOLD)
                })
              }
            }
          }
        ).always(() => {
          this.load = false
        })
    },
    sortType (obj) {
      if (!(Number.isInteger(this.formData.limit) && Number(this.formData.limit) >= 0)) {
        return false
      }
      if (obj.prop !== null) {
        this.orderBy = obj.prop
      }
      this.order = obj.order // 升序还是降序
      this.timer && clearTimeout(this.timer) 
      let isDesc = false
      this.order === 'ascending' ? isDesc = false : isDesc = true
      this._loadData(this.orderBy, this.formData.limit, isDesc)
    },
    onSubmit () {
      this.$refs['myForm'].validate((valid) => {
        if (valid) {
          this.timer && clearTimeout(this.timer)
          let isDesc = false
          this.order === 'ascending' ? isDesc = false : isDesc = true
          this._loadData(this.orderBy, this.formData.limit, isDesc)
        }
      })
    },
    refresh () {
      this.monitorStatus = !this.monitorStatus
      this.timer && clearTimeout(this.timer) // 先清除timer
      if (this.monitorStatus) {
        let isDesc = false
        this.order === 'ascending' ? isDesc = false : isDesc = true
        this._loadData(this.orderBy, this.formData.limit, isDesc)
      } 
    },
    handleIPClick (ip) {
      this.$router.push({path: '/control/realtime/' + ip.replace(/\./g, '*')})
    }
  },
  beforeDestroy () {
    this.timer && clearTimeout(this.timer)
  }
}
</script>
<style scoped>
.title {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  font-size: 16px;
}
.monitor-text {
  position:relative;
  top:-2px
}
</style>
