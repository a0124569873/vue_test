<template>
  <div class="user-protect">
    <secondBar title="用户资料"></secondBar>
    <!-- 绑定手机 -->
    <el-row type="flex"
            justify="center">
      <el-col :span="20">
        <el-card>
          <el-row type="flex"
                  align="middle"
                  justify="center">
            <el-col :span="2"
                    style="margin-right:20px;">
              <div class="user-icon">
                <img src="static/img/tel.png" />
              </div>
            </el-col>
            <el-col :span="16">
              <h3>{{formData.mobile}}</h3>
              <div>{{formData.mobile_verify_status ==1 ? '您已绑定安全手机':'您尚未绑定安全手机，为保障您对相关服务功能的正常使用，请尽快完成手机绑定'}}</div>
            </el-col>
            <el-col :span="6"
                    style="text-align:right;">
              <el-button type="primary"
                         plain
                         @click="binding(1)"
                         v-show="show !== 1">{{formData.mobile_verify_status == 1 ? '修改' : '立即绑定'}}</el-button>
            </el-col>
          </el-row>
          <el-collapse-transition>
            <div v-show="show === 1">
              <el-row type="flex"
                      justify="left"
                      class="binding-cont">
                <el-col :span="2"></el-col>
                <el-col :span="18">
                  <el-form :model="ruleForm1"
                           status-icon
                           :rules="rules1"
                           ref="ruleForm1"
                           label-width="100px"
                           label-position="left">
                    <el-form-item label="手机号码"
                                  prop="phone">
                      <el-input v-model="ruleForm1.phone"
                                size="small"
                                style="width:250px"></el-input>
                    </el-form-item>
                    <el-form-item label="验证码"
                                  prop="code">
                      <div class="el-button-group">
                        <el-input v-model="ruleForm1.code"
                                  size="small"
                                  style="width:250px">
                          <el-button slot="append"
                                     type="primary"
                                     size="small"
                                     :disabled="disable"
                                     @click="getCode(1)">{{codeButton}}</el-button>
                        </el-input>
                      </div>
                    </el-form-item>
                    <el-form-item>
                      <p>请输入您手机收到的验证码</p>
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary"
                                 size="mini"
                                 plain
                                 @click="cancelForm('ruleForm1')">取消</el-button>
                      <el-button type="primary"
                                 size="mini"
                                 @click="submitForm('ruleForm1')">保存</el-button>
                    </el-form-item>
                  </el-form>
                </el-col>
              </el-row>
            </div>
          </el-collapse-transition>
        </el-card>
      </el-col>
    </el-row>
    <!-- 绑定邮箱 -->
    <el-row type="flex"
            justify="center">
      <el-col :span="20">
        <el-card>
          <el-row type="flex"
                  align="middle"
                  justify="center">
            <el-col :span="2"
                    style="margin-right:20px;">
              <div class="user-icon">
                <img src="static/img/mail.png" />
              </div>
            </el-col>
            <el-col :span="16">
              <h3>{{formData.email}}</h3>
              <div>{{formData.email_verify_status == 1 ? '您已绑定安全邮箱':'您尚未绑定安全邮箱，为保障您对相关服务功能的正常使用，请尽快完成邮箱绑定'}}</div>
            </el-col>
            <el-col :span="6"
                    style="text-align:right;">
              <el-button type="primary"
                         plain
                         @click="binding(2)"
                         v-show="show !== 2">{{formData.email_verify_status == 1 ? '修改' : '立即绑定'}}</el-button>
            </el-col>
          </el-row>
          <el-collapse-transition>
            <div v-show="show === 2">
              <el-row type="flex"
                      justify="left"
                      class="binding-cont">
                <el-col :span="2"></el-col>
                <el-col :span="18">
                  <el-form :model="ruleForm2"
                           status-icon
                           :rules="rules1"
                           ref="ruleForm2"
                           label-width="100px"
                           label-position="left">
                    <el-form-item label="安全邮箱"
                                  prop="email">
                      <el-input v-model="ruleForm2.email"
                                size="small"
                                style="width:250px"></el-input>
                    </el-form-item>
                    <el-form-item label="验证码"
                                  prop="code">
                      <div class="el-button-group">
                        <el-input v-model="ruleForm2.code"
                                  size="small"
                                  style="width:250px">
                          <el-button slot="append"
                                     type="primary"
                                     size="small"
                                     :disabled="disable1"
                                     @click="getCode(2)">{{codeButton1}}</el-button>
                        </el-input>
                      </div>
                    </el-form-item>
                    <el-form-item>
                      <p>请输入您邮箱收到的验证码</p>
                    </el-form-item>
                    <el-form-item>
                      <el-button type="primary"
                                 size="mini"
                                 plain
                                 @click="cancelForm('ruleForm2')">取消</el-button>
                      <el-button type="primary"
                                 size="mini"
                                 @click="submitForm('ruleForm2')">保存</el-button>
                    </el-form-item>
                  </el-form>
                </el-col>
              </el-row>
            </div>
          </el-collapse-transition>
        </el-card>
      </el-col>
    </el-row>
    <!-- 实名认证 -->
    <el-row type="flex"
            justify="center">
      <el-col :span="20">
        <el-card>
          <el-row type="flex"
                  align="middle"
                  justify="center">
            <el-col :span="2"
                    style="margin-right:20px;">
              <div class="user-icon">
                <img src="static/img/idcard.png" />
              </div>
            </el-col>
            <el-col :span="16">
              <h3>{{formData.id_verify_status === 1 ? formData.real_name : '未实名认证'}}</h3>
              <div>{{formData.id_verify_status === 1 ? '您已完成实名认证' : '您尚未完成实名认证'}}</div>
            </el-col>
            <el-col :span="6"
                    style="text-align:right;">
              <el-button type="primary"
                         plain
                         @click="binding(3)">{{formData.id_verify_status === 1 ? (show1?'收起':'查看') : '立即绑定'}}</el-button>
            </el-col>
          </el-row>
          <el-collapse-transition>
            <div v-show="show1">
              <el-row type="flex"
                      justify="left"
                      class="binding-cont">
                <el-col :span="2"></el-col>
                <el-col :span="18">
                  <el-form :model="ruleForm3"
                           status-icon
                           :rules="rules1"
                           ref="ruleForm3"
                           label-width="100px"
                           label-position="left">
                    <el-form-item label="姓名"
                                  prop="realname">
                      <el-input v-model="ruleForm3.realname"
                                size="small"
                                style="width:250px"
                                :disabled="formData.id_verify_status === 1">
                      </el-input>
                    </el-form-item>
                    <el-form-item label="身份证号"
                                  prop="idnum">
                      <el-input v-model="ruleForm3.idnum"
                                size="small"
                                style="width:250px"
                                :disabled="formData.id_verify_status === 1">
                      </el-input>
                    </el-form-item>
                    <div v-show="formData.id_verify_status === 1">您已完成实名认证，如需修改信息，请联系客服</div>
                    <template v-if='formData.id_verify_status !== 1'>
                      <el-form-item label="验证码"
                                    prop="code">
                        <div class="el-button-group">
                          <el-input v-model="ruleForm3.code"
                                    size="small"
                                    style="width:118px"
                                    class="id-code">
                          </el-input>
                          <img class="codepic"
                               :src="`/captcha.html?d=${rdCode}`"
                               @click="newcode()">
                        </div>
                      </el-form-item>
                      <el-form-item>
                        <el-button type="primary"
                                   size="mini"
                                   plain
                                   @click="cancelForm('ruleForm3')">取消</el-button>
                        <el-button type="primary"
                                   size="mini"
                                   @click="submitForm('ruleForm3')">保存</el-button>
                      </el-form-item>
                    </template>
                  </el-form>
                </el-col>
              </el-row>
            </div>
          </el-collapse-transition>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import userService from 'services/userService'
