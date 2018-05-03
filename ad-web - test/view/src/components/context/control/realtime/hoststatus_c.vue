<template>
    <el-row :element-loading-text="loading_text">
        <el-col v-loading='load' element-loading-background="rgba(255, 255 ,225, 1)" :span="24" >
            <div class="content">
                <el-form :inline="true" style="text-align:left" ref="myFrom" :model="formData" :rules="rules">
                    <el-form-item label="排名显示个数：" prop="limit">
                        <el-input type="number" size="mini" v-model.number="formData.limit" auto-complete="off"></el-input>
                    </el-form-item>
                    <el-form-item>
                        <el-button type="primary" style="" @click="onSubmit">查 询</el-button>
                        <el-button type="primary" style="" @click="refresh">
                            <i v-if="!monitorStatus" class="icon-vd-play"></i>
                            <i v-else class="icon-vd-pause-20"></i>
                            <span>{{monitorStatus ? '暂停' : '监控'}}</span>
                        </el-button>
                    </el-form-item>
                </el-form>

                <el-table :data="rank" border :default-sort="{prop: 'in_bps', order: 'descending'}" @sort-change="sortType">
                    <el-table-column type='index' label="序号" width="80" align="center"></el-table-column>  
                    <el-table-column label="IP" align="center">
                        <template slot-scope="scop">
                            {{scop.row.ip}}
                        </template>
                    </el-table-column>
                    <el-table-column prop="in_bps" label="输入流量" align="center" sortable="custom"></el-table-column>
                    <el-table-column prop="in_bps_after_clean" label="滤后输入流量" align="center" sortable="custom"></el-table-column>
                    <el-table-column prop="in_pps" label="输入包速率" align="center" sortable="custom"></el-table-column>
                    <el-table-column prop="in_pps_after_clean" label="滤后输入包速率" align="center" sortable="custom"></el-table-column>
                    <el-table-column prop="tcp_conn" label="tcp连接" align="center" sortable="custom"></el-table-column>
                    <el-table-column prop="udp_conn" label="udp连接" align="center" sortable="custom"></el-table-column>

                </el-table> 
            </div>
        </el-col>
    </el-row>
</template>
<script>

import currentService from 'services/currentService_c.js'

const THRESHOLD = 2000

let checkLimit = function (rule, value, callback) {
    if (!(Number.isInteger(value) && Number(value) > 0)) {
        return callback(new Error('排名必须为正整数'))
    }
    callback()
}

export default {
    data(){
        return {
            load: false,
            loading_text: '',
            orderBy: 'in_bps',
            order: 'descending',
            limit: 10,
            rank: [],
            monitorStatus: false,
            params: {},
            formData: {
                limit: 10
            },
            rules: {
                limit: [
                    {validator: checkLimit, trigger: 'blur'}
                ]
            },
            timer: null
        }
    },
    created(){
        this.loading_text = '正在努力加载中'
        let isDesc = false
        this.order === 'ascending' ? isDesc = false : isDesc = true
        this._loadData(this.orderBy, this.limit, isDesc)
    },
    methods: {
        _loadData(orderby, limit, desc){
            currentService.getHostStatus(orderby, limit, desc)
                .then(
                    res => {
                        if (res.errcode === 0) {
                            this.rank = res['18'].map((item) => {
                                item.in_bps = this.$formatBps(item.in_bps)
                                item.in_bps_after_clean = this.$formatBps(item.in_bps_after_clean)
                                return item
                            })
                            if (this.monitorStatus) {
                                this.$nextTick(() => {
                                    this.timer && clearTimeout(this.timer)
                                    this.timer = setTimeout(() => {
                                        this._loadData(orderby, limit, desc)
                                    }, THRESHOLD);
                                })
                            }
                        }
                    }

                ).always(
                    () => {
                        this.load = false
                    }
                )
        },
        sortType(obj){
            if (!(Number.isInteger(this.formData.limit) && Number(this.formData.limit) >= 0)) {
                return false
            }
            if (obj.prop !== null){
                this.orderBy = obj.prop
            }
            this.order = obj.order
            this.timer && clearTimeout(this.timer)
            let isDesc = false
            this.order === 'ascending' ? isDesc = false : isDesc = true
            this._loadData(this.orderBy, this.formData.limit, isDesc)
        },
        handleIPClick(){
            this.$router.push({path: '/control/realtime/' + ip.replace(/\./g, '*')})
        },
        refresh () {
            this.monitorStatus = !this.monitorStatus
            this.timer && clearTimeout(this.timer)
            if (this.monitorStatus) {
                let isDesc = false
                this.order === 'ascending' ? isDesc = false : isDesc = true
                this._loadData(this.orderBy, this.formData.limit, isDesc)
            }
        },
        onSubmit () {
            // console.log()
            this.$refs["myFrom"].validate((valid) => {
                if(valid){
                    this.timer && clearTimeout(this.timer)
                    let isDesc = false
                    this.order === 'ascending' ? isDesc = false : isDesc = true
                    this._loadData(this.orderBy, this.formData.limit, isDesc)
                }
            })
        }


    },
    beforeDestroy () {
        this.timer && clearTimeout(this.timer)
    }

}
</script>
<style>

</style>

