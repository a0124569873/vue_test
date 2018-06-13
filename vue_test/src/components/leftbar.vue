<template>
    <div id="leftbar">
        <el-row class="tac" >
            <el-col :span="3">
                <el-menu
                router
                :collapse="collapsed"
                default-active="1"
                background-color="#E74C3C"
                active-text-color="#FFA595">
                    <el-menu-item index="/leftbar/charts" >first_chart</el-menu-item>
                    <el-menu-item index="/leftbar/dialog" >second_dialog</el-menu-item>
                    <el-menu-item index="/leftbar/g2chart" >third_g2chart</el-menu-item>
                    <el-menu-item index="/leftbar/bootstrap" >four_bootstrap</el-menu-item>
                    <el-menu-item index="/leftbar/elui" >five_elementui</el-menu-item>
                    <el-menu-item index="/leftbar/heighchart" >six_heighchart</el-menu-item>
                    <el-menu-item index="/leftbar/father" >seven_组件</el-menu-item>
                    <el-menu-item index="/leftbar/testcss" >eight_css</el-menu-item>
                    <el-menu-item index="/leftbar/promise" >nine_promise</el-menu-item>
                    <el-menu-item index="/leftbar/gstatus" >nine_gstatus</el-menu-item>
                </el-menu>
                <a class="toggle"
                    @click="expand">
                    <i class="el-icon-arrow-left"></i>
                </a>
            </el-col>
            <el-col :span="21">

            <div class='leftbar-title'>
                ROUTER_NAME: <a id="ta">{{texxtt}}</a>
            </div>

            <div class='leftbar-content'>
                <router-view ></router-view>
            </div>
            

            </el-col>
        </el-row>

    </div>
</template>

<script>
export default {
    name:'leftbar_components',
    data(){
        return {
            collapsed: false,

        }
    },
    computed:{
        texxtt:function(){
            let path = this.$route.path

            console.log(path.split("/")[2])

            let table_name = path.split("/")[2]

            return path

            // switch (table_name) {
            //     case "charts":
            //         return "Echart"
            //         break;
            //     case "dialog":
            //         return "dialog"
            //         break;
            //     case "g2chart":
            //         return "g2chart"
            //         break;
            //     case "bootstrap":
            //         return "bootstrap"
            //         break;
            //     case "elui":
            //         return "elui"
            //         break;
            //     case "heighchart":
            //         return "heighchart"
            //         break;
            //     case "father":
            //         return "father"
            //         break;
            //     case "testcss":
            //         return "testcss"
            //         break;
            //     case "promise":
            //         return "promise"
            //         break;
            
            //     default:
            //         break;
            // }


        }
    },
  methods:{
      expand(){
          this.collapsed = !this.collapsed
      }
  }
}
</script>

<style>

#ta {
    color: chartreuse;
    font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
    font-size: 100%;
    border: blue;
}


/* .wrap {
  
  width: 100%;
  height: 100%;
  padding: 50px;
  box-sizing: border-box;
  background-color: rgb(243, 141, 9);
  -moz-box-align: center;
} 
*/
</style>

