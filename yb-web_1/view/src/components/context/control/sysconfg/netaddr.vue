<template>
  <div class="content">
    <div>	
      <el-form 
        :model="formData"  
        :rules="rules"  
        label-width="118px" 
        label-position="right" 
        status-icon  
        ref="addrFrom"
        @keyup.enter.native="setNetAddr('addrFrom')">	
        <el-form-item label="WAN口IP" prop="wan_ip" >							 
          <el-input v-model="formData.wan_ip" ></el-input>
        </el-form-item>	
        <el-form-item label="LAN口IP" prop="lan_ip" >							 
          <el-input v-model="formData.lan_ip"></el-input>
        </el-form-item>
        <el-form-item label=" WAN口网关IP" prop="wan_gateway_ip" >							 
          <el-input v-model="formData.wan_gateway_ip"></el-input>
        </el-form-item> 
        <el-form-item label="LAN口网关IP" prop="lan_gateway_ip" >							 
          <el-input v-model="formData.lan_gateway_ip"></el-input>
        </el-form-item>  
        <el-form-item label="日志子系统IP" prop="log_ip" >							 
          <el-input v-model="formData.log_ip"></el-input>
        </el-form-item>
        <el-button @click="setNetAddr('addrFrom')" type="primary" :loading="isSubmiting" style="width:100%">{{ isSubmiting ? '保存中...' : '保存'}}</el-button>                
      </el-form>		
    </div>			 
  </div>
</template>
<script>
import netaddrService from 'services/netaddrService'
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
    return {
      isSubmiting: false,
      formData: {
        wan_ip: '',
        lan_ip: '',
        wan_gateway_ip: '',
        lan_gateway_ip: '',
        log_ip: ''
      },
      rules: {
        wan_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }
        ],
        lan_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }
        ],
        wan_gateway_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }
        ],
        lan_gateway_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }
        ],
        log_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }
        ]
      }
    }
  },
  created () {
    this.getNetAddr()
  },
  methods: {
    getNetAddr () {
      netaddrService.getNetaddr()
      .then(res => {
        if (res.errcode === 0) {
          this.formData.wan_ip = res.wan_ip
          this.formData.lan_ip = res.lan_ip
          this.formData.wan_gateway_ip = res.wan_gateway_ip
          this.formData.lan_gateway_ip = res.lan_gateway_ip
          this.formData.log_ip = res.log_ip
        }
      })
    },
    setNetAddr (formName) {
      $('.el-message').remove()
      this.$refs[formName].validate(valid => {
        if (valid) {
          this.$refs[formName].clearValidate() // 清除校验结果
          this.isSubmiting = true
          let net = this.formData.wan_ip + '|' +
                     this.formData.lan_ip + '|' +
                     this.formData.wan_gateway_ip + '|' +
                     this.formData.lan_gateway_ip + '|' +
                     this.formData.log_ip      
          netaddrService.setNetaddr(net)
            .then(res => {
              this.isSubmiting = false
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  message: '保存成功',
                  type: 'success',
                  duration: 1500
                })
                this.getNetAddr()
              }
            })
            .fail(() => {
              this.isSubmiting = false
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