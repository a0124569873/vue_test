<template>
  <div class="home-wrap">
    <el-row type="flex" class="row-bg" justify="space-between">
      <el-col class="block-wrap" :span="24">          
        <p class="phover">系统运行时间: <span class="color-num">{{run_time}}</span> </p>    
     </el-col>
    </el-row> 

    <el-row class="row-bg" justify="space-between">
      <el-col class="block-wrap" :span="24">    
        <el-row type="flex" class="pic row-bg" justify="space-between">
          <el-col :span="8">
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

    <el-row type="flex" class="row-bg" :element-loading-text="loading_text">
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content" style="border-top:none;padding:20px">
            <h4 style="float:left">链路信息</h4>           
            <el-table :data='linkInfo' border>
              <el-table-column type='index' label='序号' width="80" align="center"></el-table-column>
              <el-table-column label='线路ID' prop="uid" align="center"></el-table-column>
              <el-table-column label='前端机WAN口IP' prop="wan" align="center"></el-table-column>
              <el-table-column label='前端机LAN口IP' prop="lan" align="center"></el-table-column>
              <el-table-column label='G设备IP' prop="gip" align="center"></el-table-column>
              <el-table-column label='链路号' prop="linkNum" align="center"></el-table-column>
              <el-table-column label='二层封装格式' prop="dataType" align="center"></el-table-column>
            </el-table>
            <div class="page" style="margin-top:15px" v-if="total>0">
              <el-pagination  @size-change="pageSizeChange" @current-change="handleCurrentChange" :current-page="currentPage"
                  :page-sizes="[10, 20, 40, 80, 100]" :page-size="pageSize" layout="sizes, prev, pager, next " :total="total">
              </el-pagination>      
            </div>       
          </div>
      </el-col>
  </el-row>
  </div>
</template>
<script>
import indexService from 'services/indexService'
import LinkInfoService from 'services/linkInfoService'
const THRESHHOLD = 5000
export default {
  data () {
    return {
      run_time: 0,
      cpu: [
        {name: '内核空间', value: 0},     
        {name: '用户空间', value: 0},
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
      int: 0,
      cpuchart: null,
      diskchart: null,
      momerychart: null,
      timer: null,
      load: false,
      loading_text: '',  
      currentPage: 1,
      pageSize: 20,
      linkInfo: [],
      total: 0,
      linkInfoTimer: null
    }
  },

  created () { 
    this.loading_text = '正在努力加载中...'
  },

  mounted () {
    this.defultPic()
    this.loadData()   
    this.getLinkInfo()
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
    },
    flow (val, oldVal) {
      let compare = JSON.stringify(val) === JSON.stringify(oldVal)
      if (!compare) {
        this.splinechart.changeData(val)
      }
    }
  },
  methods: {
    JumpTO (path) {
      this.$router.push(path)
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
  
      // this.computBall(0)
      this.initDrawCircle()
      this.drawCircle(this.cpu, this.cpuchart, 'cpu')            
      this.drawCircle(this.disk, this.diskchart, 'disk')              
      this.drawCircle(this.momery, this.momerychart, 'momery')
    },
    loadData () {
      indexService.homeData()
        .then((res) => {
          if (res.errcode === 0) {
            let cpu = JSON.parse(res['1'])   
            let momery = JSON.parse(res['2'])
            let disk = JSON.parse(res['3'])
            this.computCricle(cpu, momery, disk)
            this.run_time = this.formatTime(res['5'])
            this.timer && clearTimeout(this.timer)
            this.timer = setTimeout(() => {
              this.loadData()
            }, 2000)
          }
        })
        .fail(() => {
          this.timer && clearTimeout(this.timer)
        })  
    },
    getLinkInfo () {
      LinkInfoService.getLinkInfo(this.currentPage, this.pageSize)
        .then(
          res => {
            if (res.errcode === 0) {
              if (res['4'] && res['4']['link_stat'] && res['4']['link_stat']['link_stat']) {
                this.linkInfo = res['4']['link_stat']['link_stat'].map((item) => {
                  let key = Object.keys(item)[0]
                  let values = item[key].split(',')
                  return {
                    uid: key,
                    wan: values[0],
                    lan: values[1],
                    gip: values[2],
                    linkNum: values[3],
                    dataType: values[4]
                  }
                })
                this.total = res['4']['link_stat']['count'] * 1
              }
              this.linkInfoTimer && clearTimeout(this.linkInfoTimer) // 先清除timer
              this.linkInfoTimer = setTimeout(() => {
                this.getLinkInfo()
              }, THRESHHOLD)
            }
          }
        ).always(() => {
          this.load = false
        })
    },
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      this.getLinkInfo()
    },
    handleCurrentChange (val) {
      this.currentPage = val
      this.getLinkInfo()
    },
    computCricle (cpu, momery, disk) {
      this.cpu = this.memory = this.disk = null
      this.cpu = [
        {name: '内核空间', value: cpu.system},     
        {name: '用户空间', value: cpu.user},
        {name: '空闲', value: cpu.idle}
      ]
      this.momery = [
        {name: '已用', value: momery.used},
        {name: '可用', value: momery.total - momery.used}
      ]
      this.disk = [
        {name: '已用', value: disk.used},
        {name: '可用', value: disk.total - disk.used}
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
    initDrawCircle () {
      var Stat = G2.Stat
      let charts = [this.cpuchart, this.momerychart, this.diskchart]
      charts.forEach((chart) => {
        let radius = chart === this.cpuchart ? 0.7 : 0.8
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
          this.$router.push('/sys_status')
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
    this.timer && clearTimeout(this.timer)
    this.linkInfoTimer && clearTimeout(this.linkInfoTimer)
    this.cpuchart.clear()
    this.cpuchart.destroy()
    this.diskchart.clear()
    this.diskchart.destroy()
    this.momerychart.clear()
    this.momerychart.destroy()
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
  padding: 20px;
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
.pic {
  margin: 0 -10px
}
.pic .el-col{
  margin-left: 0;
  padding: 0 10px;
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
#cpu,#momery,#disk,#flows{
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


