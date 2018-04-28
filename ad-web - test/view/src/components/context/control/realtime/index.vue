<template>
  <el-row>
    <el-col :span='24'>
      <div class='cpuheader'>
        <div>
          <span style="display:inline-block;width:60px;">监控IP:</span>
          <el-input placeholder="请输入IP" v-model="searchIp" size='mini' style="max-width:150px;" @keyup.enter.native="search"></el-input>
          <span style="display:inline-block;font-size:12px">（IP为空表示查询设备总流量）</span><el-button icon="el-icon-search" @click="search">查询</el-button>
        </div>
        <p>
          <span>
            <i v-if='!serverStatus' @click="control()" class="icon-vd-play"></i>
            <i v-else @click="controlOff()" class="icon-vd-pause-20"></i>
            {{serverStatus ? '暂停' : '监控'}}
          </span>
          <el-button @click='refresh()' :disabled='serverStatus'>刷新</el-button>
          <!-- <el-button @click='getFlow(timeRange, ip, true)' :disabled='serverStatus'>刷新</el-button> -->
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
        <h3 v-if='ip'>实时流量监控图：IP:{{ip}} <span style="font-size: 13px;font-weight: 500;margin-left:20px;" v-if='serverStatus'>滤前流量：{{$convertFlow(currentflow.max_in_bps[0], 'bps', 3)}} 滤后流量：{{$convertFlow(currentflow.max_out_bps[0],'bps', 3)}} TCP连接：{{currentflow.tcp_conn[0]}} UDP连接：{{currentflow.udp_conn[0]}}</span></h3>
        <h3 v-else>实时流量监控图：系统总流量 <span style="font-size: 13px;font-weight: 500;margin-left:20px;" v-show='pauseStatus'>滤前流量：{{$convertFlow(currentflow.max_in_bps[0],'bps', 3)}} 滤后流量：{{$convertFlow(currentflow.max_out_bps[0], 'bps', 3)}} TCP连接：{{currentflow.tcp_conn[0]}} UDP连接：{{currentflow.udp_conn[0]}}</span></h3>
        <div id='flow'></div>
      </div>
    </el-col>
  </el-row>
