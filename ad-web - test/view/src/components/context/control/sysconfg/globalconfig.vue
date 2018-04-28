<template>
		<div id='globalconfig'>	 
        <div class="content">
          <el-row>
            <el-col :span='12'>
              <div class="configModule">
                <p class="configTitle">系统操作环境</p>
                <el-form ref="operform" :model="operform" label-width="80px">
                  <el-form-item label="流量控制">
                     <el-select v-model="operform.control">
                       <el-option label='攻击防御模式' value='atk_def'></el-option>
                       <el-option label='透明直通（不处理数据）' value='bypass'></el-option>
                     </el-select>
                  </el-form-item>
                </el-form>
              </div>
            </el-col>
            <el-col :span='12'>
              <div class="configModule">
                <p class="configTitle">系统防护参数</p>
                <el-form ref="protform" :model="protform" label-width="180px" :rules="rules">
                  <el-form-item label='屏蔽持续时间' prop='time'>
                    <el-input v-model="protform.time" style="width:200px;margin-right: 10px;"></el-input>秒
                  </el-form-item>
                </el-form>
              </div>
            </el-col>
            <el-col :span='24' style="text-align: right">
              <el-button type="primary" @click="setSysForm('protform')">保 存</el-button>
            </el-col>
          </el-row>
          <el-row style="margin-top: 20px;">
            <el-col :span='24'>
              <div class="configTitle" style="display: flex;justify-content: space-between;padding-bottom: 10px;">
                <span>主机防护参数</span>
                <div>
                  <span style="marign-left: 10px;">设置集</span>
                  <el-select v-model="host_param" style="width: 70px;" @change='getherChange'>
                    <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
                  </el-select>
                </div>
              </div>
            </el-col>
            <el-col :span='12'>
              <div class="configModule">
                <p class="configTitle">流量防护策略</p>
                <el-form ref="flowform" :model="flowform" label-width="150px" :rules="rules">
                  <el-form-item label='SYN Flood保护' prop='syn_flood'>
                    <el-input v-model="flowform.syn_flood" style="width:200px;margin-right: 10px;"></el-input>报文/秒
                  </el-form-item>
                  <!-- <el-form-item label='SYN Flood单机保护' prop='syn_flood_single_server'>
                    <el-input v-model="flowform.syn_flood_single_server" style="width:200px;margin-right: 10px;"></el-input>报文/秒
                  </el-form-item>
                  <el-form-item label='ACK&RST Flood保护' prop='ack_rst_flood'>
                    <el-input v-model="flowform.ack_rst_flood" style="width:200px;margin-right: 10px;"></el-input>报文/秒
                  </el-form-item> -->
                  <el-form-item label='UDP保护触发' prop='udp_protect_trigger'>
                    <el-input v-model="flowform.udp_protect_trigger" style="width:200px;margin-right: 10px;"></el-input>Mbps
                  </el-form-item>
                  <!-- <el-form-item label='ICMP保护触发' prop='icmp_protect_trigger'>
                    <el-input v-model="flowform.icmp_protect_trigger" style="width:200px;margin-right: 10px;"></el-input>报文/秒
                  </el-form-item> -->
                </el-form>
              </div>
              <!-- <div class="configModule">
                <p class="configTitle">其他防护策略</p>
                <el-form ref="otherform" :model="otherform" label-width="150px">
                  <el-form-item label="黑白名单策略">
                    <el-checkbox-group v-model="otherform.type">
                      <el-checkbox label="黑名单" name="type" key="1"></el-checkbox>
                      <el-checkbox label="白名单" name="type" key='2'></el-checkbox>
                    </el-checkbox-group>
                  </el-form-item>
                </el-form>
              </div> -->
            </el-col>
            <el-col :span='12'>
              <div class="configModule">
                <p class="configTitle">连接防护策略</p>
                <el-form ref="connectform" :model="connectform" label-width="180px" :rules="rules">
                  <!-- <el-form-item label='TCP连接数保护' prop='tcp_conn_protect_in'>
                    <el-input v-model="connectform.tcp_conn_protect_in" style="width:200px;margin-right: 10px;"></el-input>输入/主机
                  </el-form-item>
                  <el-form-item label='' prop='tcp_conn_protect_out'>
                    <el-input v-model="connectform.tcp_conn_protect_out" style="width:200px;margin-right: 10px;"></el-input>输出/主机
                  </el-form-item>
                  <el-form-item label='' prop='tcp_conn_protect_ip'>
                    <el-input v-model="connectform.tcp_conn_protect_ip" style="width:200px;margin-right: 10px;"></el-input>/IP
                  </el-form-item>
                  <el-form-item label='TCP连接频率保护' prop='tcp_frequecy_protect'>
                    <el-input v-model="connectform.tcp_frequecy_protect" style="width:200px;margin-right: 10px;"></el-input>秒
                  </el-form-item> -->
                  <el-form-item label='TCP空闲超时' prop='tcp_idle_expire_time'>
                    <el-input v-model="connectform.tcp_idle_expire_time" style="width:200px;margin-right: 10px;"></el-input>秒
                  </el-form-item>
                  <!-- <el-form-item label='UDP连接数量保护' prop='udp_conn_protect'>
                    <el-input v-model="connectform.udp_conn_protect" style="width:200px;margin-right: 10px;"></el-input>/主机
                  </el-form-item>
                  <el-form-item label='UDP连接空闲超时' prop='udp_frequecy_protect'>
                    <el-input v-model="connectform.udp_frequecy_protect" style="width:200px;margin-right: 10px;"></el-input>秒
                  </el-form-item>
                   <el-form-item label='ICMP连接空闲超时' prop='icmp_frequecy_protect'>
                    <el-input v-model="connectform.icmp_frequecy_protect" style="width:200px;margin-right: 10px;"></el-input>秒
                  </el-form-item> -->
                </el-form>
              </div>
            </el-col>
            <el-col :span='24' style="text-align: right">
              <el-button type="primary" @click="setHostForm('flowform', 'connectform')">保 存</el-button>
            </el-col>
          </el-row>
        </div>
		</div>
