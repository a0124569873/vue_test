<template>
  <el-row :element-loading-text="loading_text">
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content"> 
            <el-alert :center="false" title="" type="info" :closable="false" style="margin-bottom:20px" show-icon>
              <template>
                <p> 
                  <span class="g-status-down">红色IP</span>表示设备处于断开状态，<span class="g-status-up">绿色IP</span>表示设备处于活动状态</span>
                </p>
              </template>
            </el-alert>     
            <div style="margin-bottom:20px;text-align:right">
              <label style="margin-right:5px">前端机保活时间</label>
              <el-select v-model="serverTimeGap" placeholder="请选择" style="margin-right:20px;width:100px">
                <el-option
                  v-for="item in serverTimeOptions"
                  :key="item[1]"
                  :label="item[0]"
                  :value="item[1]">
                </el-option>
              </el-select>
              <label style="margin-right:5px">自定义(单位：秒)</label>
              <el-input-number v-model="serverTimeGap" style="width:120px" controls-position="right" placeholder="自定义保活时间"></el-input-number>
            </div>         

            <div style="margin-bottom:20px;text-align:right">
              <label style="margin-right:5px">G设备保活时间</label>
              <el-select v-model="timeGap" placeholder="请选择" style="margin-right:20px;width:100px">
                <el-option
                  v-for="item in timeOptions"
                  :key="item[1]"
                  :label="item[0]"
                  :value="item[1]">
                </el-option>
              </el-select>
              <label style="margin-right:5px">自定义(单位：秒)</label>
              <el-input-number v-model="timeGap" style="width:120px" controls-position="right" placeholder="自定义保活时间"></el-input-number>
            </div>         
            <el-table :data='topo' border stripe class="topo-table">
              <el-table-column type='index' label='序号' width="80" align="center"></el-table-column>
              <el-table-column label='前端机IP' prop="fip" align="center">
                <template slot-scope="scope">
                  <span :class="[scope.row.status === 'up' ? 'g-status-up' : 'g-status-down']">{{scope.row.ip}}</span>
                </template>
              </el-table-column>
              <el-table-column label='G设备IP' prop="gip" align="center">
                <template slot-scope="scope">
                  <div class="my-cell" v-for="item in scope.row.gip" :key="item.ip">
                    <span :class="[item.status === 'up' ? 'g-status-up' : 'g-status-down']">{{item.ip}}</span>
                  </div>
                </template>
              </el-table-column>
            </el-table>

            <div class="page" style="margin-top:15px" v-if="total > 0">
              <el-pagination  @size-change="pageSizeChange" @current-change="handleCurrentChange" :current-page="currentPage"
                :page-sizes="[10, 20, 40, 80, 100]" :page-size="pageSize" layout="sizes, prev, pager, next" :total="total">
              </el-pagination>      
            </div>       
          </div>
      </el-col>
  </el-row>
</template>
<script>
import TopoService from 'services/topoService'
import Pagination from '@/utils/pagination'
const THRESHHOLD = 5000

