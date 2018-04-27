<template>
    <div class="bgcolor">           
        <h3>实时流量趋势图</h3>                    
        <div id="folw" class="pic"></div>   
    </div>
</template>
<script>
export default {
  props: {
    picData: {
      type: Object,
      required: true
    },
    actIp: {
      type: String,
      required: true
    }
  },
  mounted () {
    this.reset()
    this.loadSpline()
  },
  watch: {
    picData () {
      let time = (new Date()).getTime()
      let flowIn = Number(this.picData.flow_in) 
      let flowOut = Number(this.picData.flow_out) 
      if (this.flowdata.length > 10) {
        this.flowdata.shift()
        this.flowdata.shift()
      }
      this.flowdata.push({time: time, yaxis: flowIn, type: 'flow_in'})
      this.flowdata.push({time: time, yaxis: flowOut, type: 'flow_out'})
      this.flowchart.changeData(this.flowdata)
    },
    actIp () {
      this.reset()
      this.flowchart.changeData(this.flowdata)
    }
  },
  data () {
    return {
      flowdata: [],
      flowchart: {}
    }
  },
  methods: {
    reset () {
      this.flowdata = []
      let now = (new Date()).getTime() - 1000 * 70
      for (let i = 0; i <= 10; i++) {
        let time = now + 1000 * 7 * i
        let inPkt = {time: time, yaxis: 0, type: 'flow_in'}
        let outPkt = {time: time, yaxis: 0, type: 'flow_out'}
        this.flowdata.push(inPkt)
        this.flowdata.push(outPkt)
      }
    },
    loadSpline () {
      this.flowchart = new G2.Chart({
        id: 'folw',
        forceFit: true,
        height: 400,
        animate: true
      })
      this.flowchart.axis('time', {
        title: null
      })
      this.flowchart.axis('yaxis', {
        title: null,
        formatter: function (val) {
          return val + 'b/s'
        }
      })
      this.flowchart.source(this.flowdata, {
        time: {
          alias: null,
          type: 'time',
          mask: 'HH:MM:ss',
          tickCount: 10,
          nice: true
        },
        yaxis: {
          alias: null,
          min: 0
        },
        type: {
          type: 'cat',
          range: [0, 1]
        }
      })
      this.flowchart.line().position('time*yaxis').color('type', ['#ff7f0e', '#2ca02c']).shape('smooth').size(2)
      this.flowchart.render()
    } 
  }
}
</script>
<style scoped>
    .bgcolor{
        margin: 0 20px;
    }
    h3{
        font-size: 14px;
        margin-bottom: 20px;
    }
</style>
