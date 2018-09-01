<template>
  <div class="ip-config-result">
    <div class="title">当前配置</div>
    <div class="item">
      <span class="label">类型：</span>
      <span class="value">{{config.type | bType}}</span>
    </div>
    <div class="item" v-if="config.type !== 3">
      <span class="label">版本：</span>
      <span class="value">{{config.version | sType}}</span>
    </div>
    <!-- <div class="item">
      <span class="label">地域：</span>
      <span class="value">{{config.area | area}}</span>
    </div> -->
    <div class="item">
      <span class="label">线路：</span>
      <span class="value">{{config.line | line}}</span>
    </div>
    <div class="item">
      <span class="label">IP个数：</span>
      <span class="value">{{config.ip_count}}个</span>
    </div>
    <div class="item">
      <span class="label">保底防护带宽：</span>
      <span class="value">{{config.base_bandwidth}}GB</span>
    </div>
    <div class="item">
      <span class="label">弹性防护带宽：</span>
      <span class="value">{{config.bandwidth}}GB</span>
    </div>
    <div class="item" v-if="config.type === 3">
      <span class="label">端口数：</span>
      <span class="value">{{config.port_count}}个</span>
    </div>
    <div class="item" v-else>
      <span class="label">防护域名数：</span>
      <span class="value">{{config.site_count}}个</span>
    </div>
    <div class="item">
      <span class="label">业务带宽：</span>
      <span class="value">{{config.normal_bandwidth}}M</span>
    </div>
    <div class="item">
      <span class="label">购买数量：</span>
      <span class="value">{{config.count}}个</span>
    </div>
    <div class="item">
      <span class="label">购买时长：</span>
      <span class="value">{{config.period || '...' | period}}</span>
    </div>
    <div class="price">
      <span v-show="loading" style="font-size: 16px">正在计算价格...</span>
      <span v-show="!loading">{{price | price}}</span>
    </div>
    <button class="btn" @click="goPreOrder">立即购买</button>
  </div>
</template>
<script>
export default {
  props: {
    config: Object,
    price: Number,
    loading: Boolean
  },
  methods: {
    goPreOrder () {
      const t = Date.now()
      const { config, price } = this
      if (config.area.length === 1) {
        const two = config.area[0]
        config.area = [Math.floor(two / 10), two]
      }
      window.sessionStorage.setItem(t, JSON.stringify({
        price,
        config
      }))
      this.$router.push({
        path: '/order/prepay',
        query: { t }
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.ip-config-result {
  height: 100%;
  padding: 10px 20px 20px;
  background: #fff;
  box-shadow: 0 0 8px rgba(0, 0, 0, .2);
  .title {
    margin: 0 -18px 10px;
    padding: 10px 18px;
    font-size: 16px;
    border-bottom: 1px solid #d8d8d8;
  }
  .item {
    display: flex;
    align-items: center;
    line-height: 32px;
  }
  .label {
    min-width: 100px;
    color: @text-gray-color;
  }
  .price {
    font-size: 36px;
    color: #ff5722;
    height: 77px;
    line-height: 77px;
    vertical-align: middle
  }
  .btn {
    padding: 0 30px;
    font-size: 18px;
    line-height: 40px;
    color: #fff;
    background: #ff5722;
    border: none;
    &:hover {
      opacity: .8;
    }
  }
}
</style>

