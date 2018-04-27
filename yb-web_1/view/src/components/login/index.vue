<template>
	<div class="warp" @keydown.enter="login(Form)">
		<div class="context">
			<div>
				<p><img src="../img/logo.png" alt="" class="logos">XXX综合管理系统</p>
			</div>
			<div class="login">
				<el-form :model="Form" ref="Form" :rules="rules2" class="demo-ruleForm" >
					<el-form-item prop='user'>
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
      
		</div>
	</div>
</template>
<script>
import indexService from 'services/indexService'
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
  beforeRouteEnter (to, from, next) {
    next(vm => {
    // 通过 `vm` 访问组件实例
      vm.$message && vm.$message.close()
    })
  },
  mounted () {
    $('.el-message').remove()
    $('.account input').focus()
    $('.warp').css('height', $(window).height())
  },
  methods: {
    login (form) {
      this.$refs.Form.validate(valid => {
        if (valid) {
          indexService.login(form.user, form.pass, form.code).then(recvdata => {
            this.login_ing = false
            if (recvdata.errcode === 0) {
              window.sessionStorage.setItem('login', true)
              this.$store.commit('EDIT_NOTE', true)
              this.$router.push('/')
            } else {
              this.newCode()
              this.errorMessage = this.$t('error_code.' + recvdata.errcode)
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
    }
  }
}
</script>
<style scoped>
.warp {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #323542;
  position: relative;
}
p {
  color: #fff;
  text-align: center;
  font-size: 28px;
  margin-top: 15px;
}
.context {
  width: 400px;
  padding-bottom: 90px;
}
.logos {
  width: 40px;
  margin-right: 10px;
}
.login {
  background: #fff;
  height: 300px;
  margin-top: 50px;
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
  margin-top: 30px;
  font-size: 15px;
}
.errmsg{
  color: red;
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
