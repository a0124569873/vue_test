<template>
<el-row class="tac">
  <el-col :span="5" class="meunitem">
    <h5>默认颜色</h5>
    <el-menu
      default-active="2"
      class="el-menu-vertical-demo meunitem"

      active-text-color="red"
      background-color="yellow"
      @open="handleOpen"
      @close="handleClose">
      <el-submenu index="1">
        <template slot="title">
          <!-- <i class="el-icon-location"></i> -->
          <span>系统设置</span>
        </template>
        <el-menu-item-group>
          <template slot="title">网口设置</template>
          <el-menu-item index="1-1">网口1设置</el-menu-item>
          <el-menu-item index="1-2">网口2设置</el-menu-item>
        </el-menu-item-group>
        <el-menu-item-group title="管理员设置">
          <el-menu-item index="1-3">密码设置</el-menu-item>
        </el-menu-item-group>
        <el-submenu index="1-4">
          <template slot="title">添加用户</template>
          <el-menu-item index="1-4-1" @click="ggg">用户列表</el-menu-item>
        </el-submenu>
      </el-submenu>
      <el-submenu index="10">
        <template slot="title">
          <span>用户设置</span>
        </template>
        <el-menu-item index="2" @click="aaaa">
          添加IP
        </el-menu-item>
      </el-submenu>
      
      <el-menu-item index="3" disabled>
        <!-- <i class="el-icon-document"></i> -->
        <span slot="title">日志查看</span>
      </el-menu-item>
      <el-menu-item index="4">
        <!-- <i class="el-icon-setting"></i> -->
        <span slot="title">系统更新</span>
      </el-menu-item>
    </el-menu>
  </el-col>
  请选择：
  <el-select v-model="ddd" filterable placeholder="请选择">
    <el-option
     v-for="item in fff"
     :key="item.aaa"
     :label="item.bbb"
     :value="item.aaa"
     ></el-option>
  </el-select>
  {{ddd}}
  <router-view></router-view>
</el-row>
</template>

<script>
  export default {
    data(){
      return {
        ddd:"",
        fff:[
          {aaa:1,bbb:11},
          {aaa:5,bbb:112321},
          {aaa:6,bbb:12132131},
          {aaa:7,bbb:1123211},
          {aaa:9,bbb:1312311},
          {aaa:2,bbb:21232132},
          {aaa:3,bbb:33},
          {aaa:4,bbb:44}
        ]
      }
    },
    mounted(){

      this.$store.commit('add_zzz')
      // alert(this.$store.state.zzz)
    },
    methods: {
      handleOpen(key, keyPath) {
        console.log(key, keyPath);
      },
      handleClose(key, keyPath) {
        console.log(key, keyPath);
      },
      ggg(){
          this.$router.push("vvv")
      },
      aaaa(){
        this.$router.push("login")
      }
    }
  }
</script>

<style>
.el-menu-item-group__title {
    color: #59f76e;
}
</style>
