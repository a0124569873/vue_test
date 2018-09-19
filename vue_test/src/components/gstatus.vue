<template>
  <div class="content">

    <statusitem 
      v-for = "itemdata in currenttopodata"
      :ip = "itemdata.ip"
      :time = "itemdata.time"
      :status = "itemdata.status"
      :gsets = "itemdata.g_sets"
      :key= "itemdata.ip"
      >
    </statusitem>
		<div class="gstatus-page" style="text-align: center">
			<el-pagination
				@size-change="handleSizeChange"
				@current-change="handleCurrentChange"
				:current-page="currentPage"
				:page-sizes="[5, 10, 20, 50]"
				:page-size="pagesize"
				layout="total, sizes, prev, pager, next, jumper"
				:total="totalitem">
			</el-pagination>
		</div>	
    

  </div>
</template>
<script>

import statusitem from './gstatusitem'

import getXhrData from '../services/getXhrData'

import {formatDate} from './img/date.js';

export default {
  components: {
    statusitem
  },
  data () {
    return {
			topodata: [],
			topodataarr: [],
			currenttopodata: [],
			currentPage: 1,
			totalitem: 10,
			pagesize: 5,
			liveTime: 300000
    }
  },
  created () {
		this._gettopodata()
  },
  methods: {
		handleSizeChange(val) {
			this.pagesize = val
			this.pagemodify()

		},
		handleCurrentChange(val) {
			this.currentPage = val
			this.pagemodify()
		},
		pagemodify(){
			this.currenttopodata = this.topodataarr.slice((this.currentPage - 1 ) * this.pagesize, this.currentPage * this.pagesize)
		},
		_gettopodata(){
			getXhrData.gettopoData().then((res) => {
				if(res.errcode === 0){
					$.map(res['6'], (value, key) => {

						let gsets = []
						$.map(value, (gvalue, gkey) => {
							gsets[gvalue] = {'status': 'off', 'ip': gvalue}
						})
						this.topodata[key] = {'ip': key, 'time': 0, 'status': 'off', 'g_sets': gsets }
					})
					this._setGstatus()
				}
			}).always(
			)
		},
		_setGstatus(){
				getXhrData.getGstatus().then((res) => {
					if (res.errcode === 0) {
						let data = res['6']
						let serverTime = res['6']['server_time'] || parseInt((+new Date()))
						
						if (data) {
							for (let frontip in data) {
								if (frontip !== 'server_time') {
									let fip = data[frontip]
									let fstatus = Math.abs(fip.timestamp - serverTime) > this.liveTime ? 'off' : 'on'
									this.topodata[frontip]['status'] = fstatus
									this.topodata[frontip]['time'] = formatDate(new Date(fip.timestamp), 'yyyy-MM-dd hh : mm');
									for (let gip in fip) {
										if (gip !== 'timestamp') {
											this.topodata[frontip]["g_sets"][gip]['status'] = (fstatus === 'off' ? 'off' : (fip[gip]['status'] === 'down' ? 'off' : Math.abs(fip[gip].timestamp - fip.timestamp) > this.liveTime ? 'off' : 'on'))
										}
									}
								}
							}
						}
						this.topodataarr = []
						for (let fip in this.topodata) {
							let g_sets = []
							let g_sets_arr = this.topodata[fip]['g_sets']
							for (let gip in g_sets_arr) {
								g_sets.push(g_sets_arr[gip])
							}
							this.topodata[fip]['g_sets'] = g_sets
							this.topodataarr.push(this.topodata[fip])
						}

						this.totalitem = this.topodataarr.length
						this.pagemodify()
						console.log(res)
					}
				}).always(

				)
		}
  }
}
</script>
<style>
.content{
  display: flex;
  flex-direction: column;
  justify-content: center;
}
</style>

