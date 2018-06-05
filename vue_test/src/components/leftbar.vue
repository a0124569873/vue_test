<template>
    <div id="leftbar">
        <el-row class="tac" >
            <el-col :span="3">
                <el-menu
                default-active="1"
                background-color="#E74C3C"
                active-text-color="#FFA595">
                    <el-menu-item index="1" @click="toc()">first_chart</el-menu-item>
                    <el-menu-item index="2" @click="tod()">second_dialog</el-menu-item>
                    <el-menu-item index="3" @click="toe()">third_g2chart</el-menu-item>
                    <el-menu-item index="4" @click="tof()">four_bootstrap</el-menu-item>
                    <el-menu-item index="5" @click="tog()">five_elementui</el-menu-item>
                    <el-menu-item index="6" @click="toh()">six_heighchart</el-menu-item>
                    <el-menu-item index="7" @click="toi()">seven_组件</el-menu-item>
                    <el-menu-item index="8" @click="toj()">eight_css</el-menu-item>
                    <el-menu-item index="9" @click="tok()">nine_promise</el-menu-item>
                    <el-menu-item index="10" @click="tol()">nine_html</el-menu-item>
                </el-menu>
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
      toc(){
          this.jumpTo('/leftbar/charts')
      },
      tod(){
          this.jumpTo('/leftbar/dialog')
      },
      toe(){
          this.jumpTo('/leftbar/g2chart');
      },
      tof(){
          this.jumpTo('/leftbar/bootstrap');
      },
      tog(){
          this.jumpTo('/leftbar/elui');
      },
      toh(){
          this.jumpTo('/leftbar/heighchart');
      },
      toi(){
          this.jumpTo('/leftbar/father');
      },
      toj(){
          this.jumpTo('/leftbar/testcss');
      },
      tok(){
          this.jumpTo('/leftbar/promise');
      },
      tol(){
          this.jumpTo('/leftbar/gstatus');
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

