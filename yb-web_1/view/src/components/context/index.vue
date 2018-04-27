<template>
    <div class="wrap">
        <headerbar></headerbar>      
        <main>
          <router-view></router-view>
        </main>
        <div class="footer">
            <footerbar></footerbar>
        </div>
    </div>
</template>
<script>
import Headerbar from '../header/'
import Footerbar from '../footer/'
export default {
  components: {
    Headerbar,
    Footerbar
  },
  mounted () {
    this.fixfooter()
    window.onresize = () => {
      this.fixfooter()
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
.context {
  height: 100%;
  padding: 0 40px 0 240px;
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