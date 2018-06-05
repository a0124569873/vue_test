<template>
<div id="list-demo" class="demo">
    <button @click="show = !show">switch</button>
    <transition name="zxn">
        <p v-if="show">ZXN</p>
    </transition>
    <button @click="testdd">aaaaa</button>

    <el-button @click="--mid">sss</el-button>
    <a @click="mid--">丨</a>
    <i class="el-icon-caret-left" @click="mid--"></i>
        {{mid}}:小时
    <i class="el-icon-caret-right" @click="mid++"></i>
    <buju></buju>

    <!-- <div style="position:absolute;top: 50%; margin: -50px 0 0 0;" >
        dsasdasdasd
    </div> -->

    fdghdfgdfg
    <div style="position:absolute;top: 50%; margin: -50px 0 0 0;" >
        dsasdasdasd
    </div>

</div>

</template>

<script>

import buju from '@/components/fourbuju'

//垂直居中



export default {
    components:{
        buju
    },
    mounted(){
        console.log(this.$route.query.ip)
    },
    data(){
        return{
            show:true,
            mid:10

        }
    },
    methods:{
        testdd(){
            // Velocity($('.myDrag'), {width: drSwidth, height: drSheight, left: drSleft, top: drStop}, 500)
            console.log(this.$route)
        },
        dec(){
            this.mid--
            console.log(this.mid)
        }
        
    }
}
</script>

<style>
.zxn-enter-active, .zxn-leave-active {
    transition: opacity .1s;
}
.zxn-enter, .zxn-leave-to {
    opacity: 0;
}

</style>

