<style>
.el-input{
  width: 100%;
}
</style>
<template>
	<div class="warp container" @keydown.enter="login(Form)">
		<div class="context">
			<div>
				<p><img src="../img/loginicon.png" alt="" class="logos"></p>
        <p>HDC-320</p>
			</div>
			<div class="login">
				<el-form :model="Form" ref="Form" :rules="rules2" class="demo-ruleForm" >
					<el-form-item  prop='user'>
						<el-input v-model="Form.user" auto-complete="off" size="small" @focus='reset' placeholder="账 号" class='account'></el-input>
					</el-form-item>
					 <el-form-item  prop='pass'>
						<el-input v-model="Form.pass" auto-complete="off" size="small" @focus='reset' placeholder="密 码" type='password'></el-input>
					</el-form-item>
					<el-form-item  prop='code'>
						<div style="display: flex;justify-content: space-between">
							<el-input placeholder="验证码" class="input" v-model="Form.code" size="small" style="width: 50%;margin-right: 15px;"></el-input>
							<img class="codepic" src="/captcha.html" @click="newCode()">
						</div>
					</el-form-item>
				</el-form>
				<button @click="login(Form)">登 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;录</button>
        <p class='errmsg'>{{errorMessage}}</p>
			</div>
      <!-- <div>
				<p v-if='$store.state.cert.status !== "valid"' class='cert'>{{showtype}}，请联系供应商获取授权文件！</p>
        <p v-if='$store.state.cert.status === "valid" && accreditShow' class='cert'>系统将于{{misstime}}授权过期，请及时更新授权文件！</p>
			</div> -->
		</div>
	</div>
</template>
<script>
import indexService from 'services/indexService'
// import asyncRouter from '@/router/asyncRouterMap'
export default {
  data () {
    var validatePass = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入密码'))
      } else {
        callback()
      }
    }
    var validateuser = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入账号'))
      } else {
        callback()
      }
    }
    var validatecode = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请输入验证码'))
      } else {
        callback()
      }
    }
    return {
      errorMessage: '',
      Form: {
        user: '',
        pass: '',
        code: ''
      },
      rules2: {
        pass: [{ validator: validatePass, trigger: 'blur' }],
        user: [{ validator: validateuser, trigger: 'blur', required: true }],
        code: [{ validator: validatecode, trigger: 'blur' }]
      }
    }
  },
  mounted () {
    // 清除其他页面跳转到登录页面时，遗留的消息提示
    this.$nextTick(function () {
      $('.el-message').remove()      
    })
    $('.account input').focus()
    $('.warp').css('height', $(window).height())
    $('.container').backstretch('static/img/bg.jpg', {speed: 200})
  },
  computed: {
    misstime: function () {
      return this.getLocalTime(this.$store.state.cert.end_time)
    },
    accreditShow: function () {
      return (Number(this.$store.state.cert.end_time - Date.parse(new Date()) / 1000)) < 604800
    },
    showtype: function () {
      var type = ''
      switch (this.$store.state.cert.status) {
      case 'expired':
        type = '系统授权文件过期'
        break
      case 'missing':
        type = '系统授权文件缺失'
        break
      case 'device_not_match':
        type = '系统授权文件和设备不匹配'
        break
      case 'malform':
        type = '系统授权文件内容格式错误'
        break
      case 'type_error':
        type = '系统授权文件类型错误'
        break
      case 'unknown':
        type = '系统授权文件状态未知'
        break
      case 'uninit':
        type = '系统未授权'
        break
      default:
        type = ''
        break
      }
      return type
    }
  },
  methods: {
    login (form) {
      this.$refs.Form.validate(valid => {
        if (valid) {
          indexService.login(form.user, form.pass, form.code).then(res => {
            this.login_ing = false
            if (res.errcode === 0) {
              window.sessionStorage.setItem('login', true)
              window.sessionStorage.setItem('groupId', res.group_id)
              window.sessionStorage.setItem('name', form.user)
              this.$store.commit('EDIT_NOTE', true)
              this.$store.state.login = true
              this.__ID__.setId(res.group_id)
              this.__ID__.setName(form.user)
              // if (Number(res.group_id) === 1) {
              //   asyncRouter.setRouterMap('admin')
              // } else {
              //   asyncRouter.setRouterMap('user')
              // }
              // this.$router.addRoutes(asyncRouter.accessedRouters)
              this.$router.push('/')
            } else {
              this.newCode()
              this.errorMessage = this.$t('error_code.' + res.errcode)
            }
          })
        } else {
          return false
        }
      })
    },
    newCode () {
      $('.codepic').attr('src', '/captcha.html?d=' + new Date().getTime())
    },
    reset () {
      this.errorMessage = ''
    },
    getLocalTime (nS) {     
      return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/, ' ')     
    },
    filterAsyncRouter (asyncRouterMap, roles) {
      const accessedRouters = asyncRouterMap.filter(route => {
        if (route.meta[roles]) {
          if (route.children && route.children.length) {
            route.children = this.filterAsyncRouter(route.children, roles)
          }
          return true
        }
        return false
      })
      return accessedRouters
    }
  }
}
</script>
<style scoped>
.warp {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  position: relative;
}
p {
  color: #fff;
  text-align: center;
  font-size: 28px;
  /* margin-top: 15px; */
}
.context {
  width: 400px;
  padding-right: 15%;
  padding-bottom: 90px;
}
.logos {
  width: 80px;
  margin-right: 10px;
}
.login {
  background-image: url('../img/bgform.jpg');
  background-size: 100% 100%;
  height: 280px;
  margin-top: 20px;
  border-radius: 3px;
  padding: 30px;
  box-sizing: border-box;
}
button {
  width: 100%;
  height: 35px;
  background: #ae0918;
  color: #fff;
  border-radius: 5px;
  margin-top: 20px;
  font-size: 15px;
}
.errmsg{
  color: red;
  font-size: 13px;
  line-height: 30px;
}
.cert{
  color: #da972c;
  font-size: 13px;
  line-height: 30px;
}
button:hover {
  background: darkred;
}
.codepic{
  height: 32px;
}
</style>


