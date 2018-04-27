<template>
  <div class="ctl-wrap">   
    <ul class="vd-menu leftbar">
        <li @click="showbutns(0)" style=" padding-top: 20px;">
            <a><i class="el-icon-caret-right showallbtn"></i>系统配置</a>
            <ul class="vd-menu-item">
              <!-- <li :class="{'vd-menu-active': $route.path ==='/netaddr'}" @click.prevent.stop="jumpTo('/netaddr')">
                <a><i class="el-icon-setting"></i>网络地址配置</a>
              </li> -->
              <li :class="{'vd-menu-active': $route.path ==='/account_manage'}" @click.prevent.stop="jumpTo('/account_manage')">
                <a><i class="el-icon-setting"></i>账户管理</a>
              </li>
            </ul>
        </li>
        <li @click="showbutns(1)">
            <a><i class="el-icon-caret-right showallbtn"></i>业务配置</a>
            <ul class="vd-menu-item">
              <li :class="{'vd-menu-active': $route.path ==='/user_info' || $route.path ==='/control'}" @click.prevent.stop="jumpTo('/user_info')">
                  <a><i class="el-icon-setting"></i>用户接入配置</a>
              </li>
              <li :class="{'vd-menu-active': $route.path ==='/vpnserver'}" @click.prevent.stop="jumpTo('/vpnserver')">
                <a><i class="el-icon-setting"></i>VPN服务器配置</a>
              </li>
              <li :class="{'vd-menu-active': $route.path ==='/vpn_maped'}" @click.prevent.stop="jumpTo('/vpn_maped')">
                <a><i class="el-icon-setting"></i>VPN地址映射配置</a>
              </li>
              <li :class="{'vd-menu-active': $route.path ==='/connect_back'}" @click.prevent.stop="jumpTo('/connect_back')">
                <a><i class="el-icon-setting"></i>被动回连配置</a>
              </li>
              <li :class="{'vd-menu-active': $route.path ==='/camouflage'}" @click.prevent.stop="jumpTo('/camouflage')">
                <a><i class="el-icon-setting"></i>伪装原型池配置</a>
              </li>
              <li :class="{'vd-menu-active': $route.path ==='/grule'}" @click.prevent.stop="jumpTo('/grule')">
                <a><i class="el-icon-setting"></i>G设备规则上传配置</a>
              </li>
            </ul>
        </li>
        <li @click="showbutns(2)">
            <a><i class="el-icon-caret-right showallbtn"></i>实时监控</a>
            <ul class="vd-menu-item">
                <li :class="{'vd-menu-active': $route.path ==='/sys_status'}" @click.prevent.stop="jumpTo('/sys_status')">
                  <a><i class="el-icon-news"></i>设备运行状态</a>
                </li>
                <!-- <li :class="{'vd-menu-active': $route.path ==='/linkinfo'}" @click.prevent.stop="jumpTo('/linkinfo')">
                  <a><i class="el-icon-news"></i>链路信息</a>
                </li> -->
                 <li :class="{'vd-menu-active': $route.path ==='/g_status'}" @click.prevent.stop="jumpTo('/g_status')">
                  <a><i class="el-icon-news"></i>G设备保活信息</a>
                </li>
            </ul>
        </li>
    </ul>
    <div class="context" >
        <div class="page-head-line">
            <h3>{{textTitle}}</h3>
        </div>
        <el-row>
            <el-col :span="24"><router-view></router-view></el-col>
        </el-row>
    </div>
  </div>
</template>
<script>
export default {
  mounted () {
    this.showbutns(this.showIndex)
  },
  computed: {
    textTitle: function () {
      switch (this.$route.path) {
      case '/netaddr':
        this.showIndex = 0
        return '网络地址配置'
      case '/account_manage':
        this.showIndex = 0
        return '账户管理'
      case '/control':
        this.showIndex = 1
        return '用户接入配置'
      case '/user_info':
        this.showIndex = 1
        return '用户接入配置'
      case '/vpnserver':
        this.showIndex = 1
        return 'VPN服务器配置'
      case '/vpn_maped':
        this.showIndex = 1
        return 'VPN地址映射配置'
      case '/connect_back':
        this.showIndex = 1
        return '被动回连配置'
      case '/camouflage':
        this.showIndex = 1
        return '伪装原型池配置'
      case '/grule':
        this.showIndex = 1
        return 'G设备规则上传配置'
      case '/sys_status':
        this.showIndex = 2
        return '设备运行状态'
      case '/linkinfo':
        this.showIndex = 2
        return '链路信息'
      case '/g_status':
        this.showIndex = 2
        return 'G设备保活信息'
      default:
        this.showIndex = null
        break
      }
    }
  },
  data () {
    return {
      showIndex: null,
      run: false,
      load: false,
      online: false
    }
  },
  methods: {
    jumpTo (path) {
      this.$router.push(path)
    },
    showbutns (index) {
      if (index === null || typeof index === 'undefined') {
        return
      }
      var nowTime = new Date().getTime()
      var clickTime = $(this).attr('ctime')
      if (typeof clickTime !== 'undefined' && nowTime - clickTime < 500) {
        return false
      } else {
        var curShowAllBtn = $('.showallbtn').eq(index)
        $(this).attr('ctime', nowTime)
        this.isshow = !this.isshow
        $('.vd-menu-item').eq(index).slideToggle(200)
        if (
         curShowAllBtn.parent().find('.showbtn').length
        ) {
          curShowAllBtn.addClass('hidebtn').removeClass('showbtn')
        } else {
          curShowAllBtn.addClass('showbtn').removeClass('hidebtn')
        }
      }
    }
  }
}
</script>
<style scoped>
.ctl-wrap {
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
.page-head-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.el-switch{
  font-size: 12px;
  height:24px;
}
i {
  margin-right: 5px;
}
</style>