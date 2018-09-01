<template>
  <el-container direction="vertical">
    <navbar></navbar>
    <el-container>
      <el-aside :width="collapsed ? '56px' : '180px'"
                class="leftbar"
                :class="{ collapsed }">
        <el-menu router
                 :default-active="$route.path"
                 :collapse="collapsed"
                 text-color="#bfcbd9"
                 active-text-color="#409eff"
                 background-color="#202a38">
          <el-menu-item index="/">
            <!-- <i class="el-icon-menu"></i> -->
            <img src="/static/img/icon/icon-home.png" width="16" class="mr-lg">
            <span slot="title">用户主页</span>
          </el-menu-item>
          <el-menu-item index="/site" :class="{'is-active': $route.path.indexOf('/site') != -1}">
            <!-- <i class="el-icon-svg">
              <svg x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000">
        <g><path d="M500,990C229.4,990,10,770.6,10,500C10,229.4,229.4,10,500,10c270.6,0,490,219.4,490,490C990,770.6,770.6,990,500,990z M909.8,654.7H787.6c-38.6,123.4-126,224.6-239.5,280.5C714.5,917,852.6,806,909.8,654.7z M757.9,512.9c0-39.2-5.3-78.2-14.7-116.1H525.8v206.3h222.7C754.5,574,757.9,543.9,757.9,512.9z M474.2,654.7H270.6c37.7,99.5,111.2,192.9,204.2,242.6L474.2,654.7z M474.2,603.2V396.8H253.8c-7.6,33.8-11.7,68.4-11.7,103.2c0,35.6,4.3,70.1,12.3,103.2H474.2z M474.2,345.3V95.8c-94.6,53.6-168.3,141.3-205.5,249.4H474.2z M727.5,345.3C689.2,240.4,617.2,149.4,525.8,97v248.3H727.5z M525.8,654.7v249.2c97.6-52.8,173.1-141.7,209-249.2H525.8z M474.2,937.4v-9.5V937.4c-119.5-50.5-213.5-159.7-257.1-282.7H90.2C149.9,812.7,297.9,927.1,474.2,937.4z M74.7,395.2c-8.6,34.2-13.2,67.9-13.2,104.8c0,35.6,4.7,70,12.7,103.2h128.1c-7.6-33.2-11.9-67.6-11.9-103.2c0-34.8,4.2-69.3,11.5-103.2H87.4C82.9,396.8,78.4,397.3,74.7,395.2z M90.2,345.3h125.4c38.5-117.7,130.9-222.8,234.2-281.7C289.8,86.4,145.7,198.3,90.2,345.3z M545.4,64.5c108.2,55.9,193,160,235,280.8h129.4C852.3,193.2,713,81.8,545.4,64.5z M795.7,396.8c9,37.9,13.8,76.8,13.8,116.1c0,30.9-3.3,61-8.9,90.3h125.1c8-33.1,12.7-67.5,12.7-103.2c0-35.6-4.7-70-12.7-103.2H795.7z"/></g>
        </svg>
            </i> -->
            <img src="/static/img/icon/icon-site.png" width="16" class="mr-lg">
            <span slot="title">网站防护</span>
            <i class="active-icon"></i>
          </el-menu-item>
          <el-menu-item index="/app" :class="{'is-active': $route.path.indexOf('/app') != -1}">
            <!-- <i class="el-icon-svg">
              <svg x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000">
        <g><path d="M790,220.2H210L122.6,467h147.6c25.3,0,46.3,22.3,46.3,49.5v49.5h367.5v-49.5c0-27.2,20.6-49.5,45.9-49.5h147.4L790,220.2z M990,829.1c0,27.2-20.6,49.5-45.8,49.5H55.8c-25.3,0-45.8-22.4-45.8-49.5V516.4c0-6.2,1.3-12,3-17.3L135.6,153c6.7-18.3,23.1-31.6,42.7-31.6h643.3c19.6,0,36,13.4,42.7,32.1l122.6,345.3c1.8,5.7,3.1,11.5,3.1,17.7L990,829.1L990,829.1z"/></g>
        </svg>
            </i> -->
            <img src="/static/img/icon/icon-app.png" width="16" class="mr-lg">
            <span slot="title">应用防护</span>
            <i class="active-icon"></i>
          </el-menu-item>
          <el-menu-item index="/high" :class="{'is-active': $route.path.indexOf('/high') != -1}">
            <!-- <i class="el-icon-svg"><svg x="0px" y="0px" viewBox="0 0 1000 1000" enable-background="new 0 0 1000 1000">
                <g><path d="M881.1,990H118.9C58.5,990,10,941,10,881.1V118.9C10,59,58.5,10,118.9,10h762.2C941,10,990,59,990,118.9v762.2C990,941,941,990,881.1,990L881.1,990z M363.9,282.2H255v435.6h108.9V282.2L363.9,282.2z M772.2,336.7c0-30.1-24.4-54.4-54.4-54.4h-245v435.6h108.9V554.4h136.1c30.1,0,54.4-24.4,54.4-54.4V336.7L772.2,336.7z M581.7,363.9h108.9v108.9H581.7V363.9L581.7,363.9z"/></g>
                </svg>
            </i> -->
            <img src="/static/img/icon/icon-high.png" width="16" class="mr-lg">
            <span slot="title">高防实例</span>
          </el-menu-item>
          <el-submenu index="3">
            <template slot="title">
              <!-- <i class="el-icon-setting"></i> -->
              <img src="/static/img/icon/icon-user.png" width="16" class="mr-lg">
              <span>账户管理</span>
            </template>
            <el-menu-item index="/order">订单管理</el-menu-item>
            <el-menu-item index="/user">用户资料</el-menu-item>
          </el-submenu>
          <!-- <el-submenu index="5">
                    <template slot="title">
                        <i class="el-icon-message"></i>
                        <span slot="title">流量清洗</span>
                    </template>
                    <el-menu-item-group >
                        <el-menu-item index="/list">主机列表</el-menu-item>
                        <el-submenu index="1-1">
                            <template slot="title">实时监控</template>
                            <el-menu-item index="/realtime-statistic">实时流量统计</el-menu-item>
                            <el-menu-item index="/realtime-rank">实时流量排名</el-menu-item>
                        </el-submenu>
                        <el-menu-item index="/logmanage">日志管理</el-menu-item>
                    </el-menu-item-group>
                </el-submenu> -->
          <!-- <el-submenu index="6">
                    <template slot="title">
                        <i class="el-icon-message"></i>
                        <span slot="title">应用加速</span>
                    </template>
                    <el-menu-item-group>
                        <el-menu-item index="/domain">域名管理</el-menu-item>
                    </el-menu-item-group>
                </el-submenu> -->

        </el-menu>
        <a class="toggle"
           @click="expand">
          <i class="el-icon-arrow-left"></i>
        </a>
      </el-aside>
      <router-view class="el-main"></router-view>
    </el-container>
  </el-container>
