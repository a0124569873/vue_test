<template>
  <div class="port-wrap">
    <transition name='fade'>
      <div class="body" v-show='show'>  
        <netchart :eths="eths" :length='length' style="width:50%;margin-right:10px"></netchart>
        <nettable :eths="eths" style="width:50%;margin-left:10px"></nettable>
     </div>
    </transition> 
  </div>
</template>
<script>
import netchart from './net/netchart'
import nettable from './net/nettable'
import currentService from 'services/currentService'

export default {
  components: {
    netchart,
    nettable
  },
  data () {
    return {
      eths: [],
      intervalid: null,
      length: 0,
      show: false
    }
  },
  mounted () {
    this.loadData()
  },
  methods: {
    // loadData () {
    //   currentService.getNetStatus()
    //     .then((res) => {
    //       this.intervaltX = []
    //       for (let [k, v] of Object.entries(res['17'])) {
    //         this.intervaltData[0][k] = Number(v.in.packets)
    //         this.intervaltData[1][k] = Number(v.in.errors)
    //         this.intervaltData[2][k] = Number(v.out.packets)
    //         this.intervaltData[3][k] = Number(v.out.errors)
    //         this.intervaltX.push(k)
    //       }
    //       this.drawInterval(this.intervalchart, this.intervaltData)
    //       clearTimeout(this.interval)
    //       this.interval = setTimeout(() => {
    //         this.loadData()
    //       }, 2000)
    //     })
    // },
    loadData () {
      currentService.getNetStatus()
        .then((res) => {
          if (res.errcode === 0) {
            this.show = true
            this.length = Object.keys(res['17']).length
            let data = res['17']
            let tmpArr = []
            for (let key in data) {
              let value = data[key]
              let item = {
                name: value.name,
                inpkts: value.in.packets,
                outpkts: value.out.packets,
                inbytes: this.convertFlow(value.in.bytes),
                outbytes: this.convertFlow(value.out.bytes),
                in_bps: value.in.Bps,
                out_bps: value.out.Bps,
                in_err: value.in.errors,
                out_err: value.out.errors,
                in_drop: value.in.dropped,
                out_drop: value.out.dropped,
                status: Number(value.up)
              }
              tmpArr.push(item)
            }
            this.eths = tmpArr
            // this.eths = res['17'].map((item) => {
            //   return {
            //     name: item.desc,
            //     inpkts: item.in_packets,
            //     outpkts: item.out_packets,
            //     inbytes: this.convertFlow(item.in_bytes),
            //     outbytes: this.convertFlow(item.out_bytes),
            //     in_bps: item.in_Bps,
            //     out_bps: item.out_Bps,
            //     in_err: item.in_errors,
            //     out_err: item.out_errors,
            //     in_drop: item.in_dropped,
            //     out_drop: item.out_dropped,
            //     status: Number(item.up)
            //   }
            // })
          }
          clearTimeout(this.intervalid)
          this.intervalid = setTimeout(() => this.loadData(), 2000)
        }, () => {
          clearTimeout(this.intervalid)
          this.intervalid = setTimeout(() => this.loadData(), 2000)
        })
    },
    convertFlow (bps) {
      let flow = ''
      if (bps < 1024) {
        flow = Math.ceil(bps) + ' B'
      }
      if (bps >= 1024 && bps < 1024 * 1024) {
        flow = bps / 1024
        flow = flow.toFixed(2) + ' KB'
      }
      if (bps >= 1024 * 1024 && bps < 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024
        flow = flow.toFixed(2) + ' MB'
      }
      if (bps >= 1024 * 1024 * 1024) {
        flow = bps / 1024 / 1024 / 1024
        flow = flow.toFixed(2) + ' GB'
      }
      return flow
    }
  },
  beforeDestroy () {
    clearTimeout(this.intervalid)
  }
}
</script>

