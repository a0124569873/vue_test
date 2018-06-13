<template>
  <div class="wrap">
    <div class="bg">
      <p>
        Sorry，您访问的页面未找到..... <br> 点击
        <a @click="back">返回上级</a> 或
        <a @click="toHome">首页 &nbsp;&nbsp;&nbsp;</a>
      </p>
    </div>
  </div>
</template>
<script>
export default {
  methods: {
    toHome() {
      this.$router.push('/')
    },
    back() {
      window.history.back()
    }
  }
}
</script>
<style scoped>
.wrap {
    background: #fff;
    width: 100%;
    min-width: 1280px;
    height: 100%;
    min-height: 900px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.bg {
    width: 1280px;
    height: 800px;
    background: url(../../assets/img/error.png);
    position: relative;
    margin-bottom: 100px;
}
p {
    text-align: center;
    position: absolute;
    bottom: 150px;
    left: 0;
    right: 0;
    margin: auto;
    font-size: 20px;
    line-height: 50px;
    /*padding-left: 100px;*/
    display: inline-block;
}
a {
    color: rgba(139, 0, 0, 0.85);
    font-size: 20px;
}
</style>
