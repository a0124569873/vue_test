<template>
  <div class="config_cont">
    <el-row type="flex"
            justify="center">
      <el-col :span="24">
        <el-card>
            <el-form ref="configForm" :model="formData" label-width="100px">
              <el-form-item label="源站IP：" prop="server_ip">
                <el-input type="textarea" placeholder="请输入源站IP" size="small" v-model="formData.server_ip" resize="none"></el-input>
              </el-form-item>
              <el-form-item label="源站端口：" prop="server_port">
                <el-input placeholder="请输入源站端口" size="small" v-model="formData.server_port"></el-input>
              </el-form-item>
              <el-form-item label="转发协议：" prop="protocol">
                <el-select placeholder="请选择转发协议" size="small" v-model="formData.protocol">
                  <el-option value="TCP" label="TCP"></el-option>
                  <el-option value="UDP" label="UDP"></el-option>
                </el-select>
              </el-form-item>
              <el-form-item label="转发端口：" prop="proxy_port">
                <el-input placeholder="请输入转发端口" size="small" v-model="formData.proxy_port"></el-input>
              </el-form-item>
              <el-form-item label="实例与线路：" prop="proxy_ips">
                <selectIp v-model="proxy_ips" :type="3"></selectIp>
              </el-form-item>
              <el-form-item class="block">
                <el-button type="primary" size="small" @click="saveIpInfo">保存</el-button>
              </el-form-item>
            </el-form>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import selectIp from 'components/selectIp'
import portService from 'services/portService'
export default {
  data () {
    return {
      currentPage: 1,
      formData: {},
      proxy_ips: []
    }
  },
  computed: {
    portId() {
      return this.$route.params.id
    }
  },
  components: {
    selectIp
  },
  created() {
    this.loadData()
  },
  methods: {
    loadData() {
      portService.getConfig(this.portId).then(recvdata => {
        if (recvdata.errcode === 0) {
          this.formData = recvdata.data
          this.proxy_ips = this.formData.proxy_ip.map(item => ({
            ddos_id: item.instance_id,
            ip: item.ip,
            line: item.line
          }))
        } else {
          this.$message.error(recvdata.errmsg)
        }
      })
    },
    saveIpInfo() {
      this.$refs.configForm.validate(valid => {
        if(!valid) return false
        const server_ips = this.formData.server_ip
        const {app_id, server_port, proxy_port, protocol} = this.formData
        portService.updateAllConfig(app_id, {server_ips, proxy_ips: this.proxy_ips, server_port, proxy_port, protocol}).then(recvdata => {
          if (recvdata.errcode === 0) {
            this.$message.success('基本配置信息修改成功！')
            this.loadData()
          } else {
            this.$message.error(recvdata.errmsg)
          }
        })
      })
    }
  }
}
</script>
<style lang="less">
  .config_cont {
    .el-textarea__inner {
      max-width: 480px;
      height:170px;
    }
    .el-input {
      width: 195px;
    }
    .el-checkbox {
      margin-left: 0;
      margin-right: 30px
    }
  }
</style>
<style lang="less" scoped>
  .new-line {
    width:72%;
    height: 172px;
    background: #efefef;
    border: 1px solid #dddddd;
    padding: 20px 0 20px 20px;
    overflow: auto;
  }
  .pagination-style {
    width: 72%;
    margin-top:10px;
  }
</style>