import matcher from 'utils/matcher'

export default {
  data() {
    const validatePhone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请输入手机号'))
      }
      if (!matcher(value, 'phone')) {
        callback(new Error('手机格式错误'))
        this.disable = true
      } else {
        callback()
        this.disable = false
      }
    }
    const validateCode = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入验证码'))
      } else {
        callback()
      }
    }
    const validateEmail = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入邮箱'))
        this.disable1 = true
        return
      }
      if (!matcher(value, 'email')) {
        callback(new Error('邮箱格式有误，请再次输入'))
        this.disable1 = true
      } else {
        callback()
        this.disable1 = false
      }
    }
    const validateId = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入身份证号码'))
        return
      }
      if (!matcher(value, 'idcard')) {
        callback(new Error('身份证输入不合法，请再次输入'))
      } else {
        callback()
      }
    }
    const validateName = (rule, value, callback) => {
      if (!value) {
        callback(new Error('请输入真实姓名'))
        return
      }
      const ids = /^[\u4e00-\u9fa5]{2,4}$/
      if (!ids.test(value)) {
        callback(new Error('真实姓名填写有误，请再次输入'))
      } else {
        callback()
      }
    }
    return {
      show: false,
      show1: false,
      flag: false,
      countdown: 60,
      countdown1: 60,
      disable: true,
      disable1: true,
      codeButton: '点击获取验证码',
      codeButton1: '点击获取验证码',
      ruleForm1: {
        phone: '',
        code: '',
      },
      ruleForm2: {
        email: '',
        code: ''
      },
      ruleForm3: {
        realname: '',
        idnum: '',
        code: '',
      },
      rules1: {
        phone: [
          { validator: validatePhone, trigger: 'blur' }
        ],
        code: [
          { validator: validateCode, trigger: 'blur' }
        ],
        email: [
          { validator: validateEmail, trigger: 'blur' }
        ],
        realname: [
          { validator: validateName, trigger: 'blur' }
        ],
        idnum: [
          { validator: validateId, trigger: 'blur' }
        ]
      },
      formData: {},
      rdCode: Date.now()
    }
  },
  components: {
    secondBar
  },
  mounted() {
    this.loadData()
  },
  methods: {
    loadData() {
      userService.getUserInfo()
        .then((recvdata) => {
          if (recvdata.errcode === 0) {
            this.formData = recvdata
            this.formData.mobile = this.isBinded(this.formData.mobile, 1)
            this.formData.email = this.formData.email_verify_status === 1 ? this.isBinded(this.formData.safe_email, 2) : this.isBinded(this.formData.email, 2)
            this.formData.real_name = this.isBinded(this.formData.real_name, 3)
            this.formData.id_number = this.isBinded(this.formData.id_number, 4)
          }
        })
    },
    isBinded(name, type) {
      if (type === 1) {
        name = name === '' ? '安全手机' : name.substr(0, 3) + '****' + name.substr(7)
      } else if (type === 2) {
        let name1 = name.split(',').length
        if (name1 < 15) {
          name = name.substr(0, 2) + '***' + name.substr(4)
        } else {
          name = name.substr(0, 5) + '***' + name.substr(9)
        }
      } else if (type === 3) {
        let name1 = name.split(',').length
        if (name1 > 0 && name1 < 3) {
          name1 = name.substr(0, 1) + '**'
        } else if(name1 === 0) {
          name1 = ''
        }else{
          name1 = name.substr(0, 3) + '**' + name.substr(2)
        }
      } else {
        if(name.length > 0) {
          name = name.substr(0, 1) + '****************' + name.substr(17)
        } else {
          name = ''
        }
      }
      return name
    },
    submitForm(formName) {
      let serviceName, token
      if (formName !== 'ruleForm3') {
        token = this[formName].code
      }
      let senddata = {
        safephone: this.ruleForm1.phone,
        safemail: this.ruleForm2.email,
        token,
        real_name: this.ruleForm3.realname,
        id_number: this.ruleForm3.idnum,
        captcha: this.ruleForm3.code
      }
      switch (formName) {
      case 'ruleForm1':
        serviceName = userService.updatePhone(senddata)
        break
      case 'ruleForm2':
        serviceName = userService.updateEmail(senddata)
        break
      case 'ruleForm3':
        serviceName = userService.updateRealName(senddata)
        break
      default:
        break
      }
      this.$refs[formName].validate((valid) => {
        if (valid) {
          serviceName
            .then((recvdata) => {
              if (recvdata.errcode === 0) {
                this.$message({
                  message: '绑定成功！',
                  type: 'success'
                })
                this.$refs[formName].resetFields()
                this.show = false
                this.loadData()
              } else {
                this.$message({
                  showClose: true,
                  message: this.$t('error_code.' + recvdata.errcode),
                  type: 'warning',
                  duration: 1000,
                })
                if (formName === 'ruleForm3') {
                  this.newcode()
                }
              }
            })
        }
      })
    },
    cancelForm(formName) {
      this.$refs[formName].resetFields()
      this.show = false
      this.show1 = false
    },
    binding(type) {
      if (type === 3) {
        this.show1 = !this.show1
        this.ruleForm3.realname = this.isBinded(this.formData.real_name, 3)
        this.ruleForm3.idnum = this.isBinded(this.formData.id_number, 4)
      }
      this.show = type
    },
    newcode() {
      this.rdCode = new Date().getTime()
    },
    getCode(type) {
      this.sendCode(type)
      if (type === 1) {
        userService.sendPhone(this.ruleForm1.phone)
          .then((recvdata) => {
            if (recvdata.errcode === 0) {
              this.$message({
                message: '发送成功！',
                type: 'success'
              })
            } else {
              this.$message({
                showClose: true,
                message: this.$t('error_code.' + recvdata.errcode),
                type: 'warning',
                duration: 1000,
              })
            }
          })
      } else {
        userService.sendEmail(this.ruleForm2.email)
          .then((recvdata) => {
            if (recvdata.errcode === 0) {
              this.$message({
                message: '发送成功！',
                type: 'success'
              })
            } else {
              this.$message({
                showClose: true,
                message: this.$t('error_code.' + recvdata.errcode),
                type: 'warning',
                duration: 1000,
              })
            }
          })
      }
    },
    sendCode(type) {
      if (type === 1) {
        if (this.countdown === 0) {
          this.disable = false
          this.codeButton = '点击获取验证码'
          this.countdown = 60
          return false
        } else {
          this.disable = true
          this.codeButton = this.countdown + '秒可重新获取'
          this.countdown--
        }
        setTimeout(() => {
          this.sendCode(type)
        }, 1000)
      } else {
        if (this.countdown1 === 0) {
          this.disable1 = false
          this.codeButton1 = '点击获取验证码'
          this.countdown1 = 60
          return false
        } else {
          this.disable1 = true
          this.codeButton1 = this.countdown1 + '秒可重新获取'
          this.countdown1--
        }
        setTimeout(() => {
          this.sendCode(type)
        }, 1000)
      }
    }
  }
}
</script>
<style lang="less" scoped>
.user-protect {
    .el-card　 {
        min-height: 110px;
        margin-bottom: 20px;
        h3 {
            font-size: 18px;
            font-weight: 900;
            color: #000000;
        }
    }
    .formCont {
        transition: all 0.5s cubic-bezier(0, 1, 0.5, 1);
        display: none;
    }
    .slide-enter-active,
    .slide-leave-active {
        transition: all 0.5s;
    }
    .slide-enter,
    .slide-leave-active {
        opacity: 0;
    }
    .user-icon {
        width: 70px;
        height: 70px;
        text-align: center;
        line-height: 70px;
        border: 1px solid #ededed;
        border-radius: 50%;
        vertical-align: middle;
        img {
            width: 35px;
        }
    }
    .binding-cont {
        .el-col {
            margin-right: 20px;
        }
    }
    .codepic {
        height: 32px;
    }
    .id-code {
        margin-right: 25px;
    }
    .el-button-group {
        .el-button {
            width: 130px;
        }
        .is-disabled {
            background: #a0cfff;
            border-color: #a0cfff;
            color: #ffffff;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    }
}
</style>
