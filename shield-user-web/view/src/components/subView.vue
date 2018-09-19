<template>
  <el-container class="sub-view">
    <el-aside width="180px" class="sub-view-leftbar">
      <div class="sub-view-title">{{view}}</div>
      <nav class="sub-view-nav">
        <router-link v-for="item in routes" :to="item.to" :key="item.text">
          {{item.text}}
          <i class="active-icon"></i>
        </router-link>
      </nav>
    </el-aside>
    <el-main>
      <secondBar :title="title" :hasBack="hasBack" :backUrl="backUrl"></secondBar>
      <router-view></router-view>
    </el-main>
  </el-container>
</template>
<script>
import secondBar from 'components/secondBar'

export default {
  props: {
    view: String,
    title: String,
    hasBack: {
      type: Boolean,
      default: true
    },
    routes: Array,
    backUrl: String
  },
  components: {
    secondBar
  },
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.sub-view {
  padding: 0;
  &-title {
    line-height: 56px;
    font-weight: 600;
    color: #ffffff;
  }
  &-leftbar {
    text-align: center;
    background: #313a48;
    color:#c1c1c1;
  }
  &-nav {
    a {
      display: block;
      padding: 8px 18px;
      line-height: 40px;
      &:hover {
        background: rgba(0, 0, 0, .1)
      }
      &.router-link-exact-active {
        background: #3a4351;
        position: relative;
        .active-icon {
          display: inline-block;
          width: 0px;
          height: 0px;
          border-top: 8px solid transparent;
          border-right: 10px solid #FFFFFF;
          border-bottom: 8px solid transparent;
          position: absolute;
          right: 0;
          top: 50%;
          transform: translate(0, -50%);
        }
      }
    }
  }
}
</style>

