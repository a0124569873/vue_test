<template>
    <div class="wrap">
        <headerbar></headerbar>      
        <template v-if='cert'>
          <main>
            <router-view></router-view>
          </main>
        </template>  
        <template v-else> 
          <main class='cert'><sysinfo></sysinfo></main>  
        </template> 
        <div class="footer">
            <footerbar></footerbar>
        </div>
        <!-- <notice-modal :id="'notice—modal'" :title="mp.title" :msg="mp.msg" :status="mp.status" :callback="mp.callback"></notice-modal> -->
    </div>
</template>
<script>
import Headerbar from '../header/'
import Footerbar from '../footer/'
import sysinfo from '@/components/context/userinfo/sysinfo'
export default {
  components: {
    Headerbar,
    Footerbar,
    sysinfo
  },
  computed: {
    cert: function () {
      // return this.$store.state.cert.status === 'valid'
      return true
    }
  },
  methods: {
    jumpTo (path) {
      this.$router.push(path)
    },
    fixfooter () {
      if ($(window).height() > $(document.body).height()) {
        $('.footer').addClass('footer-fix')
      } else {
        $('.footer').removeClass('footer-fix')
      }
    }
  }
}
</script>
<style scoped>
.el-switch{
  font-size: 12px;
  height:24px;
}
.wrap {
  width: 100%;
  height: 100%;
  padding: 50px 0 40px;
  box-sizing: border-box;
}
main {
  width: 100%;
  height: 100%;
  position: relative;
}
.cert{
  padding: 100px 10%;
  box-sizing: border-box;
}
.context {
  height: 100%;
  padding: 0 40px 30px 240px;
  box-sizing: border-box;
  overflow: auto;
}
.footer {
  width: 100%;
  position: relative;
}
.footer-fix {
  position: fixed;
  bottom: 0;
}
.page-head-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.bk-green {
  background: #57bde3;
}
.bk-grey {
  background: #d2d2d2;
}
.radius {
  display: inline-block;
  border-radius: 50%;
  width: 10px;
  height: 10px;
  margin: 0 10px;
}
i {
  margin-right: 5px;
}
</style>