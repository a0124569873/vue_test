<template>
    <div class="status-item">
        <div :class="[statusitemtop,'status-item-top']">
            <el-row style="padding: 5px;">
                <el-col :span="10">前端机IP: {{ip}} ( {{ status === 'on' ? '连接' : '断开' }} )</el-col>
                <el-col :span="8">前端机时间:&nbsp;&nbsp;{{time}}</el-col>
                <el-col :span="4">
                    <el-button type="primary" style="border-style: solid; border-width: 1px; border-color: white;" v-if="status === 'on'" @click="tosysstatus(ip)">详情</el-button>
                    <el-button type="primary" style="border-style: solid; border-width: 1px; border-color: white; background-color: #EEEEEE; color: black; " v-else @click="tosysstatus(ip)">详情</el-button>
                </el-col>
                <el-col :span="2">
                        <i :class="[showitemcontent ? 'el-icon-arrow-up' : 'el-icon-arrow-down']" style="margin-top: 3px;" @click="switchcontent()"></i>
                </el-col>
            </el-row>
        </div>
        <el-collapse-transition>
            <div class="status-item-content" v-show="showitemcontent">
                <el-row style="display: flex; align-items: center; margin: 10px;">
                    <el-col :span="2" style="text-align: right;">
                        G设备&nbsp;&nbsp;&nbsp;&nbsp;
                    </el-col>
                    <el-col :span="10" style="display: flex; flex-wrap: wrap; width: 500px;">
                        <div v-for="gitem in gsets" :key="ip + gitem.ip" style="margin: 2px 30px 2px 30px;">
                            <div v-if="gitem.status == 'on'">{{gitem.ip}}</div>
                            <div v-else style="color: red;">{{gitem.ip}}</div>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        &nbsp;
                    </el-col>
                    <el-col :span="6" style="">
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
            statusitemtop:'status-item-top-on',
            showitemcontent: true
        }
    },
    mounted(){
        this.statusitemtop = (this.status == 'on' ? 'status-item-top-on' : 'status-item-top-off')
    },
    methods: {
        switchcontent(){
            this.showitemcontent = !this.showitemcontent
        },
        tosysstatus(ip){
            this.$router.push(`testcss?ip=${ip}`)
        }
    }
}
</script>

<style >
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
    
}


</style>


