<template>
  <el-card class="domain-guide" :class="{ collapse }">
    <a slot="header" @click="collapse = !collapse">请按照下列步骤添加您的域名 <i class="el-icon-arrow-up collapse-arrow"></i></a>
    <el-steps :active="activeStep"
              align-center>
      <el-step description="填写域名信息"></el-step>
      <el-step description="域名审核"></el-step>
      <el-step description="选择实例线路"></el-step>
      <el-step description="修改DNS解析"></el-step>
    </el-steps>
    <div class="content f-x f-jc">
      <el-form v-if="activeStep === 1"
               :model="form1"
               :rules="rules"
               ref="form1"
               label-position="right"
               label-width="100px"
               status-icon>
        <el-form-item prop="name"
                      label="防护网站：">
          <el-input size="small"
                    placeholder="请填写域名"
                    v-model.trim="form1.name"></el-input>
        </el-form-item>
        <el-form-item prop="protocol"
                      label="协议类型：">
          <el-checkbox-group v-model="form1.protocol">
            <el-checkbox label="HTTP"></el-checkbox>
            <el-checkbox label="HTTPS"></el-checkbox>
          </el-checkbox-group>
        </el-form-item>
        <el-form-item prop="upstream"
                      label="源站IP：">
          <el-input type="textarea"
                    :autosize="{ minRows: 2}"
                    placeholder="请输入IP，以英文逗号分隔，最多10个"
                    v-model.trim="form1.upstream"></el-input>
        </el-form-item>
        <el-form-item>
          <el-button size="small"
                     type="primary"
                     :loading="loading1"
                     @click="addDomain">下一步</el-button>
        </el-form-item>
      </el-form>
      <ul v-if="activeStep === 2" class="domain-step">
        <li data-step="1">登录您注册域名的网站，进入域名管理后，选择TXT记录</li>
        <li data-step="2">
          <div>以下是我们为您提供需要修改的TXT记录值</div>
          <div class="f-x row-w mt-sm">
            <span>子域名</span>
            <span>解析方式</span>
            <span>记录值</span>
          </div>
          <div class="f-x row-w border">
            <span>{{form2.vdomain}}</span>
            <span>{{form2.way}}</span>
            <span>{{form2.textCode}}</span>
          </div>
        </li>
        <li data-step="3">
          <div class="mb-lg">修改记录值，点击下一步进行验证</div>
          <el-button size="small"
                     type="primary"
                     :loading="loading2"
                     @click="review">下一步</el-button>
        </li>
      </ul>
      <el-form v-if="activeStep === 3" :model="form3" label-position="top" size="mini">
        <el-form-item label="" prop="type">
          <el-radio-group v-model="form3.type">
            <el-radio border :label="1" :key="1">共享型</el-radio>
            <el-radio border :label="2" :key="2">独享型</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="选择实例：">
          <selectIp v-model="form3.linked_ips" :type="form3.type"></selectIp>
        </el-form-item>
        <el-form-item>
          <el-button size="small"
                     type="primary"
                     :disabled="!form3.linked_ips.length"
                     :loading3="loading3"
                     @click="linkup">下一步</el-button>
        </el-form-item>
      </el-form>
      <ul v-if="activeStep === 4" class="domain-step">
        <li data-step="1">登录您注册域名的网站，进入域名管理后，选择CNAME记录</li>
        <li data-step="2">
          <div>以下是我们为您提供需要修改的CNAME记录值</div>
          <div class="f-x row-w mt-sm">
            <span>子域名</span>
            <span>解析方式</span>
            <span>记录值</span>
          </div>
          <div class="f-x row-w border">
            <span>{{form4.domain}}</span>
            <span>{{form4.way}}</span>
            <span>{{form4.cname}}</span>
          </div>
        </li>
        <li data-step="3">
          <div class="mb-lg">修改记录值，点击保存后完成操作</div>
          <el-button size="small"
                     type="primary"
                     :loading="loading4"
                     @click="complete">完成</el-button>
        </li>
      </ul>
    </div>
  </el-card>
