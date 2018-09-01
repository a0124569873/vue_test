<template>
  <el-card class="wrap">
    <el-form label-width="100px" :rules="rules" :model="formData" ref="form">
      <el-form-item label="证书文件:" prop="certificate">
        <el-input type="textarea" :rows="6" placeholder="从 '-----BEGIN CERTIFICATE-----' 开始" v-model="formData.certificate"></el-input>
      </el-form-item>
      <el-form-item label="证书私钥:" prop="certificate_key">
        <el-input type="textarea" :rows="6" placeholder="从 '-----BEGIN RSA PRIVATE KEY-----' 开始" v-model="formData.certificate_key"></el-input>
      </el-form-item>
      <el-form-item>
        <el-button size="small" type="primary" @click="upload" :loading="loading">保存</el-button>
      </el-form-item>
    </el-form>
  </el-card>
</template>
<script>
import siteService from 'services/siteService'

export default {
  data() {
    return {
      formData: {
        certificate: '',
        certificate_key: ''
      },
      rules: {
        certificate: [{ required: true, message: '请输入证书文件', trigger: 'blur' }],
        certificate_key: [{ required: true, message: '请输入证书私钥', trigger: 'blur' }]
      },
      loading: false
    }
  },
  computed: {
    siteId() {
      return this.$route.params.id
    }
  },
  methods: {
    fetch() {
      siteService.httpsCert(this.siteId).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.msg)
          return
        }
        const { certificate, certificate_key } = res.cert
        this.formData = {
          certificate: window.atob(certificate),
          certificate_key: window.atob(certificate_key)
        }
      })
    },
    upload() {
      if(this.loading) return
      this.loading = true
      const $form = this.$refs.form
      $form.validate(valid => {
        this.loading = false
        if(!valid) return false
        siteService.uploadCert(this.siteId, this.formData).then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.visible = false
          this.$message.success('证书上传成功！')
        })
      })
    }
  },
  created() {
    this.fetch()
  }
}
</script>
<style scoped>
.wrap {
  max-width: 700px;
  margin: 0 auto
}
.form {
  max-width: 100%;
  width: 700px
}
</style>


