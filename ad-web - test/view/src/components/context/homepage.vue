<template>
  <div class="home-wrap">
    <el-row type="flex" class="row-bg" justify="space-between">
      <el-col class="block-wrap " :span="6">          
        <h4>防护信息</h4>
        <div class="phover hostnum">
          <p class="num color-num">{{host_num}}</p>
          <p>监控主机数</p>
        </div>
        <p class="phover">运行时间: <span class="color-num">{{run_time}}</span> </p>    
     </el-col>
     <el-col class="block-wrap " :span="12">          
        <h4>流量概况</h4>
        <div id='general'>
          <table>
            <tr>
              <td rowspan="2">方向</td>
              <td colspan="2">流量（Mbps）</td>
              <td colspan="2">报文（pps）</td>
            </tr>
            <tr>
              <td>滤前</td>
              <td>滤后</td>
              <td>滤前</td>
              <td>滤后</td>
            </tr>
            <tr>
              <td>输入</td>
              <td>{{handleNum(flow_table.in_bps)}}</td>
              <td>{{handleNum(flow_table.in_submit_bps)}}</td>
              <td>{{flow_table.in_pps ? flow_table.in_pps : 0}}</td>
              <td>{{flow_table.in_submit_pps ? flow_table.in_submit_pps : 0}}</td>
            </tr>
            <tr>
              <td>输出</td>
              <td>{{handleNum(flow_table.out_bps)}}</td>
              <td>{{handleNum(flow_table.out_submit_bps)}}</td>
              <td>{{flow_table.out_pps ? flow_table.out_pps : 0}}</td>
              <td>{{flow_table.out_submit_pps ? flow_table.out_submit_pps : 0}}</td>
            </tr>
          </table>
        </div>
     </el-col>  
      <el-col class="block-wrap" :span="6">    
        <h4>证书信息</h4>    
        <div @click="JumpTO('/control/sysinfo')">
          <p class="phover">设备识别号: {{cert.device_id?cert.device_id:'-'}}</p>
          <p class="phover">设备型号:{{cert.model?cert.model:'-'}}</p>   
          <p class="phover">授权日期: {{cert.start_time?cert.start_time:'-'}}</p>
          <p class="phover">授权有效期: {{cert.end_time?cert.end_time:'-'}}</p>
          <p class="phover">系统版本：{{cert.version}}</p>
        </div>                     
      </el-col>   
    </el-row> 
    <el-row  type="flex" class="row-bg" justify="space-between">
      <el-col class="block-wrap" :span="24">    
        <el-row type="flex" class="pic row-bg" justify="space-between">
          <el-col  :span="8">
            <h4>CPU状态</h4>  
            <div id="cpu"></div> 
          </el-col>
          <el-col :span="8">    
            <h4>内存状态</h4>
            <div id="momery"></div>  
          </el-col>
          <el-col :span="8">      
            <h4>硬盘状态</h4>
            <div id="disk"></div>  
          </el-col>
        </el-row> 
      </el-col>
    </el-row>  
    <el-row  type="flex" class=" pic row-bg" justify="space-between">
      <el-col class="block-wrap" :span="24"> 
        <el-row type="flex" class="pic row-bg" justify="space-between">
          <el-col>    
            <div class="title">
              <h4>实时流量监控</h4>  
              <!-- <el-button class="butn" type="text" @click="JumpTO('/realtime')"> 详 情 </el-button> -->
            </div>       
            <div id="flows"></div>  
          </el-col>
        </el-row>         
      </el-col>
    </el-row>
  </div>
</template>
<script>
import indexService from 'services/indexService'

