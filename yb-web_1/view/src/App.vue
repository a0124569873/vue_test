<template>
  <div id="app" :class="theme">
    <router-view></router-view>
  </div>
</template>

<script>
export default {
  name: 'app',
  // mounted(){
    // alert(this.theme)
  // },
  computed: {
    login: function () {
      return this.$store.state.login
    }
  },
  watch: {
    login: function (val, old) {
      if (window.sessionStorage.getItem('login') !== 'true') {
        this.$router.push('/login')
      } else {
        if (document.location.href.indexOf('login') !== -1) {
          this.$router.push('/')
        }
      }
    }
  }
}
</script>
<style>
body,html{
  padding: 0;
  margin: 0;
  height: 100%;
  width: 100%;
}
#app {
  width: 100%;
  height: 100%;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /* text-align: center; */
  color: #2c3e50;
  padding: 0;
  margin: 0;
}
</style>
