<template>
  <div class="ctl-wrap">   
    <div class="leftbar">
      <ul class="vd-menu">
        <li @click="showbutns(0)" v-if="$checkPermisstion(__ID__.id)" >
              <a><i class="el-icon-caret-right showallbtn"></i>系统配置</a>
              <ul class="vd-menu-item">
                  <li :class="{'vd-menu-active': $route.path ==='/control/netaddress' || $route.path ==='/control'}" @click.prevent.stop="jumpTo('/control/netaddress')">
                    <a><i class="el-icon-setting"></i>网络地址配置</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/netaddress_c' || $route.path ==='/control'}" @click.prevent.stop="jumpTo('/control/netaddress_c')">
                    <a><i class="el-icon-setting"></i>网络地址配置_C</a>
                  </li>
                  <!-- <li :class="{'vd-menu-active': $route.path ==='/usermanage'}" @click.prevent.stop="jumpTo('/usermanage')">
                    <a><i class="el-icon-setting"></i>用户管理</a>
                  </li> -->
              </ul>
          </li>
          <li @click="showbutns(1)" v-if="$checkPermisstion(__ID__.id)" >
              <a><i class="el-icon-caret-right showallbtn"></i>防御配置</a>
              <ul class="vd-menu-item">
                  <li :class="{'vd-menu-active': $route.path ==='/control/globalconfig'}" @click.stop="jumpTo('/control/globalconfig')">
                    <a><i class="el-icon-setting"></i>全局参数配置</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/udpp'}" @click.stop="jumpTo('/control/udpp')">
                    <a><i class="el-icon-setting"></i>日志服务器配置</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/tcp'}" @click.prevent.stop="jumpTo('/control/tcp')">
                    <a><i class="el-icon-setting"></i>TCP端口保护</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/udp'}" @click.prevent.stop="jumpTo('/control/udp')">
                    <a><i class="el-icon-setting"></i>UDP端口保护</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/blacklist'}" @click.prevent.stop="jumpTo('/control/blacklist')">
                    <a><i class="el-icon-setting"></i>黑名单管理</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/whitelist'}" @click.prevent.stop="jumpTo('/control/whitelist')">
                    <a><i class="el-icon-setting"></i>白名单管理</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/dnswhitelist'}" @click.prevent.stop="jumpTo('/control/dnswhitelist')">
                    <a><i class="el-icon-setting"></i>DNS白名单管理</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/protectScope'}" @click.prevent.stop="jumpTo('/control/protectScope')">
                    <a><i class="el-icon-setting"></i>防护范围</a>
                  </li>  
              </ul>
          </li>
          <li @click="showbutns($checkPermisstion(__ID__.id) ? 2 : 1)" >
              <a><i class="el-icon-caret-right showallbtn"></i>实时监控</a>
              <ul class="vd-menu-item">
                  <li :class="{'vd-menu-active': $checkPermisstion(__ID__.id) ? $route.path.includes('realtime') : $route.path.includes('/control/realtime')}" @click.prevent.stop="jumpTo('/control/realtime')">
                    <a><i class="el-icon-news"></i>综合监控</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/realtime_c'}" @click.prevent.stop="jumpTo('/control/realtime_c')">
                    <a><i class="el-icon-news"></i>综合监控_C</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/sysstatus'}" @click.prevent.stop="jumpTo('/control/sysstatus')">
                    <a><i class="el-icon-news"></i>系统状态</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/netstatus'}" @click.prevent.stop="jumpTo('/control/netstatus')">
                    <a><i class="el-icon-news"></i>网卡状态</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/hoststatus'}" @click.prevent.stop="jumpTo('/control/hoststatus')">
                    <a><i class="el-icon-news"></i>主机状态</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/hoststatus_c'}" @click.prevent.stop="jumpTo('/control/hoststatus_c')">
                    <a><i class="el-icon-news"></i>主机状态_C</a>
                  </li>
                   <li :class="{'vd-menu-active': $route.path ==='/control/tmpblacklist'}" @click.prevent.stop="jumpTo('/control/tmpblacklist')">
                    <a><i class="el-icon-news"></i>临时黑名单</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/tmpwhitelist'}" @click.prevent.stop="jumpTo('/control/tmpwhitelist')">
                    <a><i class="el-icon-news"></i>临时白名单</a>
                  </li>
              </ul>
          </li> 
          <li @click="showbutns($checkPermisstion(__ID__.id) ? 3 : 2)">
              <a><i class="el-icon-caret-right showallbtn"></i>数据报表</a>
              <ul class="vd-menu-item">
                  <li :class="{'vd-menu-active': $route.path ==='/control/attack'}" @click.prevent.stop="jumpTo('/control/attack')">
                    <a><i class="el-icon-document"></i>攻击统计</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/flow'}" @click.prevent.stop="jumpTo('/control/flow')">
                    <a><i class="el-icon-document"></i>流量统计</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/connect'}" @click.prevent.stop="jumpTo('/control/connect')">
                    <a><i class="el-icon-document"></i>连接统计</a>
                  </li>
                  <li :class="{'vd-menu-active': $route.path ==='/control/cleanlogs'}" @click.prevent.stop="jumpTo('/control/cleanlogs')">
                    <a><i class="el-icon-document"></i>清洗日志</a>
                  </li>
              </ul>
          </li>
          <li @click="showbutns($checkPermisstion(__ID__.id) ? 4 : 3)">
              <a><i class="el-icon-caret-right showallbtn"></i>设备授权</a>
              <ul class="vd-menu-item">
                  <li :class="{'vd-menu-active': $route.path ==='/control/sysinfo'}" @click.prevent.stop="jumpTo('/control/sysinfo')">
                    <a><i class="el-icon-document"></i>授权信息</a>
                  </li>
              </ul>
          </li>
      </ul>
    </div>
    <div class="context" >
        <div class="page-head-line">
            <h3>{{textTitle}}</h3>
        </div>
        <el-row>
            <el-col :span="24">
              <router-view></router-view>
            </el-col>
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
      if (this.$route.path.includes('/control/realtime')) {
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2 
        } else {
          this.showIndex = 1
        }
        return '综合监控'
      }
      switch (this.$route.path) {
      case '/control':
        this.showIndex = 0
        return '网络地址管理'
      case '/control/netaddress':
        this.showIndex = 0
        return '网络地址管理'
      case '/control/netaddress_c':
        this.showIndex = 0
        return '网络地址管理_C'
      case '/control/usermanage':
        this.showIndex = 0
        return '用户管理'
      case '/control/globalconfig':
        this.showIndex = 1
        return '全局参数配置'
      case '/control/udpp':
        this.showIndex = 1
        return '日志服务器配置'
      case '/control/tcp':
        this.showIndex = 1
        return 'TCP端口保护'
      case '/control/udp':
        this.showIndex = 1
        return 'UDP端口保护'
      case '/control/whitelist':
        this.showIndex = 1
        return '白名单管理'
      case '/control/dnswhitelist':
        this.showIndex = 1
        return 'DNS白名单管理'  
      case '/control/blacklist': 
        this.showIndex = 1
        return '黑名单管理'
      case '/control/protectScope':
        this.showIndex = 1
        return '防护范围'
      
      case '/control/realtime':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2 
        } else {
          this.showIndex = 1
        }
        return '综合监控'
      case '/control/sysstatus':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2
        } else {
          this.showIndex = 1
        }
        return '系统状态'
      case '/control/netstatus':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2 
        } else {
          this.showIndex = 1
        }
        return '网卡状态'
      case '/control/hoststatus':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2
        } else {
          this.showIndex = 1
        }
        return '主机状态'
      case '/control/hoststatus_c':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2
        } else {
          this.showIndex = 1
        }
        return '主机状态_C'
      case '/control/tmpblacklist':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2
        } else {
          this.showIndex = 1
        }
        return '临时黑名单'
      case '/control/tmpwhitelist':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 2
        } else {
          this.showIndex = 1
        }
        return '临时白名单'
      case '/control/attack':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 3 
        } else {
          this.showIndex = 2
        }
        return '攻击统计'
      case '/control/flow':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 3 
        } else {
          this.showIndex = 2
        }
        return '流量统计'
      case '/control/connect':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 3 
        } else {
          this.showIndex = 2
        }
        return '连接统计'
      case '/control/cleanlogs':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 3 
        } else {
          this.showIndex = 2
        }
        return '清洗日志'
      case '/control/sysinfo':
        if (this.$checkPermisstion(this.__ID__.id)) {
          this.showIndex = 4 
        } else {
          this.showIndex = 3
        }
        return '授权信息'
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
        $('.vd-menu-item')
          .eq(index)
          .slideToggle(200)
        if (
          curShowAllBtn
            .parent()
            .find('.showbtn').length
        ) {
          curShowAllBtn
            .addClass('hidebtn')
            .removeClass('showbtn')
        } else {
          curShowAllBtn
            .addClass('showbtn')
            .removeClass('hidebtn')
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
  padding: 0 40px 30px 240px;
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
.leftbar{
  position: fixed;
  left: 0;
  top: 50px;
  bottom: 36px;
  padding-top: 10px;
  padding-bottom: 15px;
  background: #ecf0f1;
  box-shadow: 2px 3px 3px #d2d2d2;
  text-align: left;
  overflow-y: auto;
}
</style>