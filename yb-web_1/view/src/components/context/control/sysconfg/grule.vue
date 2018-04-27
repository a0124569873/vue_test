<template>
  <div class="content">
    <div>	
      <el-form 
        :model="formData"  
        :rules="rules"  
        label-width="135px" 
        label-position="right" 
        status-icon  
        ref="addrFrom"
        @keyup.enter.native="setGRule('addrFrom')">	
        <el-form-item label="上传服务器IP地址" prop="ip" >							 
          <el-input v-model="formData.ip" placeholder="只能输入单个IP"></el-input>
        </el-form-item>	
        <el-form-item label="上传服务器端口" prop="port" >							 
          <el-input v-model="formData.port" placeholder="端口范围是[1, 65535]"></el-input>
        </el-form-item>
        <el-form-item label="用户名" prop="username" >							 
          <el-input v-model="formData.username"></el-input>
        </el-form-item> 
        <el-form-item label="密码" prop="password">	
            <el-input v-model="formData.password" key="pwd1" placeholder="密码不能查看，只能更新"></el-input>
         	 
          <!-- <span v-else style="position:relative;top:2px">********</span>	 -->
          <!-- <span style="position:absolute;right:-34px; top:2px;width:30px;height:30px;text-align:center;cursor:pointer;" @click="viewPwd">
            <i class="el-icon-view" ></i>
          </span> -->
        </el-form-item>
        <!-- <p class="el-form-item el-form-item--feedback is-required el-form-item--mini" style="position:relative" v-else>
          <label for="username" class="el-form-item__label" style="width: 135px;">密码</label>
          <span style="line-height:30px;">********</span>
          <span style="position:absolute;right:-34px; top:2px;width:30px;height:30px;text-align:center;cursor:pointer;" @click="viewPwd">
            <i class="el-icon-view" ></i>
          </span>
        </p> -->
        <el-form-item label="规则文件路径" prop="path" >							 
          <el-input v-model="formData.path"></el-input>
        </el-form-item>  
   
        <el-button @click="setGRule('addrFrom')" type="primary" :loading="isSubmitting" style="width:100%">保存</el-button>                
      </el-form>		
    </div>			 
  </div>
</template>
<script>
import GRuleService from 'services/gruleservice'
export default {
  data () {
    const checkIP = (rule, value, callback) => {
      let reg = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('IP格式不正确'))
      } else {
        callback()
      }
    }

    const checkPort = (rule, value, callback) => {
      let regPort = /^([1-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/
      let status = regPort.test(value)
      if (!status) {
        callback(new Error('端口格式不正确'))
      } else {
        callback()
      }
    }
    return {
      showPwd: false,
      isSubmitting: false,
      formData: {
        ip: '',
        port: '',
        username: '',
        password: '',
        path: ''
      },
      rules: {
        ip: [
          {required: true, message: '不能为空', trigger: 'blur'},
          {validator: checkIP, trigger: 'blur'}
        ],
        port: [
          {required: true, message: '不能为空', trigger: 'blur'},
          {validator: checkPort, trigger: 'blur'}],
        username: [{required: true, message: '不能为空', trigger: 'blur'}],
        // password: [{required: true, message: '不能为空', trigger: 'blur'}],
        path: [{required: true, message: '不能为空', trigger: 'blur'}]
      }
    }
  },
  created () {
    this.getGRule()
  },
  methods: {
    viewPwd () {
      this.showPwd = !this.showPwd
    },
    getGRule () {
      GRuleService.getGRule()
      .then(res => {
        if (res.errcode === 0) {
          if (res['1']) {
            let data = res['1'].split('|')
            this.formData.ip = data[0]
            this.formData.port = data[1]
            this.formData.username = data[2]
            this.formData.password = data[3]
            this.formData.path = data[4]
          }
        }
      })
    },
    setGRule (formName) {
      $('.el-message').remove()
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.$refs[formName].clearValidate() // 清除校验结果
          this.isSubmitting = true
          let rule = `${this.formData.ip}|${this.formData.port}|${this.formData.username}|${this.formData.password}|${this.formData.path}`      
          GRuleService.setGRule({'1': rule})
            .then(res => {
              this.isSubmitting = false
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  message: '保存成功',
                  type: 'success',
                  duration: 1500
                })
                this.getGRule()
              }
            }, () => {
              this.isSubmitting = false
            })
        }
      })
    }
  }
}
</script>
<style scoped lang="scss">
ul {
  display: inline-block;
}
.content{
  display: flex;
  justify-content: center;
}
.content>div{
  width: 500px;
  margin-top: 20px;
}

li {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  span {
    margin: 0 5px;
  }
  .input {
    width: 200px;
  }
}
</style>