<template>
  <router-view id="app"></router-view>
</template>

<script>
import serverService from 'services/serverService'

export default {
  name: 'app',
  computed: {
    login: function () {
      return this.$store.state.login.is_login
    }
  },
  watch: {
    login: function () {
      if (!this.login) {
        this.$router.push('/login')
      } else {
        this.$router.push('/')
      }
    }
  },
  beforeCreate() {
    serverService.lines().then(res => {
      if(res.errcode !== 0) return
      this.$store.commit('INIT_LINES', res.list)
    })
  }
}
</script>
