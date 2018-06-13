<template>
    <div class="bgcolor">           
        <h3>实时包个数趋势图</h3>                    
        <div id="pk" class="pic"></div>   
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
      let flowIn = Number(this.picData.in_pkt) 
      let flowOut = Number(this.picData.out_pkt) 
      if (this.pkdata.length > 10) {
        this.pkdata.shift()
        this.pkdata.shift()
      }
      this.pkdata.push({time: time, yaxis: flowIn, type: 'in_pkt'})
      this.pkdata.push({time: time, yaxis: flowOut, type: 'out_pkt'})
      this.pkchart.changeData(this.pkdata)
    },
    actIp () {
      this.reset()
      this.pkchart.changeData(this.pkdata)
    }
  },
  data () {
    return {
      pkdata: [],
      pkchart: {}
    }
  },
  methods: {
    reset () {
      this.pkdata = []
      let now = (new Date()).getTime() - 1000 * 70
      for (let i = 0; i <= 10; i++) {
        let time = now + 1000 * 7 * i
        let inPkt = {time: time, yaxis: 0, type: 'in_pkt'}
        let outPkt = {time: time, yaxis: 0, type: 'out_pkt'}
        this.pkdata.push(inPkt)
        this.pkdata.push(outPkt)
      }
    },
    loadSpline () {
      this.pkchart = new G2.Chart({
        id: 'pk',
        forceFit: true,
        height: 400,
        animate: true
      })
      this.pkchart.axis('time', {
        title: null
      })
      this.pkchart.axis('yaxis', {
        title: null,
        formatter: function (val) {
          return val + '(个/秒)'
        }
      })
      this.pkchart.source(this.pkdata, {
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
      this.pkchart.line().position('time*yaxis').color('type', ['#8caade', '#8cdfc5']).shape('line').size(2)
      this.pkchart.render()
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
