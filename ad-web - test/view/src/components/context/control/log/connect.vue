<template>
    <el-row>
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span='24'>  
        <div class="content">
          <el-form style="text-align:left" :inline="true" ref="myForm" :model="queryFormData" :rules='rules' class="demo-form-inline">
            <el-form-item label="" prop='host_ip'>
              <el-input v-model="queryFormData.host_ip" placeholder="主机IP"></el-input>
            </el-form-item>
            <el-form-item label="" style="margin-right:0" required></el-form-item>
            <el-form-item prop="timeStart">
              <el-date-picker
                style="width:180px"
                v-model="queryFormData.timeStart"
                type="datetime"
                placeholder="开始时间"
                align="right">
              </el-date-picker>
            </el-form-item>
            <label class="el-form-item__label" style="position:relative;top:-5px;font-size:12px">至</label>
            <el-form-item prop="timeEnd">
              <el-date-picker
                style="width:180px"
                v-model="queryFormData.timeEnd"
                type="datetime"
                placeholder="结束时间"
                align="right">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="">
              <el-select v-model="queryFormData.range" @change="queryLog('myForm')">
                <el-option label='秒级统计' key="1" value='1'></el-option>
                <el-option label='分钟统计' key="2" value='2'></el-option>
                <el-option label='小时统计' key="3" value='3'></el-option>
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button style="padding:7px 12px" icon='el-icon-search' type="primary" @click="queryLog('myForm')">查询</el-button>
              <el-button style="padding:7px 12px" icon='el-icon-download' type="primary" @click="exportLogs()">导出当前日志</el-button>
            </el-form-item>
          </el-form>  
          <el-table :data='logsData' border v-loading="isQueryLoading">
            <el-table-column prop='time' label='统计区间' align="center"></el-table-column>
            <el-table-column label='最大值' align="center">
              <el-table-column label='TCP in' align="center" prop='flux.tcp_max_conn_in'></el-table-column>
              <el-table-column label='TCP out' align="center" prop='flux.tcp_max_conn_out'></el-table-column>
              <el-table-column label='UDP' align="center" prop='flux.udp_max_conn'></el-table-column>
            </el-table-column>
            <el-table-column label='平均值' align="center">
              <el-table-column label='TCP in' align="center" prop='flux.tcp_avg_conn_in'></el-table-column>
              <el-table-column label='TCP out' align="center" prop='flux.tcp_avg_conn_out'></el-table-column>
              <el-table-column label='UDP' align="center" prop='flux.udp_avg_conn'></el-table-column>
            </el-table-column>
          </el-table> 
          <div class="page"  v-if="total>0">
            <el-pagination  @size-change="pageSize" @current-change="handleCurrentChange" :current-page="curPage"
                :page-sizes="[10, 20, 40, 80, 100]" :page-size="per_page_num" layout="sizes, prev, pager, next " :total="total">
            </el-pagination>      
          </div>       
        </div>
      </el-col>
    </el-row>  
</template>
<script>
import logService from 'services/logService'
export default {
  data () {
    const checkIp = (rule, value, callback) => {
      if (!value) {
        return callback()
      }

      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      if (!regIp.test(value)) {
        return callback(new Error('IP格式错误'))
      }
      return callback()
    }
    return {
      load: false,
      logsData: [],
      curPage: 1,
      per_page_num: 10,
      total: 0,
      isQueryLoading: false,
      queryFormData: {
        host_ip: '',
        timeStart: '',
        timeEnd: '',
        range: '1'
      },
      rules: {
        host_ip: [{ validator: checkIp, trigger: 'blur' }]
      }
    }
  },
  created () {
    // this.load = true
    this.getConnlogs()
  },
  methods: {
    changeTime (val) {
      console.log(val)
    },
    buildToday (hours, minutes, seconds) {
      let today = new Date()
      today.setHours(hours)
      today.setMinutes(minutes)
      today.setSeconds(seconds)
      return today
    },
    getConnlogs () {
      let send = {
        page: this.curPage, 
        row: this.per_page_num, 
        host_ip: this.queryFormData.host_ip,
        range: this.queryFormData.range,
        end_time: Date.parse(this.queryFormData.timeEnd),
        start_time: Date.parse(this.queryFormData.timeStart)
      }
      logService.getConnlogs(send).then((recvdata) => {
        this.load = false
        this.isQueryLoading = false
        if (recvdata.errcode === 0) {
          this.logsData = recvdata['3'].logs
          this.total = recvdata['3'].count
        }
      }).always(() => {
        // 增加一个加载效果，故意延迟一些时间用来显示
        setTimeout(() => {
          this.isQueryLoading = false
        }, 200)
      })
    },
    pageSize (val) {
      this.per_page_num = val
      this.getConnlogs()
    },
    handleCurrentChange (val) {
      this.curPage = val
      this.getConnlogs()
    },
    covertFlowMb (val) {
      if (!val || val === '0') { 
        return '-'
      }
      if (val < 1024) {
        return val + ' Mbps'
      }
      if (val >= 1027 && val < 1024 * 1024) {
        let flow = val / 1024 
        return flow.toFixed(2) + ' Gbps'
      }
    },
    // 提交查询
    queryLog (myForm) {
      let timeStart = this.queryFormData.timeStart
      let timeEnd = this.queryFormData.timeEnd
      if ((timeStart && !timeEnd) || (!timeStart && timeEnd)) {
        this.$message({
          message: '查询时间可以都不选，但不能只选一个',
          type: 'error',
          duration: '1500'
        })
        return
      }
      
      this.$refs[myForm].validate((valid) => {
        if (valid) {
          this.isQueryLoading = true
          this.getConnlogs()
        }
      })
    },
    exportLogs () {
      let params = {
        t: 3,
        page: this.curPage, 
        row: this.per_page_num, 
        host_ip: this.queryFormData.host_ip,
        range: this.queryFormData.range,
        end_time: Date.parse(this.queryFormData.timeEnd),
        start_time: Date.parse(this.queryFormData.timeStart),
        export: true
      }
      this.DownLoadFile({url: '/logs/get', data: this.filterNull(params)})
    },
    DownLoadFile (options) {
      var config = $.extend(true, { method: 'get' }, options)
      var $iframe = $('<iframe id="down-file-iframe" />')
      var $form = $('<form target="down-file-iframe" method="' + config.method + '" />')
      $form.attr('action', config.url)
      for (var key in config.data) {
        $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />')
      }
      $iframe.append($form)
      $(document.body).append($iframe)
      $form[0].submit()
      $iframe.remove()
    },
    toType (obj) {
      return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
    },
    // 参数过滤函数
    filterNull (o) {
      for (var key in o) {
        if (!o[key]) {
          delete o[key]
        }
        if (this.toType(o[key]) === 'string') {
          o[key] = o[key].trim()
        } else if (this.toType(o[key]) === 'object') {
          o[key] = this.filterNull(o[key])
        } else if (this.toType(o[key]) === 'array') {
          o[key] = this.filterNull(o[key])
        }
      }
      return o
    }
  }
}
</script>
<style scoped>
.page{
  margin: 15px 0;
}
</style>
