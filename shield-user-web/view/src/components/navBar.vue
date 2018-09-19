<template>
	<el-header class="header">
		<div class="brand">
			<img src="/static/img/dun.png" class="logo">
			<div style="margin: 0 10px;">
        <span style="font-size: 22px">幻盾</span>
        <span style="font-size: 14px">DDoS动态防御系统</span>
      </div>
		</div>
    <ul class="f-1 f-x nav">
      <li>
        <a class="nav-item">产品介绍</a>
        <div class="nav-dropdown">
          <div class="box f-x">
            <a href="http://www.veda.com/Magic_Environment/" target="_blank" class="nav-subitem">
              <img src="/static/img/jing.png" alt="幻境">
              <div class="mt-md">幻境</div>
            </a>
            <a href="http://www.veda.com/Magic_Wall/" target="_blank" class="nav-subitem">
              <img src="/static/img/qiang.png" alt="幻墙">
              <div class="mt-md">幻墙</div>
            </a>
            <a href="http://www.veda.com/Magic_Shield/" target="_blank" class="nav-subitem">
              <img src="/static/img/dun.png" alt="幻盾">
              <div class="mt-md">幻盾</div>
            </a>
            <a href="http://www.veda.com/Magic_Scope/" target="_blank" class="nav-subitem">
              <img src="/static/img/jie.png" alt="幻界">
              <div class="mt-md">幻界</div>
            </a>
            <a href="http://www.veda.com/Magic_Cloud/" target="_blank" class="nav-subitem">
              <img src="/static/img/yun.png" alt="幻云">
              <div class="mt-md">幻云</div>
            </a>
            <a href="http://www.veda.com/Magic_Guardian/" target="_blank" class="nav-subitem">
              <img src="/static/img/ying.png" alt="幻影">
              <div class="mt-md">幻影</div>
            </a>
          </div>
        </div>
      </li>
      <li>
        <a class="nav-item">解决方案</a>
        <div class="nav-dropdown">
          <div class="box f-x">
            <a href="http://www.veda.com/Solutionlist/cid-4/" target="_blank" class="nav-subitem">
              <div class="circle" style="background: #F44336">金融</div>
            </a>
            <a href="http://www.veda.com/Solutionlist/cid-5/" target="_blank" class="nav-subitem">
              <div class="circle" style="background: #00bcd4">军工</div>
            </a>
            <a href="http://www.veda.com/Solutionlist/cid-7/" target="_blank" class="nav-subitem">
              <div class="circle" style="background: #2196F3">企业</div>
            </a>
            <a href="http://www.veda.com/Solutionlist/cid-8/" target="_blank" class="nav-subitem">
              <div class="circle" style="background: #8BC34A">教育</div>
            </a>
          </div>
        </div>
      </li>
      <li><a class="nav-item" href="http://www.veda.com/caselist/cid-11/" target="_blank">帮助和文档</a></li>
    </ul>
		<div>
			<div>余额：<span>0.00</span></div>
			<span class="line"></span>
			<el-dropdown placement="bottom" @command="handleCommand" >
				<a class="el-dropdown-link user">
					{{uemail}} <i class="el-icon-caret-bottom el-icon--right"></i>
				</a>
				<el-dropdown-menu slot="dropdown">
          <el-dropdown-item command="b">用户资料</el-dropdown-item>
					<el-dropdown-item command="a">退出</el-dropdown-item>
				</el-dropdown-menu>
			</el-dropdown>
		</div>
	</el-header>
</template>
<script>
import indexServices from 'services/indexServices'

export default {
  computed: {
    uemail () {
      return this.$store.state.login.uemail
    }
  },
  created () {
    this.getUserName()
  },
  methods: {
    handleCommand (command) {
      if (command === 'a') {
        indexServices.logout().then(recvdata => {
          if (recvdata.errcode === 0) {
            window.location.href = '/login'
          }
        })
      }
      if (command === 'b') {
        this.$router.push('/user')
      }
    },
    getUserName () {
      if(this.uemail) return
      indexServices.isloginfo().then(recvdata => {
        if (recvdata.errcode === 0) {
          if (recvdata.is_login) {
            this.store.commit('EDIT_NOTE', {
              is_login: recvdata.is_login,
              uemail: recvdata.user_email
            })
          }
        }
      })
    }
  }
}
</script>
<style scoped lang="less">
@import '~@/less/variables.less';

.header {
  position: relative;
  z-index: 20;
  display: flex;
  justify-content: space-between;
  width: 100%;
  padding: 0 20px;
  height: 60px;
  color: #fff;
  background: #EF5350;
  box-shadow: 1px 0 3px 2px rgba(0, 0, 0, 0.3);
}
.header > div {
  display: flex;
  align-items: center;
}
.brand {
  margin-right: 50px;
  min-width: 200px;
}
.logo {
  width: 40px;
}
.line {
  display: inline-block;
  height: 15px;
  margin: 0 10px;
  border-right: 1px dashed #ddd;
}
.user {
  font-size: 12px;
  color: #fff
}
.nav {
  display: flex;
  min-width: 300px;

  &-item {
    display: block;
    line-height: 60px;
    padding: 0 20px;
    color: #fff;
    transition: .3s ease-in-out;

    &:hover {
      color: #EF5350;
      background: #fff;
    }
  }
  > li:hover {
    .nav-dropdown {
      height: 122px;
      border-bottom: 1px solid #e8e8e8;
    }
  }
  &-dropdown {
    position: absolute;
    left: 0;
    top: 60px;
    overflow: hidden;
    width: 100%;
    height: 0;
    color: @text-color;
    background: #fff;
    transition: .3s ease-in-out;
    .box {
      padding: 10px 0 10px 300px;
    }
  }
  &-subitem {
    display: block;
    width: 100px;
    padding: 10px;
    text-align: center;
    img {
      width: 50px;
      height: 50px;
    }
    &:hover {
      background: #fff;
      border-radius: 2px;
      box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
    }
    .circle {
      display: inline-block;
      width: 60px;
      height: 60px;
      line-height: 60px;
      text-align: center;
      border-radius: 50%;
      color: #fff;
    }
  }
}
</style>
