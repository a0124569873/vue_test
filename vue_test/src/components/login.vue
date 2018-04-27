<template>
  <div class="wrap" @keydown.enter="login(Form)">
    <div class="context">
      <div>
        <p><img src="./img/logo.png" alt="" class="logos"> 登录界面 </p>
      </div>
      <div class="login">
        <el-form :model="Form" ref="Form" :rules="rules2" class="demo-ruleForm">
          <el-form-item prop="user">
            <el-input v-model="Form.user" placeholder="账号" auto-complete="" @focus="reset" class="account" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="pass">
            <el-input v-model="Form.pass" placeholder="密码" auto-complete="" @focus="reset" type="password" size="small"></el-input>
          </el-form-item>
          <el-form-item prop="code">
            <el-input v-model="Form.code" placeholder=""></el-input>
            
          </el-form-item>
        </el-form>
      </div>
    </div>

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
        user: '',
        pass: '',
        code: ''
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
          alert("success")
        }else{
          return false
        }
      })
    },
    reset(){
      this.errorMessage = ''
    }
  }

}
</script>
