<template>
  <el-row :element-loading-text="loading_text">
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content">           
            <el-form :inline="true" style="text-align:left" ref="myForm" :model="formData" :rules="rules">                 
              <el-form-item label="排名显示个数：" prop="limit">
                <el-input type="number" size="mini" v-model.number="formData.limit" auto-complete="off"></el-input>
              </el-form-item>
              <el-form-item label="(0表示显示全部)"></el-form-item>                 
              <el-form-item>
                <el-button type="primary" style="padding:7px 12px" @click="onSubmit"> 查 询 </el-button>
                <el-button type="primary" style="padding:5px 12px;vertical-align:top;" @click="refresh">
                    <i v-if='!monitorStatus' class="icon-vd-play"></i>
                    <i v-else class="icon-vd-pause-20"></i>
                    <span class="monitor-text">{{monitorStatus ? '暂停' : '监控'}}</span>
                </el-button>
              </el-form-item>
            </el-form>
            <el-table :data='rank' border :default-sort="{prop: 'flow_in', order: 'descending'}" @sort-change="sortType">
              <el-table-column type='index' label='序号' width="80" align="center"></el-table-column>
              <el-table-column label='IP' align="center">
                <template slot-scope="scope">
                  <el-button @click="handleIPClick(scope.row.server_ip)" type="text" size="small">{{scope.row.server_ip}}</el-button>
                </template>
              </el-table-column>
              <el-table-column prop='flow_in' label='输入流量' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='flow_out' label='输出流量' align="center" sortable='custom'></el-table-column>
              <el-table-column prop='in_pkt' label='输入数据包个数' align="center" sortable='custom'></el-table-column>
              <el-table-column label='操作' align='center'>
                <template slot-scope="scope">
                  <el-button @click="shouDongQianYin(scope.row.server_ip)" type="text" style="padding:5px 12px">手动牵引</el-button>
                </template>
              </el-table-column>
            </el-table>
          </div>
      </el-col>
  </el-row>
</template>
<script>
import ControlService from 'services/controlService'
const THRESHHOLD = 2000
let timer = null
let checkLimit = function (rule, value, callback) {
  if (!(Number.isInteger(value) && Number(value) >= 0)) {
    return callback(new Error('排名必须是正整数'))
  }

  callback()
}

export default {
  data () {
    return {
      load: false,
      loading_text: '',  
      orderBy: 'flow_in',
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
      }
    }
  },
  created () { 
    this.loading_text = '正在努力加载中...'
    // 排序组件会自动调用接口，所以这里不需要再次调用，否则会一开始出现两次调用
    // this._loadData({orderby: this.orderby, limit: this.limit})
  },

  watch: {
    monitorStatus: function (val) {
      console.log(val)
    }
  },

  methods: {
    _loadData (params) {
      ControlService.getRank(params)
        .then(
          res => {
            if (res.errcode === 0) {
              this.rank = res.rank.map((item) => {
                let data = item.split('|')
                return {
                  server_ip: data[0],
                  flow_in: this.$convertFlow(data[1], 'B'),
                  flow_out: this.$convertFlow(data[2], 'B'),
                  in_pkt: data[3]
                }
              })

              if (this.monitorStatus) {
                this.$nextTick(() => {
                  timer && clearTimeout(timer) // 先清除timer
                  timer = setTimeout(() => {
                    this._loadData(params)
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
      this.params = {orderBy: this.orderBy, limit: this.formData.limit}
      timer && clearTimeout(timer)
      this._loadData(this.params)
    },
    onSubmit () {
      this.$refs['myForm'].validate((valid) => {
        if (valid) {
          timer && clearTimeout(timer)
          this.params = {orderBy: this.orderBy, limit: this.formData.limit}
          this._loadData(this.params)
        }
      })
    },
    refresh () {
      this.monitorStatus = !this.monitorStatus
      timer && clearTimeout(timer) // 先清除timer
      if (this.monitorStatus) {
        this._loadData(this.params)
      } 
    },
    _setGuide (ip) {
      $('.el-message').remove()
      ControlService.delGuide(ip)
        .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              showClose: true,
              message: '操作成功',
              type: 'success',
              duration: 1500
            })
          }
        })
    },
    shouDongQianYin (ip) {
      this.$confirm('此操作将完成手动牵引, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'info'
      }).then(() => {
        this._setGuide(ip)
      }).catch()
    },
    handleIPClick (ip) {
      this.$router.push({path: '/flow/' + ip.replace(/\./g, '*')})
    }
  },
  beforeDestroy () {
    timer && clearTimeout(timer)
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
