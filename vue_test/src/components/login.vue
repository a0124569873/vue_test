<template>
  <div>
    <el-row>
    <div class="wrap_login" @keydown.enter="login(Form)">
      <div class="context_login">
        <div>
          <p><img src="./img/logo.png" alt="" class="logos"> 登录界面 </p>
        </div>
        <div class="login">
          <el-form :model="Form" ref="Form" :rules="rules2" class="demo-ruleForm">
            <el-form-item prop="user">
              <el-input v-model="Form.user" placeholder="账号" auto-complete="" @focus="reset" class="el_item account" size="small" ></el-input>
            </el-form-item>
            <el-form-item prop="pass">
              <el-input v-model="Form.pass" placeholder="密码" auto-complete="" @focus="reset" type="password" size="small" class="el_item"></el-input>
            </el-form-item>
            <el-form-item prop="code">
              <el-input v-model="Form.code" placeholder="" class="el_item"></el-input>
              
            </el-form-item>
          </el-form>
          <el-button @click="login(Form)" class="login_btn">登&nbsp;录</el-button>
        </div>
      </div>

    </div>
    </el-row>
    <el-row>
      <el-button @click="switch_theme('red')">切换红色主题</el-button>
      <el-button @click="switch_theme('blue')">切换蓝色主题</el-button>
    </el-row>
  </div>
  
</template>
<script>
export default {
  data () {
    var validatePass = (rule, value, callback) => {
      if (value == '') {
        callback(new Error('请输入密码'))
      } else {
        callback()
      }
    }
    var validateUser = (rule, value, callback) => {
      if (value == '') {
        callback(new Error('请输入账号'))
      } else {
        callback()
      }
    }
    var validateCode = (rule, value, callback) => {
      if (value == '') {
        callback(new Error('请输入验证码'))
      } else {
        callback()
      }
    }
    return {
      errorMessage: '',
      Form: {
        user: 'aaa',
        pass: 'aaa',
        code: 'aaa'
      },
      rules2: {
        pass: [{ validator: validatePass, trigger: 'blur'}],
        user: [{ validator: validateUser, trigger: 'blur', required: true}],
        code: [{ validator: validateCode, trigger: 'blur'}],
      } 
    }
  },
  beforeRouteEnter (to, from, next) {
    next(vm => {
      vm.$message && vm.$message.close()
    })
  },
  mounted () {
    console.log("dfsdfsdfsdf")
  },
  methods: {
    login(form){
      this.$refs.Form.validate(valid => {
        if (valid) {
          window.localStorage.setItem('login',true)
          console.error(window.localStorage.getItem('login'))
          this.$router.push("/")
        }else{
          return false
        }
      })
    },
    reset(){
      this.errorMessage = ''
    },
    switch_theme(theme_color){

      // this.theme = this.theme === 'theme_blue' ? 'theme_red' : 'theme_red'
      // var theme = window.localStorage.getItem('theme')

      $('#app').attr('class', 'theme_' + theme_color)
      widow.localStorage.setItem('theme', 'theme_' + theme_color)

    }
  }

}
</script>