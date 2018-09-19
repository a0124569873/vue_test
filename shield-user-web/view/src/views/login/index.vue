<template>
    <div class="wrap_login" @keydown.enter="login(username,password,usercode)">
		<div class="flex_wrap">
			<div class="cont_wrap">
				<div class="title">
					<h3><img src="../../assets/img/logo.png"  class="logo"/>{{$t('message.logo_text')}}</h3>
					<h4>{{$t('message.title')}}</h4>
				</div>
				<div class="from_wrap">
					<div>
						<img src="../../assets/img/user.png" class="icon">
						<input type="email" v-model = "username" id="username" @blur = "testFormat(username,'name','#username')" :placeholder="$t('message.email')" autocomplete="off"/>
						<p class="errormsg"></p>
					</div>
					<div>
						<img src="../../assets/img/password.png" class="icon">
						<input type="password" v-model = "password" id="password"  @blur = "testFormat(password,'password','#password')" :placeholder="$t('message.password')" autocomplete="off"/>
						<p class="errormsg"></p>
					</div>
					<div class="w-div">
						<img class="codepic" :src="`/captcha.html?d=${rdTime}`" @click="newCode">
						<img src="../../assets/img/code.png" class="icon">
						<input type="text" v-model = "usercode" id = "code" @blur = "testFormat(usercode,'code','#code')" :placeholder="$t('message.code')"/>
						<p class="errormsg"></p>
					</div>
					<button :disabled ="login_ing" :class ="login_ing?'gray':'green'"  @click = "login(username,password,usercode)"> {{login_ing ? $t('message.logining') : $t('message.login')}} </button>
				</div>
				<div class="forgets">
					<a @click="jumpTo('/password/forget')">{{$t('message.forgot')}}</a>
					<a @click="jumpTo('/register')">{{$t('message.now_register')}}</a>
				</div>
			</div>
		</div>
		<i class="bg-c"></i>
		<foot></foot>
    </div>
</template>
<script>
import foot from './foot'
import indexServices from 'services/indexServices'
export default {
  components: {
    foot
  },
  data () {
    return {
      username: '',
      password: '',
      usercode: '',
      login_ing: false,
      rdTime: Date.now()
    }
  },
  methods: {
    testFormat (val, type, ele) {
      // let reg
      switch (type) {
      case 'name':
        // reg = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/
        break
      case 'password':
        // reg = /^[0-9a-zA-Z]{6,16}$/g
        break
      case 'code':
        // reg = /^\S+$/g
        break
      default:
        break
      }
      let Val = val.trim()
      // if (reg.test(Val)) {
      //   $(ele)
      //     .parent()
      //     .find('.errormsg')
      //     .hide()

      //   return true
      // } else
      if (Val === '') {
        $(ele)
          .parent()
          .find('.errormsg')
          .html(this.$t('message.not_empty'))
          .show()
        return false
      } else {
        $(ele)
          .parent()
          .find('.errormsg')
          .hide()
        return true
      }
      // } else {
      //   $(ele)
      //     .parent()
      //     .find('.errormsg')
      //     .html(this.$t('message.reg_wrong'))
      //     .show()
      //   return false
      // }
    },
    login (username, password, usercode) {
      let nameTest = this.testFormat(username, 'name', '#username')
      let wordTest = this.testFormat(password, 'password', '#password')
      let codeTest = this.testFormat(usercode, 'code', '#code')
      if (nameTest && wordTest && codeTest) {
        this.login_ing = true

        indexServices.login(username, password, usercode).then(recvdata => {
          this.login_ing = false
          if (recvdata.errcode === 0) {
            //  this.setCookies(username,password)
            this.$router.push('/')
            let data = { is_login: true, uemail: username, ...recvdata }
            this.$store.commit('EDIT_NOTE', data)
          } else {
            this.newCode()
            this.$message({
              showClose: true,
              message: this.$t('error_code.' + recvdata.errcode),
              type: 'warning',
              duration: 1000
            })
          }
        })
      }
    },
    newCode () {
      this.rdTime = new Date().getTime()
    },
    jumpTo (page) {
      this.$router.push(page)
    }
  }
}
</script>
<style scoped lang="less">
@inputHeight: 30px;
.wrap_login {
  width: 100%;
  background-color: #1ba7fc;
}
.bg-c {
  position: absolute;
  height: 40%;
  width: 120%;
  bottom: 0;
  left: -10%;
  border-radius: 50% 50% 0 0;
  background: #23a0fd;
}
.flex_wrap {
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
}
.cont_wrap {
  width: 30%;
  max-width: 400px;
  min-width: 300px;
  padding-bottom: 10%;
  z-index: 2;
}
.title {
  text-align: center;
  margin-bottom: 40px;
  letter-spacing: 3px;
  h3,
  h4 {
    color: #ffffff;
    font-weight: 600;
  }
  h3 {
    font-size: 26px;
    margin-bottom: 10px;
  }
  h4 {
    font-size: 20px;
  }
}

.from_wrap {
  background: #ffffff;
  padding: 30px;
  div {
    position: relative;
    margin-bottom: 24px;
  }
  .icon {
    position: absolute;
    top: 8px;
    left: 9px;
  }
  input {
    width: 100%;
    height: @inputHeight;
    padding-left: 26px;
    box-sizing: border-box;
    background-repeat: no-repeat;
    background-position: 8px center;
    background: #f7f7f7;
    border: 1px solid #f7f7f7;
  }

  .errormsg {
    color: #ff4949;
    margin-top: 4px;
    position: absolute;
  }
  .codepic {
    position: absolute;
    right: 1px;
    top: 1px;
    width: 100px;
    height: 28px;
  }
  input:focus {
    border: 1px solid #50bfff;
  }
  button {
    border: none;
    color: #ffffff;
    width: 100%;
    height: @inputHeight;
  }
}
.forgets {
  text-align: right;
  margin-top: 14px;

  a:hover {
    color: #f7f7f7;
  }
  a {
    color: #fff;
    margin-left: 10px;
  }
}
.gray {
  background: #e5e9f2;
}
.green {
  background: #2fc9a4;
}
</style>
