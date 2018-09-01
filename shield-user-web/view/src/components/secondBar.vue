<template>
  <div class="secondbar f-x f-js">
    <div class="secondbar-left f-x">
      <h2 class="secondbar-title">{{title}}</h2>
      <slot></slot>
    </div>
    <div class="secondbar-right">
      <router-link v-if="!hasBack"
                   to="/buy"
                   class="el-button el-button--primary el-button--mini">新购实例</router-link>
      <el-button v-else type="primary" size="mini" @click="back">返回</el-button>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    title: String,
    hasBack: Boolean,
    backUrl: String
  },
  methods: {
    back () {
      this.backUrl ? this.$router.push(this.backUrl)
        : this.$router.back()
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.secondbar {
    margin: -20px -20px 20px;
    padding: 12px 20px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    &-title {
        position: relative;
        font-size: 15px;
        font-weight: 400;
        height: 28px;
        line-height: 28px;
        padding-left: 12px;
        margin-right: 15px;
        &::before {
            content: '';
            position: absolute;
            left: 0;
            top: 20%;
            margin-right: 10px;
            display: inline-block;
            width: 2px;
            height: 60%;
            background: #F5a72b;
        }
    }
}
</style>
