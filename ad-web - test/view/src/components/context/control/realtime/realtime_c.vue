<template>
  <div id="flow">

  </div>
</template>
<script>

import currentService from 'services/currentService'

let chart = null

export default {

    data(){
        return {

        }
    },

    mounted(){
        this.loadSpline()
        this.getFlow(2, '')
    },
    methods:{
        getFlow (index, ip, status = false) {
        currentService.getFlow(index, ip)
            .then((res) => {
            if (res.errcode === 0) {
                this.ip = this.searchIp
                this.controlLength = res[1].length
                console.log(res)
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
                console.log(newVal)
                this.flowData = newVal
                this.refreshFlowData(status)
            }
            })
            .fail((res) => {
            this.serverStatus = false
            clearTimeout(this.timer)
            })
        },
        loadSpline(){
            let _this = this
            chart = new G2.Chart({
                id: 'flow',
                forceFit: true,
                heigth: 450,
                animate: false,
                plotCfg: {
                    margin: [ 50, 120, 80, 120 ]
                }
            })
            chart.axis('timestamp', {
                title: null
            })
            chart.legend({
                title: null,
                position: 'bottom'
            })
            chart.tooltip({
                crosshairs: {
                    type: 'rect' || 'x' || 'cross'
                }
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
        }
    }


  
}
</script>