export default {
  data () {
    return {
      load: true,
      loading_text: '',  
      topo: [],
      allTopo: [],
      timer: null,
      type: '6',
      timeGap: (window.localStorage.getItem('timeGap') * 1) || 10,
      serverTimeGap: (window.localStorage.getItem('serverTimeGap') * 1) || 60,
      pageSize: 10,
      total: 0,
      currentPage: 1,
      serverTimeOptions: [['30秒', 30], ['45秒', 45], ['1分钟', 60], ['2分钟', 120], ['5分钟', 300]],
      timeOptions: [['10秒', 10], ['20秒', 20], ['30秒', 30], ['1分钟', 60], ['2分钟', 120]]
    }
  },
  created () { 
    this.loading_text = '正在努力加载中...'
  },
  mounted () {
    this.getTopo()
  },
  watch: {
    timeGap: function (newVal, oldVal) {
      window.localStorage.setItem('timeGap', newVal)
    },
    serverTimeGap: function (newVal, oldVal) {
      window.localStorage.setItem('serverTimeGap', newVal)
    }
  },
  methods: {
    getTopo () {
      if (!this.load) {
        this.load = true
      }
      TopoService.getTopo()
        .then(
          res => {
            if (res.errcode === 0) {
              let data = res[this.type]
              if (data) {
                let ret = []
                for (const frontIp in data) {
                  ret[frontIp] = {
                    ip: frontIp,
                    status: 'down',
                    gip: data[frontIp].map((item) => {
                      return {
                        ip: item,
                        status: 'down'
                      }
                    })
                  }
                }
                this.allTopo = ret
                let pager = new Pagination(this.allTopo, this.pageSize)
                this.topo = pager.page(this.currentPage)
                this.total = Object.keys(this.allTopo).length

                this.getTopoStats()
              }
            }
          }
        ).always(() => {
          this.load = false
        })
    },

    getTopoStats () {
      TopoService.getTopoStats()
        .then((res) => {
          // res = {
          //   '6': {
          //     '192.168.2.15': {
          //       'timestamp': 1513589778,
          //       '1.1.1.2': {
          //         'state': 'up',
          //         'timestamp': '1513589788'
          //       },
          //       '1.1.1.3': {
          //         'state': 'up',
          //         'timestamp': '1513160211'
          //       }
          //     },
          //     '192.168.2.18': {
          //       'timestamp': 1513169574,
          //       '1.1.1.1': {
          //         'state': 'up',
          //         'timestamp': '1513160209'
          //       }
          //     }
          //   },
          //   'errcode': 0,
          //   'errmsg': 'ok'
          // }
          if (res.errcode === 0) {
            let data = res[this.type]

            let serverTime = res[this.type]['server_time'] || parseInt((+new Date()))

            if (data) {
              for (let frontIp in data) {
                if (frontIp !== 'server_time') {
                  // 状态接口数据对应的单条前端机IP数据
                  let fip = data[frontIp]
                  let fStatus = Math.abs(fip.timestamp - serverTime) > this.serverTimeGap * 1000 ? 'down' : 'up'

                  // 总数据对应的单条前端机IP数据对象
                  let front = this.allTopo[frontIp]
                  if (front) {
                    front.status = fStatus
                  }
                  
                  // 总数据中单条前端机IP对应的G设备IP数组
                  let gip = front.gip
              
                  // 循环状态接口数据的前端机IP， 里边对应的key是G设备的ip
                  for (let ip in fip) {
                    if (ip !== 'timestamp') {
                      // front[ip].status = fStatus === 'down' ? 'down' : this.computeStatus(fip[ip].state, fip[ip].timestamp, fip.timestamp)
                      // 更新总数据中前端机IP对应G设备IP数组中每个对象的status
                      gip.forEach((item) => {
                        item.status = fStatus === 'down' ? 'down' : this.computeStatus(fip[ip].state, fip[ip].timestamp, fip.timestamp)
                      })
                    }
                  }
                }
              }
              
              let pager = new Pagination(this.allTopo, this.pageSize)
              this.topo = pager.page(this.currentPage)
            }

            this.timer && clearTimeout(this.timer) // 先清除timer
            this.timer = setTimeout(() => {
              this.getTopoStats()
            }, THRESHHOLD)
          }
        }).always(() => {
          this.load = false
        })
    },
    computeStatus (state, time, ftime) {
      if (state === 'down') {
        return 'down'
      } else {
        if (Math.abs(ftime - time) > this.timeGap * 1000) {
          return 'down'
        } 
      }

      return 'up'
    },
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      let pager = new Pagination(this.allTopo, this.pageSize)
      this.topo = pager.page(this.currentPage)
      // this.total = pager.totalPage
    },
    handleCurrentChange (val) {
      this.currentPage = val
      let pager = new Pagination(this.allTopo, this.pageSize)
      this.topo = pager.page(this.currentPage)
      // this.total = pager.totalPage
    }
  },
  beforeDestroy () {
    this.timer && clearTimeout(this.timer)
  }
}
</script>
<style scoped>
.g-status-up {
  color: #67c23a
}
.g-status-down {
  color: red
}
.my-cell {
  border-top: 1px solid #e6ebf5;
  margin: 0 -10px;
  margin-top: -1px;
  padding: 10px;
  line-height: 1
}
.my-cell:first-child {
  padding-top: 4px
}
.my-cell:last-child {
  padding-bottom: 4px
}
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
