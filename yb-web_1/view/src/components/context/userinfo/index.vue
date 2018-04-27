<template>
    <div class=" userinfo">        
      <div class="content ">
        <el-row>
          <el-col :span='24'>
            <el-alert  title="密码格式为8-16位字母和数字组成" type="info" :closable="false" show-icon></el-alert>
          </el-col>
        </el-row>
        <div class="pwd">
          <h2 style="position:absolute;left:40px;top:82px;font-size:14px;">密码修改</h2>
          <div class="form_wrap">    
            <el-form 
              :model="pwdFrom" 
              :rules="pwdRules" 
              status-icon 
              label-width="100px"  
              ref="pwdFrom"
              @keyup.enter.native="submit('pwdFrom')">
              <el-form-item label='旧密码:' prop='old_pwd'>            
                <el-input v-model="pwdFrom.old_pwd" type="password" placeholder="请输入旧密码" class="input"></el-input>       
              </el-form-item>
              <el-form-item label='新密码:' prop='new_pwd'>
                <el-input v-model="pwdFrom.new_pwd" type="password" placeholder="请输入新密码"  class="input"></el-input>        
              </el-form-item>
              <el-form-item label='确认新密码:' prop='rept_pwd'>
                <el-input v-model="pwdFrom.rept_pwd" type="password" placeholder="请再次输入新密码"  class="input"></el-input>        
              </el-form-item>
              <div style="text-align:right;">                                            
                <el-button @click="submit('pwdFrom')" type="primary" :loading="pwdSubmiting">{{ pwdSubmiting ? '保存中...' : '保 存'}}</el-button>  
              </div>                        
            </el-form>
          </div>
        </div>       
      </div>  
    </div>
</template>
<script>
import indexService from 'services/indexService'
export default {
  data () {
    const checkPwd = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('不能为空'))
      }
      let reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('密码格式不正确'))
      } else {
        callback()
      }
    }  
    const checkRept = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('不能为空'))
      }
      if (value !== this.pwdFrom.new_pwd) {
        callback(new Error('两次输入密码不一致!'))
      } else {
        callback()
      }
    }  
     
    return {
      pwdFrom: {
        old_pwd: '',
        new_pwd: '',
        rept_pwd: ''     
      },
      pwdSubmiting: false, 
      pwdRules: {
        old_pwd: [{ validator: checkPwd, trigger: 'blur' }],
        new_pwd: [{ validator: checkPwd, trigger: 'blur' }],
        rept_pwd: [{ validator: checkRept, trigger: 'blur' }]
      }    
    }
  },

  methods: {
    submit (formName) {
      $('.el-message').remove()
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.pwdSubmiting = true
          indexService.changePwd(this.pwdFrom.old_pwd, this.pwdFrom.new_pwd)
            .then((res) => {
              this.pwdSubmiting = false
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  message: '修改成功',
                  type: 'success',
                  duration: 1500
                })
              }        
            })
        }
      })
    }
  }
}
</script>
<style lang="scss" scoped>
 .userinfo{
  .note{
    color:#b4bccc; 
    text-align: left;
  }
}
.pwd{
  display: flex;
  justify-content: center;
  .form_wrap{
    width:400px;
  }
}
.el-row{
  margin-bottom: 20px;
}
</style>

