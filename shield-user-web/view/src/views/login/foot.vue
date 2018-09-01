<template>
    <div class="bottom">
        <p>北京卫达科技有限公司版权所有 Copyright © 2015-{{year}}</p>
        <p>www.veda.com</p>
    </div>
</template>
<script>
export default {
  data() {
    return {
      year: ''
    }
  },
  created: function () {
    this.year = new Date().getFullYear()
  }
}
</script>
<style scoped>
    .bottom{
        background: #ffffff;
        padding: 0.5rem 0;
        font-size:14px;
        position: absolute;
        bottom: 0;
        width: 100%;
    }
    .bottom p{
        text-align: center;
        line-height: 1.5rem;
    }
</style>
