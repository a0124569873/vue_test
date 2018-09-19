<template>
    <div class="status-item">
        <div :class="[status === 'on' ? 'status-item-top-on' : 'status-item-top-off','status-item-top']">
            <el-row style="padding: 5px;">
                <el-col :span="10">前端机IP: {{ip}} ( {{ status === 'on' ? '连接' : '断开' }} )</el-col>
                <el-col :span="8">前端机时间:&nbsp;&nbsp;{{time}}</el-col>
                <el-col :span="4">
                    <el-button type="primary" :class="[status === 'on' ? 'frontstatusitem-bt-on' : 'frontstatusitem-bt-off']" @click="tosysstatus(ip)">详情</el-button>
                </el-col>
                <el-col :span="2">
                    <i :class="[showitemcontent ? 'el-icon-arrow-up' : 'el-icon-arrow-down']" style="margin-top: 5px;" @click="switchcontent()"></i>
                </el-col>
            </el-row>
        </div>
        <el-collapse-transition>
            <div class="status-item-content" v-show="showitemcontent">
                <el-row class='status-item-content-row'>
                    <el-col :span="2" style="text-align: right;">
                        G设备&nbsp;&nbsp;&nbsp;&nbsp;
                    </el-col>
                    <el-col :span="10" class='status-item-content-row-gipcol'>
                        <div v-for="gitem in gsets" :key="ip + gitem.ip" style="margin: 2px 30px 2px 30px;">
                            <div v-if="gitem.status == 'on'">{{gitem.ip}}</div>
                            <div v-else style="color: red;">{{gitem.ip}}</div>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        &nbsp;
                    </el-col>
                    <el-col :span="6">
                        G设备个数:{{gsets.length}}个
                    </el-col>
                </el-row>
            </div>
        </el-collapse-transition>
    </div>
</template>

<script>
export default {
    props: [
        "ip",
        "time",
        "status",
        "gsets"
    ],
    data(){
        return {
            showitemcontent: true
        }
    },
    mounted(){
    },
    methods: {
        switchcontent(){
            this.showitemcontent = !this.showitemcontent
        },
        tosysstatus(ip){
            this.$router.push(`sys_status?ip=${ip}`)
        }
    }
}
</script>

<style lang='scss'>

.status-item {

    border-style: solid;
    border-width: 1px;
    border-color: #EEEEEE;
    background-color: #FDFDFD;
    width: 100%;
    margin: 5px;
}

.status-item-top {
    /* height: 50px; */
}

.status-item-top-on {
    background-color: #00C4C2;
}

.status-item-top-off {
    background-color: #EEEEEE;
}

.status-item-content {
    background-color: #FDFDFD;
    .status-item-content-row{
        display: flex; 
        align-items: center; 
        margin: 10px;
        .status-item-content-row-gipcol{
            display: flex; 
            flex-wrap: wrap; 
            width: 500px;
        }
    }
}

.frontstatusitem-bt-on{
    border-style: solid; 
    border-color: white; 
    border-width: 1px;
}

.frontstatusitem-bt-off{
    border-style: solid; 
    border-color: black; 
    background-color: #EEEEEE; 
    color: black; 
    border-width: 1px;
}



</style>


