<style>
  #udp .popper__arrow{
    left: 70px;
    display: none !important;
  }
  #udp .el-form {
    padding-left: 80px; 
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
  }
  #udp .el-checkbox + .el-checkbox{
    margin-left: 0;
  }
</style>

<template>
  <div id='udp'>
    <div class="protTitle">
      <div>
        <span style="margin-right: 10px;">端口保护设置集</span>
        <el-select v-model="tcp_port_protect" style="width: 70px;position: relative;" @change='getherChange'>
          <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
        </el-select>
      </div>
      <div>
        <el-button type='primary' @click="add">添加</el-button>
        <el-button type='primary' @click="del">删除</el-button>
      </div>
    </div>
    <el-table border :data='tableData' @selection-change='changeSelection'>
      <el-table-column type='selection' align='center' width='50px'></el-table-column>
      <el-table-column type='index' label='序号' align='center' width='50px'></el-table-column>
      <el-table-column prop='start_port' align='center' label='端口起始'></el-table-column>
      <el-table-column prop='end_port' align='center' label='端口终止'></el-table-column>
      <!-- <el-table-column prop='attack_frequecy_protect' align='center' label='攻击频率检测'></el-table-column>
      <el-table-column prop='pkt_frequecy_limit' align='center' label='报文频率限制'></el-table-column> -->
      <!-- <el-table-column align='center' label='防护模式'>
        <template slot-scope='scope'>
          {{protection_mode_string(scope.row.protection_mode)}}
        </template>
      </el-table-column> -->
      <el-table-column align='center' label='端口状态'>
        <template slot-scope='scope'>
          <span v-if='scope.row.status_port === "0"' class="color-red">禁用</span>
          <span v-else class="color-green">启用</span>
        </template>
      </el-table-column>
      <el-table-column align='center' label='操作'>
        <template slot-scope="scope">
          <el-button type="text" size="small" @click="edit(scope.row)">编辑</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog :title='operText' :visible.sync='visible' width='545px' @close='reset'>
      <el-row style="margin-bottom: 15px">               
        <el-alert title="端口范围：[0, 65535]；格式：单个端口，或者端口范围，比如100-200" type="info" :closable="false" show-icon>
        </el-alert>
      </el-row>
      <el-form label-width="120px" :model="singleFormData" :inline="true" :rules="rules" ref="singleForm" @submit.native.prevent>
        <el-form-item label='防护端口:' prop='port'>
          <el-input v-model="singleFormData.port" placeholder="请输入"></el-input>
        </el-form-item>
        <!-- <el-form-item label='攻击频率检测:' prop='attack_frequecy_protect'>
          <el-input v-model="singleFormData.attack_frequecy_protect" placeholder="请输入"></el-input>
        </el-form-item>
        <el-form-item label='报文频率限制:' prop='pkt_frequecy_limit'>
          <el-input v-model="singleFormData.pkt_frequecy_limit" placeholder="请输入"></el-input>
        </el-form-item> -->
        <el-form-item label='端口状态:' prop='status_port'>
          <el-select v-model="singleFormData.status_port" style="width:163px;">
            <el-option label='禁用' key='0' value='0'></el-option>
            <el-option label='启用' key='1' value='1'></el-option>
          </el-select>
        </el-form-item>
        <!-- <el-form-item label='防护模式:'>
          <el-checkbox-group v-model="singleFormData.protection_mode" style="width: 230px;display: flex; justify-content: space-between;flex-wrap: wrap;">
            <el-checkbox label="延时提交" name="protection_mode" key="1" value='1'></el-checkbox>
            <el-checkbox label="同步连接" name="protection_mode" key='2' value='2'></el-checkbox>
            <el-checkbox label="验证TTL" name="protection_mode" key='3' value='3'></el-checkbox>
            <el-checkbox label="开放端口" name="protection_mode" key='3' value='3'></el-checkbox>
          </el-checkbox-group>
        </el-form-item> -->
      </el-form>
      <div style="text-align:right">
        <el-button type='primary' @click="submitSingleForm('singleForm')">确 定</el-button>
        <el-button @click='visible=false'>取 消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import controlService from 'services/controlService'
