<template>
  <div id='globalconfig'>   
    <el-row>
      <el-col :span='24'>
        <h2 class="config-title" style="padding-bottom: 10px;">
          网络地址
        </h2>
        <el-form :inline="true" :model="netAddrFormData" :rules="rules" ref="net_address_form">
          <el-form-item label='默认网关' prop='gateway' style="margin-left: 5px">
            <el-input style="width:200px;margin-right: 20px;" v-model="netAddrFormData.gateway"></el-input>
          </el-form-item>
          <el-form-item label='首选DNS服务器' prop='primary_dns'>
            <el-input style="width:200px;margin-right: 20px;" v-model="netAddrFormData.primaryDns"></el-input>
          </el-form-item>
          <el-form-item label='备选DNS服务器' prop='second_dns'>
            <el-input style="width:200px;margin-right: 20px;" v-model="netAddrFormData.secondDns"></el-input>
          </el-form-item>
        </el-form>
        <el-col :span='24' style="text-align: right">
          <el-button type="primary" @click="setNetAddr('net_address_form')" :loading="is_submitting_net_addr">保 存</el-button>
        </el-col>
      </el-col>
      
      <el-col :span='24' style="position: relative; margin-top: 40px">
        <h2 class="config-title" style="padding-bottom: 10px;">
          汇聚网卡列表
        </h2>
        <div style="position: absolute; top: 0; right: 0">
          <el-button type="primary" @click="showEthGrpAddDialog">添加</el-button>
          <el-button type="primary" @click="handleRemoveEthGrp">删除</el-button>
        </div>
        <el-table 
          border 
          :data="ethData.ethgrpData"
          @selection-change="handleEthgrpSelectionChange">
          <el-table-column type='selection' align='center' width='50px'></el-table-column>
          <el-table-column type='index' label='序号' align='center' width='50px'></el-table-column>
          <el-table-column label="网卡标识" prop="name"></el-table-column>
          <el-table-column label="IP地址">
            <template slot-scope="scope">
                <template v-if="scope.row.ips.length">
                  <div v-for="(ip, index) in scope.row.ips" :key="index">
                    {{ip}}
                  </div>
                </template>
                <template v-else>
                  -
                </template>
            </template>
          </el-table-column>
          <el-table-column label="MAC地址" prop="mac"></el-table-column>
          <el-table-column label="绑定网卡">
            <template slot-scope="scope">
              <template v-if="scope.row.binds.length">
                <div v-for="(bind, index) in scope.row.binds" :key="index">
                  {{bind.name}}：{{eth_mode_options_text[bind.mode]}}
                </div>
              </template>
              <template v-else>
                -
              </template>
            </template>
          </el-table-column>
          <el-table-column label="负载均衡模式" prop="load_mode">
            <template slot-scope="scope">
              {{load_balance_mode_options_text[scope.row.load_mode]}}
            </template>
          </el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button @click="showEthGrpEditDialog(scope.row)">配置</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>

      <!-- 汇聚物理网卡配置, 编辑，增加 -->
      <el-dialog 
        :title="ethgrpFormData.type === 'add' ? ethgrpAddDialogTitle : ethgrpEditDialogTitle" 
        :visible="ethgrpDialogVisible" 
        width="545px" 
        @close="resetEthGrpEditDialog('ethgrpEditForm')">
        <el-row style="margin-bottom: 15px">               
          <el-alert title="IP只能为掩码格式，比如10.22.1.1/23；掩码范围是[16, 32]" type="info" :closable="false" show-icon>
          </el-alert>
        </el-row>
        <div 
          v-loading='ethgrpDialogLoading' 
          element-loading-text="正在配置中..."
          element-loading-background="rgba(255, 255, 255, 1)">
          <el-form 
            label-width="100px" 
            :label-position="'center'"
            :model="ethgrpFormData" 
            ref="ethgrpEditForm" 
            @submit.native.prevent>
            <el-form-item 
              v-for="(item, index) in ethgrpFormData.ips"
              :label="'IP地址' + index"
              :key="index"
              :prop="'ips.' + index + '.ip'"
              >

              <el-input v-model="item.ip" style="width:200px"></el-input>

              <el-button @click.prevent="ethgrpRemoveIpInput(item)" style="vertical-align: -2px;">删除</el-button>
              
            </el-form-item>
            <div style="margin-top: 0px; margin-bottom: 15px; margin-left: 100px; width: 200px">
              <el-button type="primary" style="width:100%" @click="ethgrpAddIpInput">增加IP配置</el-button>
            </div>

            <el-form-item
              label="MAC地址"
              prop="mac">
              <el-select 
                v-model="ethgrpFormData.ethgrp_mac_mode" 
                placeholder="请选择" 
                style="width: 100px">
                <el-option 
                  v-for="item in ethgrp_mac_mode_options"
                  :label="item.label"
                  :value="item.value"
                  :key="item.value">
                </el-option>
              </el-select>

              <el-input v-if="ethgrpFormData.ethgrp_mac_mode === 1" v-model="ethgrpFormData.mac" placeholder="请输入" style="width: 200px"></el-input>
            </el-form-item>

            <div style="margin-bottom: 30px; padding: 0 5px;font-size: 14px; color: #5a5e66;">
              <h2 style="padding-bottom: 5px; font-size: 14px; border-bottom: 1px solid; color: #5a5e66;">绑定</h2>

              <div style="display: flex; justify-content: space-around;">
                <el-table 
                  border
                  :data="ethgrpFormData.allEthData"
                  highlight-current-row
                  @current-change="handleAllEthTableCurChange"
                  class="all-eth-table"
                  style="flex: 0 0 100px; margin-top: 15px;width:100%"
                  max-height="300">
                  <el-table-column prop="name" label="网卡列表" style="background:red"></el-table-column>
                </el-table>

                <div style="display: flex; flex-direction: column; justify-content: center; width: 56px;">
                  <el-button @click="addFromAllEth">增加</el-button>
                  <el-button style="margin: 15px 0 0 0" @click="removeFromEthMode">删除</el-button>
                </div>

                <el-table
                  border
                  :data="ethgrpFormData.ethModeData"
                  highlight-current-row
                  @current-change="handleEthModeTableCurChange"
                  style="flex: 0 0 200px; margin-top: 15px;"
                  max-height="300">

                  <el-table-column prop="name" label="网卡"></el-table-column>
                  <el-table-column prop="mode" label="模式">
                    <template slot-scope="scope">
                      <el-select v-model="scope.row.mode">
                        <el-option
                          v-for="item in eth_mode_options"
                          :label="item.label"
                          :value="item.value"
                          :key="item.value">
                        </el-option>
                      </el-select>
                    </template>
                  </el-table-column>
                </el-table>
              </div>
            </div>

            <el-form-item
              label="负载均衡模式"
              prop="mac">
              
              <el-select v-model="ethgrpFormData.load_balance_mode" placeholder="请选择" style="width: 110px">
                <el-option 
                  v-for="item in load_balance_mode_options"
                  :label="item.label"
                  :value="item.value"
                  :key="item.value">

                </el-option>
              </el-select>
            </el-form-item>
          </el-form>
          <div style="text-align:right">
            <el-button type="primary" @click="submitEthGrpForm('ethgrpEditForm')">确 定</el-button>
            <el-button @click="resetEthGrpEditDialog('ethgrpEditForm')">取 消</el-button>
          </div>
        </div>
      </el-dialog>

      <el-col :span='24' style="position: relative; margin-top: 40px">
        <h2 class="config-title" style="padding-bottom: 10px;">
          物理网卡列表
        </h2>
        <!-- <div style="position: absolute; top: 0; right: 0">
          <el-button type="primary" v-if="availableEth.length" @click="showEthAddDialog">添加</el-button>
          <el-button type="primary" @click="deletePhyNetCard">删除</el-button>
        </div> -->
        <el-table border :data='ethData.allEthData'>
          <!-- <el-table-column type='selection' align='center' width='50px'></el-table-column> -->
          <el-table-column type='index' label='序号' align='center' width='50px'></el-table-column>
          <el-table-column label="网卡标识" prop="name"></el-table-column>
          <el-table-column label="IP地址" prop="ips">
            <template slot-scope="scope">
                <template v-if="scope.row.ips.length">
                  <div v-for="(ip, index) in scope.row.ips" :key="index">
                    {{ip}}
                  </div>
                </template>
                <template v-else>
                  -
                </template>
            </template>
          </el-table-column>
          <el-table-column label="操作">
            <template slot-scope="scope">
              <el-button @click="showEthEditDialog(scope.row.name, scope.row)">配置</el-button>
            </template>
          </el-table-column>
          <el-table-column label="管理口">
            <template slot-scope="scope">
              <el-button v-if='scope.row.name === mgpName' class="mgp-red" disabled>当前管理口</el-button>
              <el-button v-else @click="setMgp(scope.row)" :loading="scope.row.loading">设置为管理口</el-button>
            </template>
          </el-table-column>
        </el-table>
      </el-col>
    </el-row>

    <!-- 添加物理网卡 -->
    <!-- <el-dialog :title="ethAddDialogTitle" :visible="ethAddDialogVisible" width="545px" @close="resetEthAddDialog('ethNetAddForm')">
      <el-row style="margin-bottom: 15px">               
        <el-alert title="提示" type="info" :closable="false" show-icon>
        </el-alert>
      </el-row>
      <el-form label-width="120px" :model="ethAddFormData" :inline="true" ref="ethNetAddForm" @submit.native.prevent>
        <el-form-item label="网卡">
          <el-select v-model="ethAddFormData.eth" multiple placeholder="请选择">
            <el-option
              v-for="item in availableEth"
              :key="item.value"
              :label="item.label"
              :value="item.value">
            </el-option>
          </el-select>
        </el-form-item>
      </el-form>
      <div style="text-align:right">
        <el-button type="primary" @click="submitEthAddForm('ethNetAddForm')">确 定</el-button>
        <el-button @click="resetEthAddDialog('ethNetAddForm')">取 消</el-button>
      </div>
    </el-dialog> -->

    <!-- 物理网卡配置修改 -->
    <el-dialog 
      :title="ethEditDialogTitle" 
      :visible="ethEditDialogVisible" 
      width="580px" 
      @close="resetEthEditDialog('ethEditForm')"
      >
      <el-row style="margin-bottom: 15px">               
        <el-alert title="IP只能为掩码格式，比如10.22.1.1/23；掩码范围是[16, 24]" type="info" :closable="false" show-icon>
        </el-alert>
      </el-row>
      <div 
        v-loading='ethEditDialogLoading' 
        element-loading-text="正在配置中..."
        element-loading-background="rgba(255, 255, 255, 1)">
        <el-form 
          label-width="120px" 
          :model="ethEditFormData" 
          ref="ethEditForm" 
          @submit.native.prevent>
          <el-form-item 
            :label="'IP地址' + index"
            v-for="(item, index) in ethEditFormData.ips"
            :key="index"
            :prop="'ips.' + index + '.ip'"
            style="width:100%"
            >

            <el-input v-model="item.ip" style="width:220px" placeholder="请输入"></el-input>
            <el-button @click.prevent="ethRemoveIpInput(item)">删除</el-button>
            <template v-if='canSetBroadcastIP'>
              <el-button v-if='item.ip === mgpIp'  class="mgp-red" disabled>当前广播IP</el-button>
              <el-button v-else @click="setBoardcast(item)" :id='item.ip' :loading="item.loading" v-show="!item.isNew">设置为广播IP</el-button>
            </template>

          </el-form-item>

          <div>
            <el-button type="primary" @click="ethAddIpInput" style="width:223px;margin-left:120px">增加IP配置</el-button>
          </div>
        </el-form>
        <div style="text-align:right">
          <el-button type="primary" @click="submitEthEditForm('ethEditForm')">确 定</el-button>
          <el-button @click="resetEthEditDialog('ethEditForm')">取 消</el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import controlService from 'services/controlService'
