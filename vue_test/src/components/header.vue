<template>
  <div class="hello">
    <!-- <el-button @click="toZxn('/aaa')">jump</el-button>
    <el-button @click="toZxn('/dd')">dialog_form</el-button>
    <el-button @click="toZxn('/charts')">Echarts</el-button> -->



    <el-menu :default-active="activeIndex" class="el-menu-demo" style="background-color:#AE0918;" mode="horizontal" @select="handleSelect">
      <el-menu-item index="1">首页</el-menu-item>
      <el-menu-item index="2">后台页</el-menu-item>
      <el-menu-item index="3">leftbar</el-menu-item>
      <el-menu-item index="4">test</el-menu-item>

    </el-menu>

    <el-button @click="sssss">退出登录</el-button>
    <el-checkbox label="dfsadasdasdas"></el-checkbox>

    <!-- <router-view></router-view> -->


  </div>
</template>

<script>

// import chartService from '../services/getXhrData'

export default {
  name: 'header_components',

  props:{
    zzxn:{
      type:String
    }
  },

  data () {
    return {
      msg: 'Welcome to Your Vue.js App',
      activeIndex: '1'

    }
  },
  mounted(){
    // chartService.getChartData().then(
    //   (res) => {
    //     console.log(res.data);
    //   }
    // )

    console.log(this.zzxn)
    
  },
  methods:{
    sssss(){
      // console.error("adsfsdfsdf")
      window.localStorage.setItem('login',false)
      this.$router.push("/login")
      console.log("1111111111111111111111111111")
    },
    toZxn(pathh){
      // this.$router.push(pathh);
      this.jumpTo(pathh);
    },
    handleSelect(key,path){
      
      switch (key) {
        case "1":
        console.log(key);
          this.jumpTo("/zxn")
          break;
        case "2":
        console.log(key);
          this.jumpTo("/menuitem")
          break;
        case "3":
        console.log(key);
          this.jumpTo("/leftbar")
          break;
    
        default:
          break;
      }
    }
  }

}
</script>

<style>
/* .wrap {
  background-color: rgb(243, 141, 9);
  width: 100%;
  height: 100%;
  box-sizing: border-box;
} */
</style>

