<template>
  <el-card class="add-rules" :class="{ collapse }">
    <h2 class="rule-title">添加规则</h2>
    <div class="rule-form">
      <el-form
        size="mini"
        :model="addTableData"
        label-width="145px"
        ref="addTableData"
        :rules="rules"
        @submit.native.prevent
        v-loading="dialogLoading">
        <el-form-item label='源站IP:' prop="server_ips">
          <el-input type="textarea" v-model="addTableData.server_ips" placeholder="请输入源站IP"></el-input>
        </el-form-item>
        <el-form-item label='源站端口:' prop="server_port" style="width:408px;">
          <el-input v-model="addTableData.server_port" placeholder="请输入源站端口" style="width: 120px;"></el-input>
        </el-form-item>
        <el-form-item label='实例与线路:' prop="proxy_ips">
          <div class="proxy-ip">
            <selectIp v-model="addTableData.proxy_ips" :type="addTableData.type"></selectIp>
          </div>
        </el-form-item>
        <el-form-item label='转发协议:' prop="protocol">
          <el-select size="mini"v-model="addTableData.protocol" style="width: 120px;">
          <el-option :value="1"
                    label="TCP"></el-option>
          <el-option :value="2"
                    label="UDP"></el-option>
        </el-select>
          <!-- <el-radio border label="TCP" v-model="addTableData.protocol"></el-radio>
          <el-radio border label="UDP" v-model="addTableData.protocol"></el-radio> -->
        </el-form-item>
        <el-form-item label='转发端口:' prop="proxy_port">
          <el-input v-model="addTableData.proxy_port" placeholder="请输入转发端口" style="width:120px;"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button type='primary' @click="addItem" :loading="subLoading">保 存</el-button>
          <el-button @click='returnBack'>返 回</el-button>
        </el-form-item>
      </el-form>
    </div>
  </el-card>
</template>
<script>
import selectIp from 'components/selectIp'
import portService from 'services/portService'

export default {
  components: {
    selectIp
  },
  props: {
    activeStep: Number,
    form2: Object,
    form4: Object
  },
  data() {
    const checkIp = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写源站IP'))
      }

      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      if (!regIp.test(value)) {
        return callback(new Error('IP格式错误'))
      }
      return callback()
    }
    const checkPort = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请填写端口'))
      }
      let regPort = /^((([0-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-5]{2}[0-3][0-5]))(\-([0-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-5]{2}[0-3][0-5])){0,1})$/
      if (!regPort.test(value)) {
        return callback(new Error('端口格式有误'))
      }
      return callback()
    }
    return {
      addTableData: {
        server_ips: '',
        server_port: '',
        proxy_ips: [],
        proxy_port: '',
        protocol: 'TCP',
        type: 3
      },
      rules: {
        server_ip: [{ validator: checkIp, trigger: 'blur' }],
        server_port: [{ validator: checkPort, trigger: 'blur' }],
        proxy_port: [{ validator: checkPort, trigger: 'blur' }]
      },
      dialogLoading: false,
      subLoading: false,
      collapse: true
    }
  },
  computed: {

  },
  watch: {

  },
  methods: {
    addItem () {
      this.$refs['addTableData'].validate(valid => {
        if(!valid) return
        this.subLoading = true
        portService.addNonList(this.addTableData).then(recvdata => {
          this.subLoading = false
          if (recvdata.errcode === 0) {
            this.$message.success('添加成功')
            this.$emit('add')
            this.$refs['addTableData'].resetFields()
            this.collapse = true
          } else {
            this.$message.error(recvdata.errmsg)
          }
        })
      })
    },
    returnBack() {
      this.collapse = true
      this.$refs['addTableData'].resetFields()
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';
.add-rules {
  padding: 8px 30px 0 30px;
  margin-bottom: 20px;
  &.collapse {
    display: none;
    .el-card__body {
      display: none
    }
    .collapse-arrow {
      transform: rotate(180deg)
    }
  }
  .rule-title {
    font-weight: normal;
    padding:0 0 8px 12px;
    position: relative;
    color: #409eff;
    font-size: 16px;
    border-bottom: 1px solid #eeeeee;
    &::before {
      content: '';
      position: absolute;
      left: 0;
      top: 12%;
      margin:0 10px 8px 0;
      display: inline-block;
      width: 2px;
      height: 55%;
      background: #409eff;
    }
  }
  .rule-form {
    padding-top: 20px;
    .el-textarea__inner {
      height: 112px;
      width: 313px;
    }
    .proxy-ip {
      // width: 832px;
      .g-select-ip {
        table {
          width: 100%;
          // background: #efefef;
          border: 1px solid #dddddd;
        }
        // td {
        //   border: none;
        // }
      }
    }
  }
}

</style>