</template>
<script>
import controlService from 'services/controlService'
export default {
  data () {
    const checkNum = (rule, value, callback) => {
      if (!String(value).trim().length) {
        return callback(new Error('不能为空'))
      }
      let reg = /^(0|[1-9][0-9]*)$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('请输入非负整数'))
      } else {
        callback()
      }
    }
    return {
      isSubmiting: false,
      operform: {
        control: 'bypass'
      },
      protform: {
        time: ''
      },
      flowform: {
        syn_flood: 0,
        // syn_flood_single_server: 0,
        // ack_rst_flood: 0,
        udp_protect_trigger: 0
        // icmp_protect_trigger: 0
      },
      connectform: {
        // tcp_conn_protect_in: 0,
        // tcp_conn_protect_out: 0,
        // tcp_conn_protect_ip: 0,
        // tcp_frequecy_protect: 0,
        // tcp_idle_expire_time: 0,
        udp_conn_protect: 0
        // udp_frequecy_protect: 0,
        // icmp_frequecy_protect: 0
      },
      otherform: {
        type: []
      },
      host_param: '0',
      rules: {
        time: [{ validator: checkNum, trigger: 'blur' }],
        syn_flood: [{ validator: checkNum, trigger: 'blur' }],
        // syn_flood_single_server: [{ validator: checkNum, trigger: 'blur' }],
        // ack_rst_flood: [{ validator: checkNum, trigger: 'blur' }],
        udp_protect_trigger: [{ validator: checkNum, trigger: 'blur' }],
        // icmp_protect_trigger: [{ validator: checkNum, trigger: 'blur' }],
        // tcp_conn_protect_in: [{ validator: checkNum, trigger: 'blur' }],
        // tcp_conn_protect_out: [{ validator: checkNum, trigger: 'blur' }],
        // tcp_conn_protect_ip: [{ validator: checkNum, trigger: 'blur' }],
        // tcp_frequecy_protect: [{ validator: checkNum, trigger: 'blur' }],
        tcp_idle_expire_time: [{ validator: checkNum, trigger: 'blur' }]
        // udp_conn_protect: [{ validator: checkNum, trigger: 'blur' }],
        // udp_frequecy_protect: [{ validator: checkNum, trigger: 'blur' }],
        // icmp_frequecy_protect: [{ validator: checkNum, trigger: 'blur' }]
      }
    }
  },
  created () {
    this.getGlobalData('1|2|3', 0)
  },
  methods: {
    getGlobalData (t, gether) {
      controlService.getGlobalData(t, gether)
        .then((res) => {
          if (res.errcode === 0) {
            let data = res['3'].split('|')
            
            this.flowform = {
              syn_flood: data[0],
              // syn_flood_single_server: listArr[1],
              // ack_rst_flood: listArr[2],
              udp_protect_trigger: data[1]
              // icmp_protect_trigger: listArr[4]
            } 
            this.connectform = {
              // tcp_conn_protect_in: listArr[5],
              // tcp_conn_protect_out: listArr[6],
              // tcp_conn_protect_ip: listArr[7],
              // tcp_frequecy_protect: listArr[8],
              tcp_idle_expire_time: data[2]
              // udp_conn_protect: listArr[10],
              // udp_frequecy_protect: listArr[11],
              // icmp_frequecy_protect: listArr[12]
            }
            // switch (listArr[13]) {
            // case '0':
            //   this.otherform.type = []
            //   break
            // case '1':
            //   this.otherform.type = ['黑名单']
            //   break
            // case '2':
            //   this.otherform.type = ['白名单']
            //   break
            // case '3':
            //   this.otherform.type = ['黑名单', '白名单']
            //   break  
            // default:
            //   this.otherform.type = []
            //   break
            // }

            // 如果是单独配置t=3的情况
            if (t === '3') return
            this.operform.control = res['1']
            // 系统防护参数, 屏蔽时间
            this.protform.time = res['2']
          }
        })
    },
    getherChange (val) {
      this.getGlobalData('3', val)
    },
    setSysForm (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          let sentdata = {1: this.operform.control, 2: this.protform.time}
          controlService.setGlobalData('1|2', sentdata)
            .then((res) => {
              if (res.errcode === 0) {
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '修改成功'
                })
              }
            })
        }
      })
    },
    setHostForm (formName, formName1) {
      let flowStatus = false
      let connStatus = false
      this.$refs[formName].validate((valid) => {
        flowStatus = valid
      })
      this.$refs[formName1].validate((valid) => {
        connStatus = valid
      })
      if (flowStatus && connStatus) {
        // let otherType = 0
        // for (let v of this.otherform.type) {
        //   if (v === '黑名单') {
        //     otherType = 1
        //   } else {
        //     otherType = 2
        //   }
        // }
        // if (this.otherform.type.length === 2) {
        //   otherType = 3
        // }
        // let sentdata = {3: `${this.host_param}|${this.flowform.syn_flood}|${this.flowform.syn_flood_single_server}|` +
        // `${this.flowform.ack_rst_flood}|${this.flowform.udp_protect_trigger}|${this.flowform.icmp_protect_trigger}|` +
        // `${this.connectform.tcp_conn_protect_in}|${this.connectform.tcp_conn_protect_out}|${this.connectform.tcp_conn_protect_ip}|` +
        // `${this.connectform.tcp_frequecy_protect}|${this.connectform.tcp_idle_expire_time}|${this.connectform.udp_conn_protect}|` +
        // `${this.connectform.udp_frequecy_protect}|${this.connectform.icmp_frequecy_protect}|${otherType}`}
        let sentdata = {
          3: `${this.host_param}|${this.flowform.syn_flood}|${this.flowform.udp_protect_trigger}|${this.connectform.tcp_idle_expire_time}`
        }
                           
        controlService.setGlobalData('3', sentdata)
          .then((res) => {
            if (res.errcode === 0) {
              this.$message({
                type: 'success',
                duration: 1500,
                message: '修改成功'
              })
            }
          })
      }
    }
    // setNetAddr (formName) {
    //   this.$refs[formName].validate((valid) => {
    //     if (valid) {
    //       this.$refs[formName].clearValidate()// 清除校验结果
    //       this.isSubmiting = true
    //       let sentdata = {
    //         '1': `${this.formData.addrip}/${this.formData.addrmask}|` +
    //         `${this.formData['1ip']}/${this.formData['1mask']}|` +
    //         `${this.formData['2ip']}/${this.formData['2mask']}|` +
    //         `${this.formData.sship}/${this.formData.sshmask}` + 
    //         `|${this.formData.getway}|${this.formData.dnsF}|${this.formData.dnsS}`
    //       }
    //       controlService.setData('1', sentdata)
    //         .then((res) => {
    //           this.isSubmiting = false
    //           if (res.errcode === 0) {
    //             this.$message({
    //               showClose: true,
    //               message: '保存成功',
    //               type: 'success',
    //               duration: 1000
    //             })
    //             this.getNetAddr()
    //           }
    //         })
    //         .fail(() => {
    //           this.isSubmiting = false
    //         })
    //     }
    //   })
    // }
  }
}
</script>
<style scoped lang="scss">
#globalconfig .el-form-item__content{
  display: flex;
  justify-content: flex-start;
}
.configModule{
  padding: 20px 10px;
}
.configTitle{
  text-align: left;
  color: #7a7f8a;
  line-height: 30px;
  font-size: 15px;
  padding: 0 5px;
  margin-bottom: 15px;
  border-bottom: 1px solid #b4bccc; 
}
</style>