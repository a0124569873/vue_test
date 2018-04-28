<template>
		<div class=" userinfo">				
			<div class="content ">
				<el-row>
					<el-col :span='24'>
            <el-alert  title="密码格式为8-16位字母和数字组成" type="info" :closable="false" show-icon></el-alert>
          </el-col>
        </el-row>
				<div class="pwd">
					<h4>密码修改</h4> 
					<div class="form_wrap">		
						<el-form :model="pwdFrom" :rules="pwdRules" status-icon label-width="100px"	ref="pwdFrom">
							<el-form-item label='旧密码:' prop='old_pwd'>						
								<el-input v-model="pwdFrom.old_pwd" type="password" placeholder="请输入旧密码" class="input"></el-input>			 
							</el-form-item>
							<el-form-item label='新密码:' prop='new_pwd'>
								<el-input v-model="pwdFrom.new_pwd" type="password" placeholder="请输入新密码"	class="input"></el-input>				
							</el-form-item>
							<el-form-item label='确认新密码:' prop='rept_pwd'>
								<el-input v-model="pwdFrom.rept_pwd" type="password" placeholder="请再次输入新密码"	class="input"></el-input>				
							</el-form-item>
							<div style="text-align:right;">																						
								<el-button @click="submit('pwdFrom')" type="primary" :loading="pwdSubmiting">{{ pwdSubmiting ? '保存中...' : '保 存'}}</el-button>	
							</div>												
						</el-form>
					</div>
				</div>			 
			</div>	
      <div class="content token">
        <span style="color:#2c3e50;font-size:14px">Access-token: </span>
				<p style="display:inline-block;color:#2c3e50;font-size:14px;margin:0 20px;">{{token ? token : '-'}}</p>
        <el-button type="primary" @click="setToken">刷新</el-button>
			</div>		 
		</div>
</template>
<script>
import indexService from 'services/indexService'
export default {
  data () {
    const checkPwd = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('密码不能为空'))
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
      },
      token: ''
    }
  },
  created () {
    this.getToken()
  },
  methods: {
    submit (formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.pwdSubmiting = true
          indexService
            .changePwd(this.pwdFrom.old_pwd, this.pwdFrom.new_pwd)
            .then(res => {
              this.pwdSubmiting = false
              if (res.errcode === 0) {
                this.$message({
                  message: '修改成功',
                  type: 'success',
                  duration: 1000
                })
              }
            })
            .catch(() => {
              this.pwdSubmiting = false
            })
        }
      })
    },
    getToken () {
      indexService.getToken().then(res => {
        if (res.errcode === 0) {
          this.token = res.access_token
        }
      })
    },
    setToken () {
      indexService.setToken().then(res => {
        if (res.errcode === 0) {
          this.token = res.access_token
          this.$message({
            message: '获取成功',
            type: 'success',
            duration: 1000
          })
        }
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.userinfo {
  .note {
    color: #b4bccc;
    text-align: left;
  }
}
.pwd,
.token {
  display: flex;
  justify-content: flex-start;
  .form_wrap {
    width: 60%;
  }
}
.el-row {
  margin-bottom: 20px;
}
.token {
  margin: 20px 0;
  padding-top: 30px;
  border-top: 5px solid rgba(0, 0, 0, 0.1);
}
.token {
  align-items: center;
}
</style>

