<template>
    <div class="vd-head">
      <ul class="logo">
        <li> <img src="../img/logo.png"></li>
        <span class="line"></span>               
        <li><h3>HDC-320</h3></li>    
        <span class="line"></span>               
        <li v-if='$store.state.cert.status === "valid"' class="hover-able" @click="JumpTO('/')"><span>设备概况</span></li>     
        <span class="line"></span>               
        <li v-if='$store.state.cert.status === "valid"' class="hover-able" @click="JumpTO('/control/netaddress')"><span>管理控制台</span></li>     
      </ul>
      <ul class="right—list">
        <li class="hover-able">
          <el-dropdown @command="handleCommand">
            <span class="el-dropdown-link"> {{__ID__.name ? __ID__.name : 'admin'}}</span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="userinfo" :disabled="$store.state.cert.status !== 'valid'">账户管理</el-dropdown-item>
              <el-dropdown-item command="exit" divided>退出</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </li>
        <span class="line"></span>  
        <li class="hover-able">
          <el-dropdown @command="changeTheme">
            <span class="el-dropdown-link">主题切换</span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="theme-red">红色主题</el-dropdown-item>
              <el-dropdown-item command="theme-blue" divided>蓝色主题</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </li>
        <span class="line"></span>  
        <li class="hover-able">
          <el-dropdown @command="changeLang">
            <span class="el-dropdown-link">切换语言</span>
            <el-dropdown-menu slot="dropdown">
              <el-dropdown-item command="ch">简体中文</el-dropdown-item>
              <el-dropdown-item command="en" divided>English</el-dropdown-item>
            </el-dropdown-menu>
          </el-dropdown>
        </li>
      </ul>    
    </div>
</template>
<script>

import indexService from 'services/indexService'
export default {
  data () {
    return {
      theme: 'red',
      lang: 'ch'
    }
  },
  methods: {
    JumpTO (path) {
      this.$router.push(path)
    },
    handleCommand (command) {
      if (command === 'userinfo') {
        this.$router.push('/control/userinfo')
      } else if (command === 'exit') {
        this.logout()
      }
    },
    logout () {
      indexService.logout().then((res) => {
        if (res.errcode === 0) {
          this.$store.state.login = false
          window.sessionStorage.setItem('login', false)
          // this.$store.commit('EDIT_NOTE', false)
          this.$router.push('/login')
          // window.location.reload()
        }
      })
    },
    changeTheme (theme) {
      this.theme = theme
      $('#app').attr('class', theme)
      window.localStorage.setItem('theme', theme)
    },
    changeLang (lang) {
      this.lang = lang
    }
  }
}
</script>