</template>

<script>
import navbar from 'components/navBar'
export default {
  name: 'context',
  components: {
    navbar
  },
  data() {
    return {
      collapsed: false,
      collapsedKey: 'HD_MENU_COLLAPSED'
    }
  },
  methods: {
    expand() {
      this.collapsed = !this.collapsed
      window.localStorage.setItem(this.collapsedKey, String(this.collapsed))
    }
  },
  created() {
    const collapsed = window.localStorage.getItem(this.collapsedKey)
    this.collapsed = collapsed ? collapsed === 'true' : false
  }
}
</script>
<style lang="less">
@bg: #202a38;

.el-aside,
.el-main {
    overflow-x: hidden;
}
.leftbar {
    position: relative;
    background: @bg;
    transition: width 0.2s ease-in;

    .el-menu {
      border-right: none;
      svg {
        fill: currentColor;
        width: 16px
      }
    }

    .el-menu-item i,
    .el-submenu__title i {
      color: inherit
    }
    .el-submenu .el-menu-item {
      min-width: auto;
      background: darken(@bg, 8%)!important
    }
    .el-menu-item:hover,
    .el-submenu__title:hover {
        background: lighten(@bg, 4%)!important
    }

    .el-menu-item.is-active {
      color: #fff!important;
      background: darken(@bg, 4%)!important;
      border-left: 3px solid #EF5350;
      .active-icon {
          display: inline-block;
          width: 0px;
          height: 0px;
          border-top: 8px solid transparent;
          border-right: 10px solid #313a48;
          border-bottom: 8px solid transparent;
          position: absolute;
          right: 0;
          top: 50%;
          transform: translate(0, -50%);
        }
    }
    .el-submenu.is-active {
      .el-submenu__title {
        background: #151b25;
        border-right: 3px solid #EF5350;
      }
      .el-menu-item.is-active {
        border-right: none;
      }
    }
    .toggle {
        position: absolute;
        left: 50%;
        bottom: 10px;
        margin-left: -18px;
        width: 36px;
        height: 36px;
        line-height: 36px;
        text-align: center;
        color: #fff;
        border-radius: 50%;
        background: #EF5350;
        box-shadow: 0 0 3px #000;
        transition: transform 0.3s ease-in;
        &:hover {
            opacity: 0.8;
        }
    }

    &.collapsed {
        .toggle {
            transform: rotate(180deg);
        }
    }
}
.el-menu--popup {
  .el-menu-item.is-active {
    color: #fff!important;
    background: darken(@bg, 4%)!important;
  }
}
</style>