export default {
  data () {
    return {
      host_num: 0,
      run_time: 0,
      flow_table: {},
      cpu: [     
        {name: '已用', value: 0},
        {name: '空闲', value: 100}
      ],
      momery: [
        {name: '已用', value: 0},
        {name: '可用', value: 100}
      ],
      disk: [
        {name: '已用', value: 0},
        {name: '可用', value: 100}
      ],
      flow: [ ],
      int: 0,
      rAF: null,
      circleColor: '',
      needDrawCircle: true,
      cpuchart: null,
      diskchart: null,
      momerychart: null,
      splinechart: null,
      intervaltData: [{name: '接收报文'}, {name: '接收错误'}, {name: '发送报文'}, {name: '发送错误'}],
      intervaltX: [],
      ballPercent: 0,
      oldBallPercent: 0,
      ballText: '',
      ballXOffset: 0
    }
  },
  mounted () {
    this.defultPic()
    this.loadData()
    this.initData()
  },
  computed: {
    cert () {
      let cert = JSON.parse(JSON.stringify(this.$store.state.cert))
      cert.create_time = new Date(cert.create_time * 1000).toLocaleString()
      cert.start_time = new Date(cert.start_time * 1000).toLocaleString()
      cert.end_time = new Date(cert.end_time * 1000).toLocaleString()
      cert.lang = cert.lang === 'chinese' ? '简体中文' : 'English'
      return cert
    }
  },
  watch: {
    cpu (val, oldVal) {
      if (JSON.stringify(val) !== JSON.stringify(oldVal)) {
        this.cpuchart.changeData(val)
      }
    },
    momery (val, oldVal) {
      let p1 = String(val[0].value / val[1].value).slice(0, 6)
      let p2 = String(oldVal[0].value / oldVal[1].value).slice(0, 6)
      if (p1 !== p2) {
        this.momerychart.changeData(val) 
      }
    },
    disk (val, oldVal) {
      let p1 = String(val[0].value / val[1].value).slice(0, 6)
      let p2 = String(oldVal[0].value / oldVal[1].value).slice(0, 6)
      if (p1 !== p2) {
        this.diskchart.changeData(val) 
      }
    }
  },
  methods: {
    JumpTO (path) {
      this.$router.push(path)
    },
    handleNum (num) {
      return Number(Number(num / 1024 / 1024).toFixed(2)) ? Number(Number(num / 1024 / 1024).toFixed(2)) : 0
    },
    defultPic () {
      this.cpuchart = new G2.Chart({
        id: 'cpu',
        forceFit: true,
        height: 320      
      })
      this.diskchart = new G2.Chart({
        id: 'disk',
        forceFit: true,
        height: 320
      })
      this.momerychart = new G2.Chart({
        id: 'momery',
        forceFit: true,
        height: 320
      })
      
      this.initSplineChart()
      this.initDrawCircle()
      this.drawCircle(this.cpu, this.cpuchart, 'cpu')            
      this.drawCircle(this.disk, this.diskchart, 'disk')              
      this.drawCircle(this.momery, this.momerychart, 'momery')
    },
    loadData () {
      indexService.homeData()
        .then((res) => {
          if (res.errcode === 0) {    
            this.host_num = res['1']  
            this.run_time = this.formatTime(res['2'])
            // cpu,内存，硬盘
            this.computCricle(res['4'], res['7'], res['8'])
            // 流量概况数据
            this.flow_table = res['3']

            clearTimeout(this.interval)
            this.interval = setTimeout(() => {
              this.loadData()
            }, 2000)
          }
        })
        .fail(() => {
          clearTimeout(this.interval)
        })    
    },
    // 实时流量监控部分  
    initData () {
      indexService.senseData()
        .then((res) => {
          if (res.errcode === 0) {    
            let dataSense = res['6']
            let flowDataIn = res[6].map((item, index) => {
              let obj = {
                type: '滤前流量',
                value: Number(item.max_in_bps[0]),
                num: null,
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataOut = res[6].map((item, index) => {
              let obj = {
                type: '滤后流量',
                value: Number(item.max_in_bps_after_clean[0]),
                num: null,
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataTCP = res[6].map((item, index) => {
              let obj = {
                type: 'TCP连接',
                value: null,
                num: Number(item.tcp_conn[0]),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let flowDataUDP = res[6].map((item, index) => {
              let obj = {
                type: 'UDP连接',
                value: null,
                num: Number(item.udp_conn[0]),
                timestamp: item.timestamp * 1000
              }
              return obj
            })
            let newVal = [...flowDataIn, ...flowDataOut, ...flowDataTCP, ...flowDataUDP]

            this.flow = newVal
            this.splinechart.changeData(this.flow)

            clearTimeout(this.interval_f)
            this.interval_f = setTimeout(() => {
              this.initData()
            }, 30000)
          }
        })
        .fail(() => {
          clearTimeout(this.interval_f)
        })
    },
    computCricle (cpu, momery, disk) {
      this.cpu = this.memory = this.disk = null
      this.cpu = [     
        {name: '已用', value: Number(cpu.user)},
        {name: '空闲', value: Number(cpu.idle)}
      ]
      this.momery = [
        {name: '已用', value: Number(momery.used)},
        {name: '可用', value: Number(momery.total - momery.used)}
      ]
      this.disk = [
        {name: '已用', value: Number(disk.used)},
        {name: '可用', value: Number(disk.total - disk.used)}
      ]     
      this.legendFormat(this.cpu, this.cpuchart, '%')
      this.legendFormat(this.momery, this.momerychart, 'byte')
      this.legendFormat(this.disk, this.diskchart, 'byte')
    },
    formatTime (time) {
      time = Number(time)
      if (time < 60) {
        return `${time} 秒`
      }
      if (time >= 60 && time < 3600) {
        return `${Math.floor(time / 60)} 分  ${time % 60} 秒`
      }
      if (time >= 3600 && time < 24 * 3600) {
        return `${Math.floor(time / 3600)} 小时 ${Math.floor(time % 3600 / 60)} 分 ${Math.floor(time % 3600 % 60)} 秒`
      }
      if (time >= 24 * 3600) {
        return `${Math.floor(time / 24 / 3600)} 天 ${Math.floor(time % 86400 / 3600)} 小时 ${Math.floor(time % 86400 % 3600 / 60)} 分`
      }
    },
    // 流量单位转换
    covertFlow (bps) {
      let flow = ''
      let type = ''
      if (bps < 1024) {
        flow = Math.ceil(bps) 
        type = 'b'
      }
      if (bps >= 1024 && bps < 1024 * 1024) {
        flow = bps / 1024
        flow = flow.toFixed(2)
        type = 'K'
      }
      if (bps >= 1024 * 1024 && bps < 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024
        flow = flow.toFixed(2)
        type = 'M'
      }
      if (bps >= 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024 / 1024
        flow = flow.toFixed(2)
        type = 'G'
      }
      return {flow: flow, type: type}
    },
    // 初始化曲线图
    initSplineChart () {
      this.splinechart = new G2.Chart({
        id: 'flows',
        forceFit: true,
        height: 450,
        plotCfg: {
          margin: [ 50, 120, 80, 120 ]
        }
      })
      // x轴
      this.splinechart.axis('timestamp', {
        title: null // 不展示 timestamp 对应坐标轴的标题
      })
      this.splinechart.legend({
        title: null,
        position: 'bottom'
      })

      this.splinechart.filter('type', ['滤前流量', '滤后流量', 'TCP连接', 'UDP连接'])
      // y轴
      this.splinechart.axis('value', {
        // title: null,
        alias: '流量(bps)',
        formatter: (val) => {
          return this.$convertFlow(val, 'bps', 3)
        }
      })
      this.splinechart.axis('num', {
        // title: null,
        alias: '连接(个)',
        formatter: (val) => {
          return this.$convertNum(val, 3)
        }
      })
      // 显示辅助线
      this.splinechart.tooltip({
        crosshairs: {
          type: 'rect' || 'x' || 'y' || 'cross'
        } // 
      })     
      
      this.splinechart.off('tooltipchange').on('tooltipchange', (ev) => {
        for (let i = 0; i < ev.items.length; i++) {
          if (ev.items[i].name !== 'TCP连接' && ev.items[i].name !== 'UDP连接') {
            ev.items[i].value = this.$convertFlow(ev.items[i].value, 'bps', 3)
          }
          if (ev.items[i].name !== '滤前流量' && ev.items[i].name !== '滤后流量') {
            ev.items[i].value = this.$convertNum(ev.items[i].value, 3)
          }
        }
      })
     
      this.drawSpline(this.flow)
    },
    drawSpline (data) {   
      this.splinechart.clear()
      this.splinechart.source(data, {
        timestamp: {
          tickCount: 5, // 控制坐标轴刻度线个数
          type: 'time',
          mask: 'HH:MM:ss'
        },
        value: {
          alias: '流量(bps)',
          min: 0
        },
        num: {
          alias: '连接(个)',
          min: 0
        }
      })
      this.splinechart.line().position('timestamp*value').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
      this.splinechart.line().position('timestamp*num').shape('smooth').color('type', ['#e14d4d', '#2ca02c', '#ff7f0e', '#2c3e50', '#5d8ae6', '#d4d305']).size(2)
      this.splinechart.render()
    },
    initDrawCircle () {
      var Stat = G2.Stat
      let charts = [this.cpuchart, this.momerychart, this.diskchart]
      charts.forEach((chart) => {
        let radius = 0.8
        chart.coord('theta', {
          radius: radius 
        })

        chart.tooltip({
          title: null,
          map: {
            value: 'value%'
          }
        })
        
        chart.on('plotclick', (ev) => {
          this.$router.push('/control/sysstatus')
        })
      })
    },
    drawCircle (data, chart, ID) {
      var _this = this
      var Stat = G2.Stat
      chart.clear()
      chart.source(data)
      chart.intervalStack()
        .position(Stat.summary.percent('value'))
        .color('name')
        .label('name', {
          offset: 15,
          renderer: function (text, item, index) {
            var point = item.point // 每个弧度对应的点
            var percent = point['..percent'] // ..percent 字段由 Stat.summary.percent 统计函数生成
            percent = (percent * 100).toFixed(2) + '%'
            return text + ' ' + percent
          }
        })
      chart.render()
    },
    // 饼图图例格式转化
    legendFormat (data, chart, unit) {
      var _this = this
      chart.legend('name', {
        position: 'bottom',
        itemWrap: true,
        formatter: function (val) {
          for (var i = 0, len = data.length; i < len; i++) {
            var obj = data[i]
            if (obj.name === val) {
              if (unit === 'byte') {
                let val_unit = _this.covertFlow(obj.value)
                return val + ': ' + val_unit.flow + val_unit.type
              }
              return val + ': ' + obj.value + unit
            }
          }
        }
      })
    }
  },
  beforeDestroy () {
    clearTimeout(this.interval)
    clearTimeout(this.interval_f)
    this.cpuchart.clear()
    this.cpuchart.destroy()
    this.diskchart.clear()
    this.diskchart.destroy()
    this.momerychart.clear()
    this.momerychart.destroy()
    this.splinechart.clear()
    this.splinechart.destroy()
  }
}
</script>
<style lang="scss" scoped>
#ball{
  display: flex;
  align-items: center;
  justify-content: center;
}
.home-wrap{
  padding: 30px 5%;
  -webkit-box-sizing: border-box;
  box-sizing: border-box;
  height: 100%;
  overflow: auto;
}
.block-wrap{
  background: #FFFFFF;
  text-align: left;
  padding: 15px;
  box-shadow: 0 0 2px 3px rgba(0, 0, 0, 0.03); 
}  
.el-row {
  margin-top: 20px;
  &:first-child{
    margin-top: 0;
  } 
}
.el-col {
  margin-left: 20px;
  &:first-child{
    margin-left: 0;
  } 
}
.pic .el-col{
  margin-left: 0;
  padding: 10px;
} 
.title{
  display: flex;
  justify-content: space-between;
  .butn{
    margin-bottom: 10px;
  }
}
.phover{
 
  padding:15px;
  cursor: pointer;
  &:nth-of-type(n+2){
    margin-top: 10px;
  }
} 
#general table {
  width: 100% !important;
  margin-top: 35px;
  border-left: 1px solid #d2d2d2;
  border-top: 1px solid #d2d2d2;
tr {
  td {
    padding:10px 5px;
    text-align: center;
    border-bottom: 1px solid #d2d2d2;
    border-right: 1px solid #d2d2d2;
  }
}
}
#cpu,#momery,#disk{
  cursor: pointer;
  background: #f5f5f6;
} 
#flows{
  padding:0 30px;
  padding-top: 20px;
  box-sizing: border-box;
} 
.hostnum{
  text-align:center;
  margin-bottom: 10px;
  padding: 20px;
  p{
    font-size: 14px;
    margin: 10px;
  }
  .num{
    font-size: 35px;
  }
}
.red{
  color: #fa5555;
}
.green{
  color: #67c23a;
}
</style>


