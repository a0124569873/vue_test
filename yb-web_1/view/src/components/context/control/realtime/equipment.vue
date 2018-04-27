<template>
  <el-row>
    <el-col :span='24'>
      <div class='cpuheader'>
        <p><span>运行状态</span></p> 
        <p>
          <span>
            <i v-if='!serverStatus' @click="serverStatus=!serverStatus" class="icon-vd-play"></i>
            <i v-else @click="serverStatus=!serverStatus" class="icon-vd-pause-20"></i>
            {{serverStatus ? '暂停' : '监控'}}
          </span>
          <el-button @click='getState(timeRange)' :disabled='serverStatus'>刷新</el-button>
          <i class="el-icon-caret-left" @click="prebtn"></i>
          <span @click='changeRange'>{{rangeType(timeRange)}}</span>
          <i class="el-icon-caret-right" @click="nextbtn"></i>
        </p>    
      </div>
      <div class="timerange">
        <p>时间范围：</p>
        <ul>
          <li><el-button @click="show(2)">最近15分钟</el-button></li>
          <li><el-button @click="show(3)">最近30分钟</el-button></li>
          <li><el-button @click="show(4)">最近1小时</el-button></li>
          <li><el-button @click="show(5)">最近12小时</el-button></li>
          <li><el-button @click="show(6)">最近24小时</el-button></li>
          <li><el-button @click="show(7)">最近7天</el-button></li>
          <li><el-button @click="show(8)">今天</el-button></li>
          <li><el-button @click="show(9)">当月</el-button></li>
        </ul>
      </div>
      <div class="chart">
        <h3 style="text-align:center">设备CPU运行状态</h3>
        <percent :picData.sync='cpuData' :timeRange.sync="timeRange" :status='serverStatus'></percent> 
      </div>
      <div class="chart">
        <h3 style="text-align:center">设备内存存储状态</h3>
        <memory :picData.sync='memoryData' :timeRange.sync="timeRange" :status='serverStatus' :total='memoryTotal'></memory> 
      </div>
      <div class="chart" style="margin-bottom:15px">
        <h3 style="text-align:center">设备硬盘存储状态</h3>
        <disk :picData='diskData' :timeRange="timeRange" :status='serverStatus' :total='diskTotal'></disk>  
      </div>
    </el-col>
  </el-row>
</template>
<script>
import percent from '@/components/common/percent'
import memory from '@/components/common/memory'
import disk from '@/components/common/disk'
import currentService from 'services/currentService'
export default {
  components: {
    percent,
    memory,
    disk
  },
  data () {
    return {
      timeRange: 2,
      rangebtn: false,
      cpuData: [],
      memoryData: [],
      diskData: [],
      serverStatus: false,
      timer: null,
      memoryTotal: 0,
      diskTotal: 0
    }
  },
  mounted: function () {
    $('.timerange').hide()
    this.getState(this.timeRange)
  },
  watch: {
    timeRange: function () {
      this.serverStatus = false
      this.getState(this.timeRange)
      this.$emit('update:timeRange', this.timeRange)
    },
    serverStatus: function () {
      this.getState(this.timeRange)
    }
  },
  methods: {
    getState (index) {
      currentService.getState(index)
        .then((res) => {
          if (res.errcode === 0) {
            let cpuDataUser = res[1].map((item, index) => {
              let obj = {
                value: Number(item.value.user),
                timestamp: item.timestamp * 1000,
                type: '用户空间CPU'
              }
              return obj
            })
            let cpuDataSys = res[1].map((item, index) => {
              let obj = {
                value: Number(item.value.system),
                timestamp: item.timestamp * 1000,
                type: '内核空间CPU'
              }
              return obj
            })
            this.cpuData = cpuDataUser.concat(cpuDataSys)
            let memoryTotal = 0
            let diskTotal = 0
            this.memoryData = res[2].map((item, index) => {
              if (memoryTotal < Number(item.value.total)) {
                memoryTotal = Number(item.value.total)
              }
              let obj = {
                value: Number(item.value.used),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            this.memoryTotal = memoryTotal
            this.diskData = res[3].map((item, index) => {
              if (diskTotal < Number(item.value.total)) {
                diskTotal = Number(item.value.total)
              }
              let obj = {
                value: Number(item.value.used),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            this.diskTotal = diskTotal
            if (this.serverStatus) {
              clearTimeout(this.timer)
              this.timer = setTimeout(() => {
                this.getState(this.timeRange)
              }, 5000)
            }
          }
        })
        .fail((res) => {
          this.serverStatus = false
          clearInterval(this.timer)
        })
    },
    changeRange () {
      this.rangebtn = !this.rangebtn 
      if (this.rangebtn) {
        $('.timerange').show()
      } else {
        $('.timerange').hide()
      }
    },
    show (index) {
      this.timeRange = index
      this.rangebtn = !this.rangebtn
      $('.timerange').hide()
    },
    rangeType (index) {
      let type = ''
      switch (index) {
      case 1:
        type = '当前'
        break
      case 2:
        type = '最近15分钟'
        break
      case 3:
        type = '最近30分钟'
        break
      case 4:
        type = '最近1小时'
        break
      case 5:
        type = '最近12小时'
        break
      case 6:
        type = '最近24小时'
        break
      case 7:
        type = '最近7天'
        break
      case 8:
        type = '今天'
        break
      case 9:
        type = '当月'
        break
      default:
        type = '当前'
        break
      }
      return type
    },
    prebtn () {
      if (this.timeRange === 2) {
        return 
      }
      this.timeRange--
    },
    nextbtn () {
      if (this.timeRange === 9) {
        return 
      }
      this.timeRange++
    }
  },
  beforeDestroy () {
    this.serverStatus = false
    clearTimeout(this.timer)
  }
}
</script>

<style scoped lang='scss'>
  .cpuheader{
    display: flex;
    justify-content: space-between;
    span {
      font-size: 15px;
      margin:0 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      i{
        margin: 0 10px;
      }
    }
    button{
      margin: 0 10px;
    }
    p{
      display: flex;
      align-items: center;
    }
    i {
        width: 20px;
        font-size: 25px;
        cursor: pointer;
        margin: 0 5px;
      }
  }
  .timerange{
    margin-top: 15px;
    padding: 10px 20px;
    box-sizing: border-box;
    p {
      font-size: 15px;
    }
    background: #fff;
    display: flex;
    justify-content: flex-start;
    ul {
      max-width: 500px;
      display: flex;
      justify-content: space-between;
      flex-wrap: wrap;
      li{
        padding: 5px 15px;
      }
    }
  }
  .chart{
    padding: 20px 0;
    box-sizing: border-box;
    margin-top: 15px;
    background: #fff; 
    h3{
      font-size: 15px;
      font-weight: 600;
    }
  }
</style>