export default {
  data () {
    let checkPort = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('端口不能为空'))
      }
      let regPort = /^((([0-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-5]{2}[0-3][0-5]))(\-([0-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-5]{2}[0-3][0-5])){0,1})$/
      if (!regPort.test(value)) {
        return callback(new Error('端口格式有误'))
      }
      return callback()
    }
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
    let checkVal = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请选择'))
      }
      return callback()
    }
    return {
      tcp_port_protect: 0,
      tableData: [],
      selectArr: [],
      visible: false,
      singleFormData: {
        port: '', 
        // attack_frequecy_protect: '', 
        // pkt_frequecy_limit: '', 
        // protection_mode: [], 
        status_port: ''
      },
      rules: {
        port: [{ validator: checkPort, trigger: 'blur' }],
        // attack_frequecy_protect: [{ validator: checkNum, trigger: 'blur' }],
        // pkt_frequecy_limit: [{ validator: checkNum, trigger: 'blur' }],
        status_port: [{ validator: checkVal, trigger: 'change' }]
      },
      operText: '添加配置',
      isEdit: false,
      uid: ''
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    getherChange (val) {
      this.loadData()
    },
    loadData () {
      let sendData = {
        type: 5,
        gether: this.tcp_port_protect
      }
      controlService.getUdpProt(sendData)
        .then((res) => {
          if (res.errcode === 0) {
            this.tableData = res['5'].data.map((item) => {
              let listArr = item.split('|')
              let obj = {
                id: listArr[0],
                start_port: listArr[1],
                end_port: listArr[2],
                status_port: listArr[3],
                // attack_frequecy_protect: listArr[4],
                // pkt_frequecy_limit: listArr[5],
                // protection_mode: listArr[6],
                protection_plug: listArr[4]
              }
              return obj
            })
          }
        })
    },
    del () {
      if (this.selectArr.length < 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '请选择要删除的配置'
        })
      } else {
        this.$confirm('此操作将删除选中配置, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
          .then(() => {
            let idSelect = this.selectArr.map(item => {
              return item.id
            })
            let idStr = idSelect.join('|')
            let send = {5: idStr}
            controlService.delUdpPort(send).then(res => {
              if (res.errcode === 0) {
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '删除成功'
                })
                this.currentPage = 1
                this.loadData()
              }
            })
          })
          .catch(() => {
            
          })
      }
    },
    add () {
      this.operText = '添加配置'
      this.visible = true
      this.isEdit = false
    },
    edit (row) {
      this.uid = row.id
      this.isEdit = true
      this.operText = '修改配置'
      this.visible = true
      let port 
      if (row.start_port === row.end_port) {
        port = row.start_port
      } else {
        port = row.start_port + '-' + row.end_port
      }
      // let protectionMode = []
      // if (row.protection_mode) {
      //   let modeArr = row.protection_mode.split(',')
      //   for (let v of modeArr) {
      //     protectionMode.push(this.protection_mode(v))
      //   }
      // }
      this.singleFormData = {
        port: port, 
        // attack_frequecy_protect: row.attack_frequecy_protect, 
        // pkt_frequecy_limit: row.pkt_frequecy_limit, 
        // protection_mode: protectionMode,
        status_port: row.status_port
      }
    },
    submitSingleForm (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          let portStart = ''
          let portEnd = ''
          // let protection_mode = []
          if (this.singleFormData.port.includes('-')) {
            let portArr = this.singleFormData.port.split('-')
            portStart = portArr[0] > portArr[1] ? portArr[1] : portArr[0]
            portEnd = portArr[0] > portArr[1] ? portArr[0] : portArr[1]
          } else {
            portStart = this.singleFormData.port
            portEnd = this.singleFormData.port
          }
          // for (let v of this.singleFormData.protection_mode) {
          //   if (v === '开放端口') {
          //     protection_mode.push(1)
          //   }
          //   if (v === '同步连接') {
          //     protection_mode.push(2)
          //   }
          //   if (v === '延时提交') {
          //     protection_mode.push(3)
          //   }
          //   if (v === '验证TTL') {
          //     protection_mode.push(4)
          //   }
          // }
          if (this.isEdit) {
            let sendData = {5: `${this.uid}|${portStart}|${portEnd}|${this.singleFormData.status_port}`} 
            controlService.updateUdpPort(sendData)
              .then((res) => {
                if (res.errcode === 0) {
                  this.$message({
                    type: 'success',
                    duration: 1500,
                    message: '修改成功'
                  })
                  this.loadData()
                  this.visible = false
                }
              })
          } else {
            let sendData = {5: `${this.tcp_port_protect}|${portStart}|${portEnd}|${this.singleFormData.status_port}`}
            controlService.addUdpPort(sendData)
              .then((res) => {
                if (res.errcode === 0) {
                  this.$message({
                    type: 'success',
                    duration: 1500,
                    message: '添加成功'
                  })
                  this.loadData()
                  this.visible = false
                }
              })
          }
        }
      })
    },
    reset () {
      this.$refs['singleForm'].resetFields()
      this.singleFormData = {
        port: '',
        // attack_frequecy_protect: '', 
        // pkt_frequecy_limit: '', 
        protection_mode: []
      }
    },
    changeSelection (selection) {
      this.selectArr = selection
    },
    protection_mode_string (str) {
      if (!str) {
        return '-'
      }
      let modeArr = str.split(',')
      let modeStr = ''
      for (let v of modeArr) {
        modeStr = this.protection_mode(v) + ',' + modeStr
      }
      return modeStr.substring(0, modeStr.length - 1)
    },
    protection_mode (index) {
      let type = ''
      switch (index) {
      case '1':
        type = '开放端口'
        break
      case '2':
        type = '同步连接'
        break
      case '3':
        type = '延时提交'
        break
      case '4':
        type = '验证TTL'
        break
      default:
        break
      }
      return type
    }
  }
}
</script>
<style scoped lang="scss">
.title {
  display: flex;
  justify-content: space-between;
  background: #eb9e0517;
  margin-bottom: 20px;
  padding: 5px 10px;
  text-align: left;
  border: 1px solid #ffbe7a;
}
</style>


