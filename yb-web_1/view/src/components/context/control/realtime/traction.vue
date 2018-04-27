<template>
  <el-row>
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content">  
            <p class="tracTital">
              <el-button type="primary" style="padding:5px 12px;vertical-align:top;" @click="refresh">
                <i v-if='!monitorStatus' class="icon-vd-play"></i>
                <i v-else class="icon-vd-pause-20"></i>
                <span class="monitor-text" style="position:relative;top:-2px">{{monitorStatus ? '暂停' : '监控'}}</span>
              </el-button>
            </p>         
            <el-table :data='blackHole' border>
              <el-table-column prop='ip' label='被牵引数据IP' align="center"></el-table-column>
              <el-table-column prop='start_time' label='牵引时间'  align="center"></el-table-column>
              <el-table-column prop='bytes_speed' label='被牵引时的速率' align="center"></el-table-column>
              <el-table-column prop='pkts_speed'label='被牵引时包速率(个/s)' align="center"></el-table-column>
              <el-table-column prop='in_threshold' label='被牵引时阈值速率' align="center"></el-table-column>
              <el-table-column prop='end_time' label='反牵引时间' align="center"></el-table-column>
              <el-table-column prop='type' label='牵引类型' align="center"></el-table-column>
              <el-table-column label="操作" align="center">
                <template slot-scope="scope">
                  <el-button
                    size="mini"
                    type="text"
                    @click="handleDelete(scope.$index, scope.row)">反牵引</el-button>
                </template>
              </el-table-column>
            </el-table>
          </div>
      </el-col>
  </el-row>
</template>
<script>
import currentService from 'services/currentService'
export default {
  data () {
    return {
      load: true,
      blackHole: [],
      timer: {},
      monitorStatus: false
    }
  },
  created () {
    this.getData()
  },
  methods: {
    getData () {
      currentService.getBlackHole()
        .then((res) => {
          if (res.errcode === 0) {
            this.blackHole = res.black_hole.map((item) => {
              item.bytes_speed = this.$convertFlow(Number(item.bytes_speed), 'B')
              item.in_threshold = this.covertFlowMb(Number(item.in_threshold))
              item.type = item.type === 0 ? '手动牵引' : '自动牵引'
              return item
            })
            if (this.monitorStatus) {
              clearTimeout(this.timer)
              this.timer = setTimeout(() => {
                this.getData()
              }, 5000)
            }
          }
          this.load = false
        })
        .fail(() => {
          clearTimeout(this.timer)
          this.monitorStatus = false
        })
    },
    handleDelete (index, row) {
      $('.el-message').remove()
      this.$confirm('此操作将完成反牵引, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'info'
      })
        .then(() => {
          currentService.setTraction(row.ip)
            .then((res) => {
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  type: 'success',
                  message: '反牵引成功',
                  duration: 1500
                })
                this.getData()
              }
            })
        })
        .catch(() => {
            
        })
    },
    refresh () {
      this.monitorStatus = !this.monitorStatus
      if (this.monitorStatus) {
        this.getData()
      }
    },
    covertFlowMb (val) {
      if (!val) { 
        return '0 B/S'
      }
      if (val < 1024) {
        return val + ' MB/S'
      }
      if (val >= 1027 && val < 1024 * 1024) {
        let flow = val / 1024 
        return flow.toFixed(2) + ' GB/S'
      }
    }
  },
  beforeDestroy () {
    clearTimeout(this.timer)
    this.monitorStatus = false
  }
}
</script>
<style scoped>
.tracTital {
  /* display: flex;
  justify-content: flex-end; */
  text-align: left;
  margin-bottom: 15px;
}
</style>