export default {
  data () {
    const checkMask = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('掩码不能为空'))
      }
      let reg = /^(254|252|248|240|224|192|128|0)\.0\.0\.0|255\.(254|252|248|240|224|192|128|0)\.0\.0|255\.255\.(254|252|248|240|224|192|128|0)\.0|255\.255\.255\.(254|252|248|240|224|192|128|0)$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('掩码格式不正确'))
      } else {
        callback()
      }
    }
    return {
      regHandle: (callback, status) => {
        if (!status) {
          callback(new Error('IP格式错误'))
        } else {
          callback()
        }
      },
      checkIp: (rule, value, callback) => {
        if (!value) {
          return callback(new Error('IP不能为空'))
        }

        let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/

        // 192.168.1.1-192.168.2.1
        // if (value.indexOf('-') !== -1) {
        //   let ips = value.split('-')
        //   if (ips.length) {
        //     let status = ips.every((ip) => {
        //       return regIp.test(ip)
        //     })
        //     regHandle(callback, status)
        //     return true
        //   }
        // }

        // 192.168.1.1/32
        if (value.indexOf('/') !== -1) {
          let ips = value.split('/')
          if (ips.length) {
            let status = regIp.test(ips[0]) && /^\d+$/.test(ips[1]) && Number(ips[1]) >= 16 && Number(ips[1]) <= 32
            this.regHandle(callback, status)
            return
          }
        } else {
          this.regHandle(callback, false)
          return
        }

        // 如果只是一个ip
        let status = regIp.test(value)
        this.regHandle(callback, status)
      },
      ethEditDialogLoading: false,
      ethgrpDialogLoading: false,
      ethgrpDialogVisible: false,
      is_submitting_net_addr: false,
      ethAddDialogVisible: false,
      ethAddDialogTitle: '添加网卡',
      ethEditDialogVisible: false,
      ethEditDialogTitle: '',
      ethgrpEditDialogTitle: '修改网卡配置',
      ethgrpAddDialogTitle: '添加网卡配置',
      netAddrFormData: {
        gateway: '',
        primaryDns: '',
        secondDns: ''
      },
      phyNetCardFormData: {
        domains: [
          {
            value: '',
            key: Date.now()
          }
        ]
      },
      
      // 汇聚网卡编辑表单数据
      ethgrpFormData: {
        mac: '',
        ethgrp_mac_mode: 0,
        ips: [],
        all_eth: [],
        ethModeData: [] // 汇聚网卡中，网卡模式数据
      },
      ethgrp_mac_mode_options: [
        {
          label: '随机生成',
          value: 0
        },
        {
          label: '自定义',
          value: 1
        }
      ],
      load_balance_mode_options: [
        {
          label: '轮询',
          value: 'round-robin'
        },
        {
          label: 'MAC哈希',
          value: 'xor-mac'
        },
        {
          label: 'IP哈希',
          value: 'xor-ip'
        }
      ],
      load_balance_mode_options_text: {
        'round-robin': '轮询',
        'xor-mac': 'MAC哈希',
        'xor-ip': 'IP哈希'
      },

      ethAddFormData: { // 添加物理网卡数据
        eth: []
      }, 
      ethEditFormData: {
        name: '',
        ips: [
          {ip: ''}
        ]
      },
      ethData: {
        ethnetData: [], // 物理网卡列表数据
        ethgrpData: [], // 汇聚网卡列表数据
        all_eth: []
      },
      availableEth: [],
      ethSelectArr: [], // 物理网卡已选择的数据
      ethgrpSelectedArr: [], // 网卡汇聚已选择的数据

      allEthTableData: [], // 汇聚网卡中，所有网卡
      
      exchangeRow: {
        name: ''
      }, // 用于交换的数据
      eth_mode_options: [
        {
          label: '被动',
          value: 'passive'
        },
        {
          label: '手动',
          value: 'static'
        },
        {
          label: '主动',
          value: 'active'
        }
      ],

      eth_mode_options_text: {
        'passive': '被动',
        'static': '手动',
        'active': '主动'
      },
      rules: {
        gateway: [{ validator: this.checkIP, trigger: 'blur' }],
        primaryDns: [{ validator: this.checkIP, trigger: 'blur' }],
        secondDns: [{ validator: this.checkIP, trigger: 'blur' }]
      },
      mgpName: "",
      mgpIp: "",
      canSetBroadcastIP: false,

    }
  },
  mounted () {
    this.getNetAddr()
	this.getEthNet()
	this.getMgpp()
  },
  methods: {
    getMgpp(){
        controlService.getMgp().then((res) => {
            // res['3'] = "eth0_0|1.1.1.1"
            // res['3'] = "1.1.1.1"
            // res['3'] = "eth0_0"
            if (res.errcode === 0) {
                if (res["3"].indexOf("|") != -1) {
                let arr = res["3"].split("|")
                this.mgpName = arr[0]
                this.mgpIp = arr[1]
                } else if (res["3"].indexOf("eth") != -1) {
                this.mgpName = res["3"]
                } else {
                this.mgpIp = res["3"]
                }
            }
        })
    },
    // 设置管理口
    setMgp(row) {
      row.loading = true
      let params = {"3": row.name}
      controlService.setMgp(params, "mgt")
      .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              message: '设置' + row.name + '为管理口成功',
              type: 'success'
            })
            this.mgpName = row.name
          }
        })
        .always(() => {
            row.loading = false
        })
    },
    // 设置管理口广播ip
    setBoardcast(item) {
        this.checkIp(null, item.ip, (err) => {
            if (err) {
                this.$message({
                    message: err.message,
                    type: 'error'
                })
            } else {
                item.loading = true
                let params = {"3": item.ip}
                controlService.setMgp(params, "bro")
                .then((res) => {
                    if (res.errcode === 0) {
                        this.$message({
                        message: '设置' + item.ip + '为广播ip成功',
                        type: 'success'
                        })
                        this.mgpIp = item.ip
                    }
                })
                .always(() => {
                    item.loading = false
                })
            }
        })
    },
    compare (key) {
      return (a, b) => {
        return a[key] - b[key]
      }
    },
    getEthNet () {
      controlService.getEthNet()
        .then((res) => {
          if (res.errcode === 0) {
            // 汇聚网卡列表数据
            this.ethData.ethgrpData = res['2'].ethgrp.map((item) => {
              item.no = item.name.replace(/[^\d+]/g, '') * 1
              let binds = item.binds.map((bind) => {
                let item = bind.split('|')
                let numMatch = item[0].match(/^eth(\d+)_0$/)
                if (numMatch) {
                  return {
                    no: numMatch[1] * 1, 
                    name: item[0],
                    mode: item[1]}
                } else {
                  return {
                    no: -1, 
                    name: item[0],
                    mode: item[1]
                  }
                }
              })
              .sort(this.compare('no'))
              .map((bind) => {
                return {
                  name: bind.name,
                  mode: bind.mode
                }
              })
              item.binds = binds
              return item
            })
            .sort(this.compare('no'))

            // 物理网卡列表数据
            // this.ethData.ethnetData = res['2'].ethnet

            // 对物理网卡数据进行排序
            // res['2'].ethnet = [
            //   {
            //     name: 'eth0_0', 
            //     ips: []
            //   }, 
            //   {
            //     name: 'eth4_0', 
            //     ips: []
            //   }, 
            //   {
            //     name: 'eth3_0', 
            //     ips: ['192.168.100.10/24', '192.168.100.11/24']
            //   }
            // ]
            // 所有网卡数据
            this.ethData.allEthData = res['2'].ethnet.map((item) => {
              let numMatch = item.name.match(/^eth(\d+)_0$/)
              if (numMatch) {
                return {no: numMatch[1] * 1, data: item}
              } else {
                return {no: -1, data: item}
              }
            })
            .sort(this.compare('no'))
            .map((item) => {
                item.data.loading = false
                return item.data
            })
            // this.ethData.allEthData = res['2'].ethnet.map((item) => {
            //   return {
            //     name: item.name
            //   } 
            // })
            // 已配置过的网卡
            // let usedEths = this.ethData.ethnetData.map((item) => {
            //   return item.name
            // })

            // // 空闲可配置的网卡
            // this.availableEth = this.ethData.allEthData.filter((item) => {
            //   return !usedEths.includes(item)
            // }).map((item) => {
            //   return {
            //     label: item,
            //     value: item
            //   }
            // })
          }
        })
    },

    // 检查网卡配置IP加入聚合后不可访问，是否继续
    checkNeedConfirmForEthConfigIP (exchangeRow) {
      let ethnetData = this.ethData.allEthData
      let ethName = exchangeRow.name
      return ethnetData.some((item) => {
        return item.ips.length > 0 && ethName === item.name
      })
    },

    // 检查网卡已配置，是否继续
    checkNeedConfirmForEthConfigEth (exchangeRow) {
      let bindedEths = this.ethgrpFormData.bindedEths
      let ethName = exchangeRow.name
      return bindedEths.some((item) => {
        return ethName === item.name
      })
    },
    // 添加，删除网卡，左右两个列表
    exchangeEthConfig () {
      this.ethgrpFormData.ethModeData.push({
        name: this.exchangeRow.name,
        mode: 'passive'
      })

      let index = this.ethgrpFormData.allEthData.indexOf(this.exchangeRow)
      this.ethgrpFormData.allEthData.splice(index, 1)
      this.exchangeRow = {name: ''}
    },
    // 从全部网卡列表中添加网卡 到 网卡模式列表，绑定网卡
    addFromAllEth () {
      if (!this.exchangeRow || !this.exchangeRow.name || this.exchangeRow.mode) {
        return
      }

      if (this.checkNeedConfirmForEthConfigIP(this.exchangeRow)) {
        this.$confirm('当前网卡配置有ip，加入聚合后，ip将不可访问，是否继续？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
          callback: (action) => {
            if (action === 'confirm') {
              this.exchangeEthConfig()
            }
          }
        })
        return
      }

      if (this.checkNeedConfirmForEthConfigEth(this.exchangeRow)) {
        this.$confirm('当前网卡已配置，是否继续？', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning',
          callback: (action) => {
            if (action === 'confirm') {
              this.exchangeEthConfig()
            }
          }
        }) 
        return
      }

      // 正常情况
      this.exchangeEthConfig()
    },
    // 从网卡模式列表中删除绑定的网卡 
    removeFromEthMode () {
      if (!this.exchangeRow || !this.exchangeRow.name || !this.exchangeRow.mode) {
        return
      }
      this.ethgrpFormData.allEthData.push({
        name: this.exchangeRow.name
      })
      this.ethgrpFormData.allEthData = this.ethgrpFormData.allEthData.map((item) => {
        let numMatch = item.name.match(/^eth(\d+)_0$/)
        if (numMatch) {
          return {
            no: numMatch[1] * 1, 
            name: item.name
          }
        } else {
          return {
            no: -1, 
            name: item.name
          }
        }
      })
      .sort(this.compare('no'))
      .map((item) => {
        return {
          name: item.name
        }
      })
      let index = this.ethgrpFormData.ethModeData.indexOf(this.exchangeRow)
      this.ethgrpFormData.ethModeData.splice(index, 1)
      this.exchangeRow = {}
    },
    handleAllEthTableCurChange (row, oldRow) {
      this.exchangeRow = row
    },
    handleEthModeTableCurChange (row, oldRow) {
      this.exchangeRow = row
    },

    // 选择汇聚网卡列表复选框
    handleEthgrpSelectionChange (val) {
      this.ethgrpSelectedArr = val
    },
    // 删除汇聚网卡
    handleRemoveEthGrp (items) {
      if (this.ethgrpSelectedArr.length < 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '请选择要删除的配置'
        })
      } else {
        this.$confirm('此操作将删除当前所选汇聚网卡配置, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
        .then(() => {
          let names = this.ethgrpSelectedArr.map((item) => {
            return item.name
          })
      // 只读数据，不会对数据源做修改，只是用来提交数据
          let params = {
            '2': {
              ethgrp: this.ethData.ethgrpData.filter((item) => {
                return !names.includes(item.name)
              }).map((item) => {
                return {
                  name: item.name,
                  ips: item.ips,
                  binds: item.binds.map((item) => {
                    return `${item.name}|${item.mode}`
                  }),
                  load_mode: item.load_mode,
                  mac: item.mac
                }
              }),
              ethnet: this.ethData.allEthData
            }
          }

          controlService.updateOrCreateEthNet(params)
            .then((res) => {
              if (res.errcode === 0) {
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '删除成功'
                })

                this.getEthNet()
              }
            })
        })
        .catch(() => {})
      }
    },

    // 增加汇聚网卡
    showEthGrpAddDialog () {
      this.ethgrpDialogVisible = true
      this.ethgrpDialogLoading = false
      this.ethgrpFormData = {
        type: 'add',
        mac: '',
        ethgrp_mac_mode: 0,
        ips: [{ip: ''}],
        bindedEths: this.ethData.ethgrpData.reduce((arr, item) => {
          return arr.concat(item.binds)
        }, []),
        allEthData: this.ethData.allEthData.map((item) => {
          let numMatch = item.name.match(/^eth(\d+)_0$/)
          if (numMatch) {
            return {
              no: numMatch[1] * 1, 
              name: item.name
            }
          } else {
            return {
              no: -1, 
              name: item.name
            }
          }
        })
        .sort(this.compare('no'))
        .map((item) => {
          return {
            name: item.name
          }
        }),

        ethModeData: [],
        load_balance_mode: 'round-robin'
      }
    },

    // 编辑汇聚网卡
    showEthGrpEditDialog (row) {
      this.ethgrpEditDialogTitle = `${row.name} 配置修改`
      let bindeths = row.binds.map((item) => {
        return item.name
      })

      this.ethgrpDialogVisible = true
      this.ethgrpDialogLoading = false

      this.ethgrpFormData = {
        type: 'edit',
        name: row.name,
        mac: row.mac,
        ethgrp_mac_mode: row.mac ? 1 : 0,
        ips: row.ips.map((ip) => {
          return {
            ip: ip
          }
        }),

        bindedEths: this.ethData.ethgrpData.filter((item) => {
          return item.name !== row.name
        }).reduce((arr, item) => {
          return arr.concat(item.binds)
        }, []),
        
        allEthData: this.ethData.allEthData.filter((item) => {
          return !bindeths.includes(item.name)
        }).map((item) => {
          return {
            name: item.name
          }
        }),

        ethModeData: row.binds.map((item) => {
          return {
            name: item.name,
            mode: item.mode
          }
        }),
        load_balance_mode: row.load_mode
      }
    },
    resetEthGrpEditDialog (formName) {
      this.ethgrpDialogVisible = false
      this.$refs[formName].resetFields()
    },
    ethgrpAddIpInput () {
      this.ethgrpFormData.ips.push({
        ip: ''
      })
    },
    ethgrpRemoveIpInput (item) {
      let index = this.ethgrpFormData.ips.indexOf(item)
      if (index !== -1) {
        this.ethgrpFormData.ips.splice(index, 1)
      }
    },

    // 增加新的，要和原有的数据放在一块一起提交
    setupEthgrpAddData () {
      let ethgrpName = this.ethData.ethgrpData.length ? 'ethgrp' + (this.ethData.ethgrpData[this.ethData.ethgrpData.length - 1].no * 1 + 1)
                                                      : 'ethgrp0'

      let formBindNames = this.ethgrpFormData.ethModeData.map((item) => {
        return item.name
      })

      let dupEthgrpData = this.ethData.ethgrpData.map((item) => {
        return {
          name: item.name,
          mac: item.mac,
          load_mode: item.load_mode,
          ips: item.ips,
          binds: item.binds.filter((item) => {
            return formBindNames.indexOf(item.name) === -1
          }).map((item) => {
            return `${item.name}|${item.mode}`
          })
        }
      })
      let params = {
        '2': {
          ethgrp: [
            ...dupEthgrpData,
            {
              name: ethgrpName,
              mac: this.ethgrpFormData.ethgrp_mac_mode === 0 ? '' : this.ethgrpFormData.mac,
              load_mode: this.ethgrpFormData.load_balance_mode,
              ips: this.ethgrpFormData.ips.map((item) => { return item.ip }).filter((item) => {
                return item !== ''
              }),
              binds: this.ethgrpFormData.ethModeData.map((item) => {
                return `${item.name}|${item.mode}`
              })
            } 
          ],

          ethnet: this.ethData.allEthData
        }
      }

      return params
    },

    setupEthgrpEditData () {
      // 从数据源中找到要修改的汇聚网卡数据，深拷贝一份数据，不破坏原有的数据
      let formBindNames = this.ethgrpFormData.ethModeData.map((item) => {
        return item.name
      })
      let ethgrp = this.ethData.ethgrpData.map((item) => {
        if (item.name === this.ethgrpFormData.name) {
          return {
            name: item.name,
            mac: this.ethgrpFormData.ethgrp_mac_mode === 0 ? '' : this.ethgrpFormData.mac,
            load_mode: this.ethgrpFormData.load_balance_mode,
            ips: this.ethgrpFormData.ips.map((item) => { return item.ip }).filter((item) => {
              return item !== ''
            }),
            binds: this.ethgrpFormData.ethModeData.map((item) => {
              return `${item.name}|${item.mode}`
            })
          }
        } else {
          let dupItem = {
            name: item.name,
            mac: item.mac,
            load_mode: item.load_mode,
            ips: item.ips,
            binds: item.binds.filter((item) => {
              return formBindNames.indexOf(item.name) === -1
            }).map((item) => {
              return `${item.name}|${item.mode}`
            })
          }
          return dupItem
        }
      })
      let params = {
        '2': {
          ethgrp: ethgrp,
          ethnet: this.ethData.allEthData
        }
      }

      return params
    },

    // 提交增加，编辑汇聚网卡配置
    submitEthGrpForm (formName, type) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          let params = this.ethgrpFormData.type === 'add' ? this.setupEthgrpAddData() : this.setupEthgrpEditData()
          this.ethgrpDialogLoading = true
          controlService.updateOrCreateEthNet(params)
            .then((res) => {
              if (res.errcode === 0) {
                this.ethgrpDialogVisible = false
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '配置成功'
                })
                this.getEthNet()
              } else {
                console.log(res.errmsg)
              }
            })
            .always(() => {
              this.ethgrpDialogLoading = false
            })
        }
      })
    },
    // 选择要删除的物理网卡, 暂时不做
    // handlePhyNetCardSelect (val) {
    //   this.ethSelectArr = val.map((item) => item.name)
    // },
    // deletePhyNetCard () {
    //   this.ethData.ethnetData = this.ethData.ethnetData.filter((item) => {
    //     return !this.ethSelectArr.includes(item.name)
    //   })
    // },
    showEthAddDialog () {
      this.ethAddDialogVisible = true
    },
    resetEthAddDialog (formName) {
      this.ethAddDialogVisible = false
      this.$refs[formName].resetFields()
      // this.singleFormData = {port: '', /* attack_frequecy_protect: '', */ conn_limit: '', protection_mode: [], status_port: ''}
    },

    // 添加物理网卡，暂时不做
    // submitEthAddForm () {
    //   this.ethData.ethnetData = this.ethData.ethnetData.concat(this.ethAddFormData.eth.map((item) => {
    //     return {
    //       name: item, 
    //       ips: []
    //     }
    //   }))

    //   this.ethAddDialogVisible = false
    // },

    // 编辑物理网卡配置
    showEthEditDialog (ethName, row) {
      this.ethEditDialogVisible = true
      this.canSetBroadcastIP = (row.name == this.mgpName);
      this.ethEditDialogTitle = `${ethName} 配置`
      let ips = row.ips.map((ip) => {
        return {
          ip: ip,
          loading: false
        }
      })
      this.ethEditFormData = {
        name: row.name,
        ips: ips.length ? ips : [{ip: ''}]
      }
    },
    // 动态删除物理网卡配置中IP输入框
    ethRemoveIpInput (item) {
      let index = this.ethEditFormData.ips.indexOf(item)
      if (index !== -1) {
        this.ethEditFormData.ips.splice(index, 1)
      }
    },
    // 动态增加物理网卡配置中IP输入框
    ethAddIpInput () {
      this.ethEditFormData.ips.push({
        ip: '',
        isNew: true
      })
    },
    resetEthEditDialog () {
      this.ethEditDialogVisible = false
    },

    // 编辑物理网卡配置
    submitEthEditForm (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
           // 每个功能创建不可变params， 不改变数据源
          let params = {
            2: {
              ethgrp: this.ethData.ethgrpData.map((item) => {
                return {
                  name: item.name,
                  ips: item.ips,
                  binds: item.binds.map((item) => {
                    return `${item.name}|${item.mode}`
                  }),
                  load_mode: item.load_mode,
                  mac: item.mac
                }
              }),
              ethnet: this.ethData.allEthData.map((item) => {
                if (item.name === this.ethEditFormData.name) {
                  return {
                    name: item.name,
                    ips: this.ethEditFormData.ips.map((item) => {
                      return item.ip
                    }).filter((item) => {
                      return item !== ''
                    })
                  }
                } else {
                  return {
                    name: item.name,
                    ips: item.ips.filter((item) => {
                      return item !== ''
                    })
                  }
                }
              })
            }
          }
          this.ethEditDialogLoading = true
          controlService.updateOrCreateEthNet(params)
            .then((res) => {
              if (res.errcode === 0) {
                this.ethEditDialogVisible = false
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '配置成功'
                })

                this.getEthNet()
              } else {
                console.log(res.errmsg)
              }
            })
            .always(() => {
              this.ethEditDialogLoading = false
            })
        } 
      })
     
      // 如果成功, 重新请求接口获取数据

      // 如果失败，数据没有发送成功，数据源也不会改变。每次点击配置，数据又会和数据源一致
      // console.log(this.ethEditFormData.ips)

      // this.$refs[formName].resetFields()
    },

    // 网络地址配置
    getNetAddr () {
      controlService.getNetAddr()
        .then((res) => {
          if (res.errcode === 0) {
            let data = res['1'].split('|')
            this.netAddrFormData.gateway = data[0]
            this.netAddrFormData.primaryDns = data[1]
            this.netAddrFormData.secondDns = data[2]
          }
        })
    },
    setNetAddr (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.$refs[formName].clearValidate()// 清除校验结果
          this.is_submitting_net_addr = true
          let sentdata = {
            '1': `${this.netAddrFormData.gateway}|${this.netAddrFormData.primaryDns}|${this.netAddrFormData.secondDns}`
          }
          controlService.setNetAddrData(sentdata)
            .then((res) => {
              this.is_submitting_net_addr = false
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  message: '保存成功',
                  type: 'success',
                  duration: 1000
                })
                this.getNetAddr()
              } else {
                console.log(res.errmsg)
              }
            })
            .fail(() => {
              this.is_submitting_net_addr = false
            })
        }
      })

      // let data = {
      //   2: {
      //     ethgrp: [
      //       {
      //         name: 'ethgrp0',
      //         mac: '00:00:00:00:00:01',
      //         load_mode: 'xor-ip',
      //         ips: ['192.168.100.100/24'],
      //         binds: ['eth0_0|passive', 'eth1_0|passive']
      //       },
      //       {
      //         name: 'ethgrp1',
      //         mac: '00:00:00:00:00:02',
      //         load_mode: 'xor-ip',
      //         ips: ['192.168.100.101/24'],
      //         binds: ['eth2_0|active']
      //       },
      //       {
      //         name: 'ethgrp5', 
      //         mac: '02:09:c0:76:29:8b', 
      //         load_mode: 'round-robin', 
      //         ips: ['192.168.100.103/24'], 
      //         binds: []
      //       }
      //     ], 
      //     ethnet: [
      //       {
      //         name: 'eth0_0', 
      //         ips: []
      //       }, 
      //       {
      //         name: 'eth3_0', 
      //         ips: ['192.168.100.10/24', '192.168.100.11/24']
      //       }
      //     ], 
      //     all_eth: ['eth0_0', 'eth1_0', 'eth2_0', 'eth3_0']
      //   }
      // }
    }
  }
}
</script>
<style>
.all-eth-table tr {
    cursor: pointer;
}
</style>
<style scoped lang="scss">

#globalconfig .el-form-item__content{
  display: flex;
  justify-content: flex-start;
}
.config-title{
  text-align: left;
  color: #7a7f8a;
  line-height: 30px;
  font-size: 15px;
  padding: 0 5px;
  margin-bottom: 15px;
  border-bottom: 1px solid #b4bccc; 
}
.mgp-red{
  color: #FF8E7A;
  border-color: #ffddd7;
  background-color: #fff4f2;
}
</style>