</template>
<script>
import percent from '@/components/common/percent'
import currentService from 'services/currentService'
let chart = null
export default {
  components: {
    percent
  },
  data () {
    return {
      timeRange: 2,
      rangebtn: false,
      flowData: [],
      serverStatus: false,
      pauseStatus:false,
      timer: null,
      searchIp: '',
      ip: '',
      controlData: [],
      controlDataChange: [],
      currentflow: {max_in_bps: [0], max_out_bps: [0], tcp_conn: [0], udp_conn: [0]}
    }
  },
  created () {
    let flowIp = this.$route.params.ip
    if (flowIp) {
      let searchIp = flowIp.replace(/\*/g, '.')
      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/ 
      if (regIp.test(searchIp)) {
        this.searchIp = searchIp
        this.ip = searchIp
      }
    } 
  },
  mounted: function () {
    $('.timerange').hide()
    this.loadSpline()
    this.getFlow(this.timeRange, this.ip)
  },
  watch: {
    timeRange: function () {
      if (this.timeRange > 1) {
        this.serverStatus = false
        this.getFlow(this.timeRange, this.ip)
      }
    }
  },
  methods: {
    control () { // 开启监控
      this.serverStatus = true
      this.pauseStatus = true
      this.timeRange = 1
      currentService.getFlow(2, this.ip)
        .then((res) => {
          if (res.errcode === 0 || res.errcode === '0') {
            this.controlData = res[1]
            this.controlFlow()
          }
        })
        .fail((res) => {
          this.serverStatus = false
          clearTimeout(this.timer)
        })
    },
    //暂停
    controlOff () {
      this.serverStatus = false
      this.pauseStatus = true
      this.timeRange = 0
    },
     // 点击刷新按钮
    refresh () {
      this.pauseStatus = false
      if(this.timeRange>1){
        this.getFlow(this.timeRange, this.ip, true);
      }else{
        this.timeRange = 2;
        this.getFlow(this.timeRange, this.ip, true);
      }
    },
    controlFlow () {
      currentService.getFlow(1, this.ip)
        .then((res) => {
          res[1] = {
            max_in_bps:[res[1].in_bps,res[1].timestamp],
            max_out_bps:[res[1].out_bps,res[1].timestamp],
            tcp_conn:[res[1].tcp_conn,res[1].timestamp],
            udp_conn:[res[1].udp_conn,res[1].timestamp],
            timestamp:res[1].timestamp,
            name:'new'
          }
          this.currentflow = res[1]
          this.controlData.shift()
          this.controlData.push(res[1])
          let flowDataIn = this.controlData.map((item, index) => {
            let obj = {
              type: '滤前流量',
              value: Number(item.max_in_bps[0]),
              num: null,
              timestamp: item.timestamp * 1000
            }
            return obj
          })
          let flowDataOut = this.controlData.map((item, index) => {
            let obj = {
              type: '滤后流量',
              value: Number(item.max_out_bps[0]),
              num: null,
              timestamp: item.timestamp * 1000
            }
            return obj
          })
          let flowDataTCP = this.controlData.map((item, index) => {
            let obj = {
              type: 'TCP连接',
              value: null,
              num: Number(item.tcp_conn[0]),
              timestamp: item.timestamp * 1000
            }
            return obj
          })
          let flowDataUDP = this.controlData.map((item, index) => {
            let obj = {
              type: 'UDP连接',
              value: null,
              num: Number(item.udp_conn[0]),
              timestamp: item.timestamp * 1000
            }
            return obj
          })
          this.flowData = [...flowDataIn, ...flowDataOut, ...flowDataTCP, ...flowDataUDP]
          if (this.serverStatus) {
            this.refreshFlowData()
          }
          if (this.serverStatus) {
            clearTimeout(this.timer)
            this.timer = setTimeout(() => {
              this.controlLength--
              this.controlFlow()
            }, 2000)
          }
        })
    },
    getFlow (index, ip, status = false) {
      currentService.getFlow(index, ip)
        .then((res) => {
          if (res.errcode === 0) {
            this.ip = this.searchIp
            this.controlLength = res[1].length
            let flowDataIn = res[1].map((item, index) => {
              let obj = {
                type: '滤前流量',
                value: Number(item.max_in_bps[0]),
                num: null,
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataOut = res[1].map((item, index) => {
              let obj = {
                type: '滤后流量',
                value: Number(item.max_in_bps_after_clean[0]),
                num: null,
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataTCP = res[1].map((item, index) => {
              let obj = {
                type: 'TCP连接',
                value: null,
                num: Number(item.tcp_conn[0]),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataUDP = res[1].map((item, index) => {
              let obj = {
                type: 'UDP连接',
                value: null,
                num: Number(item.udp_conn[0]),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let newVal = [...flowDataIn, ...flowDataOut, ...flowDataTCP, ...flowDataUDP] 
            this.flowData = newVal
            this.refreshFlowData(status)
          }
        })
        .fail((res) => {
          this.serverStatus = false
          clearTimeout(this.timer)
        })
    },
    loadSpline () {
      let _this = this
      chart = new G2.Chart({
        id: 'flow',
        forceFit: true,
        height: 450,
        // padding: [50, 200, 50, 50],
        animate: false,
        plotCfg: {
          margin: [ 50, 120, 80, 120 ]
        }
      })
      chart.axis('timestamp', {
        title: null // 不展示 timestamp 对应坐标轴的标题
      })
      chart.legend({
        title: null,
        position: 'bottom'
      })
      chart.tooltip({
          crosshairs: {
            type: 'rect' || 'x' || 'y' || 'cross'
          }, // 
      })  
      chart.source(this.flowData, {
        timestamp: {
          tickCount: 5,
          type: 'time',
          mask: 'HH:MM:ss'
        },
        value: {
          alias: '流量(bps)'
        },
        num: {
          alias: '连接(个)'
        }
      })
      chart.line().position('timestamp*value').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
      chart.line().position('timestamp*num').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
      chart.render()
    },
    refreshFlowData (status = false) {
      if (status) {
        chart.changeData(this.flowData)
        return
      }
      let _this = this
      if (this.serverStatus) {
        if (this.controlLength === 6) {
          chart.source(this.flowData, {
            timestamp: {
              tickInterval: 15 * 1000,
              type: 'time',
              mask: 'HH:MM:ss'
            },
            value: {
              alias: '流量(bps)',
              min: 0
            },
            num: {
              alias: '连接(个)'
            }
          })
        }
        chart.changeData(this.flowData)
      } else {
        chart.destroy()
        chart = new G2.Chart({
          id: 'flow',
          forceFit: true,
          height: 450,
          animate: false,
          plotCfg: {
            margin: [ 50, 120, 80, 120 ]
          }
        })
        chart.source(this.flowData, {
          timestamp: {
            tickInterval: 15 * 1000,
            type: 'time',
            mask: 'HH:MM:ss'
          },
          value: {
            alias: '流量(bps)',
            min: 0
          },
          num: {
            alias: '连接(个)',
            min:0
          }
        })
        chart.axis('timestamp', {
          title: null // 不展示 timestamp 对应坐标轴的标题
        })
         chart.tooltip({
            crosshairs: {
              type: 'rect' || 'x' || 'y' || 'cross'
            }, // 
        })  
        chart.legend({
          title: null,
          position: 'bottom'
        })
        chart.coord().scale(0.98, 1)
        chart.axis('value', {
          formatter: (val) => {
            return this.$convertFlow(val, 'bps', 3)
          }
        })
       chart.axis('num', {
          // title: null,
          alias: '连接(个)',
          formatter: (val) => {
            return this.$convertNum(val, 3)
          }
        })
        chart.on('tooltipchange', (ev) => {
          for (let i = 0; i < ev.items.length; i++) {
            if (ev.items[i].name !== 'TCP连接' && ev.items[i].name !== 'UDP连接') {
             ev.items[i].value = this.$convertFlow(ev.items[i].value, 'bps', 3)
           }
            if (ev.items[i].name !== '滤前流量' && ev.items[i].name !== '滤后流量') {
              ev.items[i].value = this.$convertNum(ev.items[i].value, 3)
            }
          }
        })
        chart.line().position('timestamp*value').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
        chart.line().position('timestamp*num').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
        // 查看7天
        if (this.timeRange === 7) {
          chart.source(this.flowData, {
            timestamp: {
              tickInterval: 24 * 60 * 60 * 1000,
              type: 'time',
              mask: 'yyyy-mm-dd HH:MM:ss'
            },
            value: {
              alias: '流量(bps)',
              min: 0
            }
          })
        } else if (this.timeRange === 9) {//最近一个月
            chart.source(this.flowData, {
            timestamp: {
              tickCount:6,
              tickInterval: 24 * 60 * 60 * 1000 * 5,
              type: 'time',
              mask: 'yyyy-mm-dd HH:MM:ss'
            },
            value: {
              alias: '流量监控',
              min: 0
            }
          })
        } else if (this.timeRange === 2) {//最近十五分钟
          chart.source(this.flowData, {
            timestamp: {
              tickInterval: 60 * 1000 * 2,
              type: 'time',
              mask: 'HH:MM:ss'
            },
            value: {
              alias: '流量(bps)',
              min: 0
            }
          })
        }else {
          chart.source(this.flowData, {
            timestamp: {
              tickCount: 12,
              type: 'time',
              mask: 'HH:MM:ss'
            },
            value: {
              alias: '流量(bps)',
              minTickInterval: 1024 * 1024 * 1024,
              min: 0
              // nice: true
              // splitNumber: 10
            }
          })
        }
        chart.render()
      }
    },
    search () {
      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      if (this.searchIp === '') {
        this.ip = ''
        this.getFlow(this.timeRange, this.ip)
        return 
      }
      if (regIp.test(this.searchIp)) {
        this.getFlow(this.timeRange, this.searchIp)
      } else {
        this.$message({
          type: 'warning',
          message: 'IP格式有误',
          duration: '1500'
        })
      }
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
      this.pauseStatus = false;
      $('.timerange').hide()
    },
    rangeType (index) {
      let type = ''
      switch (index) {
      case 1:
        type = '实时'
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
      case 0:
        type = '暂停' 
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
      }else if(this.timeRange < 2){
        this.timeRange = 2
      }else{
        this.timeRange-- 
      }
    },
    nextbtn () {
      if (this.timeRange === 9) {
        return 
      }else if(this.timeRange < 2){
        this.timeRange = 2
      }else{
        this.timeRange++ 
      }
    }
  },
  beforeDestroy () {
    this.serverStatus = false
    clearInterval(this.timer)
  }
}
</script>

<style scoped lang='scss'>
  .cpuheader{
    display: flex;
    justify-content: space-between;
    div {
      display: flex;
      justify-content: flex-start;
      align-items: center;
    }
    input{
      height: 32px;
    }
    span {
      font-size: 15px;
      margin: 0;
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
    margin-top: 20px;
    background: #fff; 
    padding: 15px;
    h3{
      text-align:center;
      font-size: 15px;
      font-weight: 600;
    }
  }
</style>