</template>
<script>
import matcher, { regex } from 'utils/matcher'
import areas from 'utils/areas'
import siteService from 'services/siteService'
import selectIp from 'components/selectIp'

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
    return {
      areas,
      collapse: true,
      form1: {
        name: '',     // 防护网站
        protocol: [],   // 协议
        upstream: '', // 源站ip
      },
      rules: {
        name: [{
          required: true,
          message: '请填写防护网站',
          trigger: 'blur'
        }, {
          pattern: regex.domain,
          message: '输入的域名有误',
          trigger: 'blur'
        }],
        protocol: [{
          required: true,
          message: '请至少选择一种协议类型',
          trigger: 'change'
        }],
        upstream: [{
          validator(rule, value, next) {
            if(!value) {
              next(new Error('请填写源站IP'))
              return
            }
            const ipArr = value.split(',')
            if(ipArr.length > 10) {
              next(new Error('最多只能输入10个IP'))
              return
            }
            let flag = true
            ipArr.forEach(str => {
              if(!matcher(str, 'ip')) {
                flag = false
                next(new Error('IP格式不正确'))
              }
            })
            if(flag) next()
          },
          trigger: 'blur',
          required: true
        }],
      },
      loading1: false,
      loading2: false,
      loading3: false,
      loading4: false,
      form3: {
        domain: '',
        type: 1,
        linked_ips: []
      },
      ipList: []
    }
  },
  computed: {
    filterIp() {
      const { type } = this.form3
      return this.ipList.filter(item => type === Number(item.type))
    }
  },
  watch: {
    activeStep(val) {
      if(val !== 1 && this.$refs.form1) {
        this.$refs.form1.clearValidate()
      }
    }
  },
  methods: {
    addDomain() {
      this.$refs.form1.validate(valid => {
        if (!valid) {
          return false
        }
        this.loading1 = true
        const { name, protocol, upstream } = this.form1
        const http = protocol.indexOf('HTTP') >= 0 ? 1 : 0
        const https = protocol.indexOf('HTTPS') >= 0 ? 1 : 0
        siteService.create({
          name,
          http,
          https,
          upstream
        }).then(res => {
          this.loading1 = false
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.$emit('add', name)
          // this.$refs.form1.resetFields()
        })
      })
    },
    review() {
      this.loading2 = true
      const { domain } = this.form2
      siteService.verifyText(domain).then(res => {
        this.loading2 = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.$emit('review', domain)
      })
    },
    linkup() {
      this.loading3 = true
      const { domain, linked_ips, type } = this.form3
      siteService.linkup(domain, { linked_ips, type }).then(res => {
        this.loading3 = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.collapse = true
        this.form3.linked_ips = []
        this.$message.success(`${domain} 接入已提交！`)
        this.$emit('linkup', domain, res.data.cname)
      })
    },
    complete() {
      this.loading4 = true
      const domain = this.form4.domain
      siteService.verifyCname(domain).then(res => {
        this.loading4 = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.collapse = true
        this.$emit('complete')
        this.$message.success(`${domain} 验证通过！`)
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.domain-guide {
  &.collapse {
    display: inline-block;
    .el-card__body {
      display: none
    }
    .collapse-arrow {
      transform: rotate(180deg)
    }
  }
    .el-step__main {
        margin-top: 8px;
    }
    .content {
        margin: 0 auto;
        padding: 40px 0 0;
    }
    .el-checkbox + .el-checkbox {
        margin-left: 10px;
    }
    .el-checkbox__label {
        padding-left: 5px;
    }

    .el-table__empty-block {
      min-height: 50px
    }
}
.domain-step {
  li {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
    &::before {
      content: attr(data-step);
      position: absolute;
      top: 0;
      left: 0;
      width: 20px;
      height: 20px;
      line-height: 20px;
      font-size: 12px;
      text-align: center;
      border: 1px solid #ddd;
      border-radius: 50%;
    }
  }
  .row-w {
    width: 600px;
    margin-left: -60px;
    line-height: 26px;
    span {
      flex: 1;
      padding: 0 5px;
      text-align: center
    }
  }
  .border {
    border: 1px solid #ddd;
    border-radius: 20px;
    span:last-child {
      color: #fff;
      background: @brand-color;
      border-radius: 20px;
    }
  }
}
</style>

