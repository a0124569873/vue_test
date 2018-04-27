<template>  
  <div class="content">          
    <div class="content-inner">
      <div class="formtitle">
        <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visible = true">添 加</el-button>
        <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
        <el-button icon='el-icon-remove-outline' type='primary' @click="clearAll">清 空</el-button>
      </div>
      <el-table border :data='tableData'  @selection-change='selectItem'>
        <el-table-column type='selection' style="width:50px;" align='center'></el-table-column>
        <el-table-column type="index" label='序号' align='center'></el-table-column>
        <el-table-column label="VPN服务器地址" prop="ip" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              {{ scope.row.ip }}
            </span>
            <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.ip"></el-input>
          </template>
        </el-table-column>
        <el-table-column label="VPN服务器SSH端口" prop="port" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              {{ scope.row.port }}
            </span>
            <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.port"></el-input>
          </template>
        </el-table-column>
        <el-table-column label="VPN服务器IP段" prop="port" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              {{ scope.row.pip }}
            </span>
            <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.pip"></el-input>
          </template>
        </el-table-column>
        <el-table-column label="用户名" prop="username" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              {{ scope.row.username }}
            </span>
            <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.username"></el-input>
          </template>
        </el-table-column>
        <el-table-column label="密码" type="password" prop="pwd" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              ********
            </span>
            <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.password"></el-input>
          </template>
        </el-table-column>
        <el-table-column label="操作" align='center'>
          <template slot-scope="scope">
            <el-button v-show="!scope.row.isEditting" type="text" size="small" @click="editRow(scope.row)">编辑</el-button>
            <el-button v-show="scope.row.isEditting" type="text" size="small" @click="updateRow(scope.row)">更新</el-button>
            <el-button v-show="scope.row.isEditting" type="text" size="small" @click="editRowCancel(scope.row)">取消</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-pagination small layout="sizes, prev, pager, next" :total="total"
       @size-change="handleSizeChange" @current-change="handleCurrentChange"></el-pagination>

      <el-dialog title='添加配置' :visible.sync='visible' style="text-align:center" width='600px' @close='reset'>
          <el-alert  title="VPN不能配置相同IP" style="ma" type="info" :closable="false" show-icon></el-alert>                 
            <!-- <el-tabs v-model="activeName">
              <el-tab-pane label="单个添加" name="first"> -->
                <div style="margin-top:15px;display: flex; justify-content: center;">
                  <div>
                    <el-form 
                      style="width:500px;" 
                      :model="singleFormData" 
                      :rules="rules" 
                      ref="singleForm"
                      @keyup.enter.native="submitSingleForm">
                      <el-form-item label='VPN服务器地址:' label-width="153px" prop='ip' required>
                        <el-input v-model="singleFormData.ip" placeholder="只能输入单个IP"></el-input>
                      </el-form-item>
                      <el-form-item label='VPN服务器SSH端口:' label-width="153px" prop='port' required>
                        <el-input v-model="singleFormData.port" placeholder="端口范围是[1, 65535]"></el-input>
                      </el-form-item>
                      <el-form-item label='VPN服务器私有IP段:' label-width="153px" prop='pip' required>
                        <el-input v-model="singleFormData.pip" placeholder="格式只能是IP/掩码，掩码范围[1,32]"></el-input>
                      </el-form-item>
                      <el-form-item label='用户名:' label-width="153px" prop='username' required>
                        <el-input v-model="singleFormData.username" placeholder="请输入用户名"></el-input>
                      </el-form-item>
                      <el-form-item label='密码:' label-width="153px" prop='password' required>
                        <el-input v-model="singleFormData.password" placeholder="请输入密码"></el-input>
                      </el-form-item>
                    </el-form>
                    <div style="text-align:right">
                      <el-button type='primary' @click="submitSingleForm" ::loading="isSubmitting">确 定</el-button>
                      <el-button @click='visible=false'>取 消</el-button>
                    </div>
                  </div>
                </div>          
              <!-- </el-tab-pane>
              <el-tab-pane label="批量添加" name="second">
                <table style="width:100%">
                  <tr>
                    <th style="text-align:left">
                      <el-checkbox v-model="selectAll" @change="selectAllChange">
                      </el-checkbox>
                    </th>
                    <th style="position:relative;left:-26px"><span>VPN服务器IP</span></th>  
                    <th style="position:relative;left:-68px"><span>VPN服务器SSH端口</span></th>
                    <th style="position:relative;left:-82px"><span>用户名</span></th>
                    <th style="position:relative;left:-32px"><span>密码</span></th>
                  </tr>
                </table> 
                <el-form ref="batchForm" v-for="(item,index) in batchFormData" :key="item.id" :rules="rules" :model="item"  >
                  <table>
                    <tr >
                      <td style="text-align:left">
                        <el-form-item>
                          <el-checkbox v-model="item.checked" @change='selectSingleItem(item)'></el-checkbox>
                        </el-form-item>
                      </td>
                      <td>
                        <el-form-item prop="ip">
                          <el-input v-model="item.ip" placeholder="请输入IP"></el-input>
                        </el-form-item>
                      </td>
                      <td>
                        <el-form-item prop="port">
                          <el-input v-model="item.port" placeholder="请输入端口"></el-input>
                        </el-form-item> 
                      </td>
                      <td>
                        <el-form-item prop="username">
                          <el-input v-model="item.username" placeholder="请输入用户名"></el-input>
                        </el-form-item> 
                      </td>   
                      <td>
                        <el-form-item prop="password">
                          <el-input v-model="item.password" placeholder="请输入密码"></el-input>
                        </el-form-item> 
                      </td>     
                    </tr>
                  </table> 
                </el-form>       
                <p>
                  <i v-if='nextIcon' class='el-icon-arrow-down showNext' style='font-size:15px;' @click='addBatch'></i>
                </p>
                <div style="text-align:right;margin-right:10px;">
                  <el-button type='primary' @click="submitBatchForm" :loading="isSubmitting">确 定</el-button>
                  <el-button @click='visible=false'>取 消</el-button>
                </div>
              </el-tab-pane>
            </el-tabs> -->
      </el-dialog>
    </div>
  </div>
</template>
<script>
import VPNService from 'services/vpnService'
export default {
  data () {
    const checkIP = (rule, value, callback) => {
      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      let status = regIp.test(value)
      if (!status) {
        callback(new Error('IP格式错误'))
      } else {
        callback()
      }
    } 

    const checkPIP = (rule, value, callback) => {
      let pip = value.split('/')
      if (pip.length !== 2) {
        return callback(new Error('IP格式错误'))
      }

      let ip = pip[0]
      let mask = pip[1] * 1

      if (!Number.isInteger(mask)) {
        return callback(new Error('掩码格式错误'))
      } else {
        if (mask < 1 || mask > 32) {
          return callback(new Error('掩码范围错误'))
        }
      }
      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      let status = regIp.test(ip)
      if (!status) {
        callback(new Error('IP格式错误'))
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
      isSubmitting: false,
      tableData: [],
      dupTableData: [],
      total: 0,
      page: 1,
      row: 10,
      visible: false,
      activeName: 'first',
      selectArr: [],
      singleFormData: {
        ip: '',
        port: '',
        pip: '',
        username: '',
        password: ''
      },
      // batchFormData: [
      //   {
      //     id: 0,
      //     ip: '',
      //     port: '',
      //     username: '',
      //     password: '',
      //     checked: false
      //   },
      //   {
      //     id: 1,
      //     ip: '',
      //     port: '',
      //     username: '',
      //     password: '',
      //     checked: false
      //   },
      //   {
      //     id: 2,
      //     ip: '',
      //     port: '',
      //     username: '',
      //     password: '',
      //     checked: false
      //   },
      //   {
      //     id: 3,
      //     ip: '',
      //     port: '',
      //     username: '',
      //     password: '',
      //     checked: false
      //   },
      //   {
      //     id: 4,
      //     ip: '',
      //     port: '',
      //     username: '',
      //     password: '',
      //     checked: false
      //   }
      // ],
      rules: {
        ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIP, trigger: 'blur' }],
        port: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkPort, trigger: 'blur' }],
        pip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkPIP, trigger: 'blur' }
        ],
        username: [{ required: true, message: '不能为空', trigger: 'blur' }],
        password: [{ required: true, message: '不能为空', trigger: 'blur' }]
      },
      selectedItems: [],
      selectAll: false,
      nextIcon: true
    }
  },
  created () {
    this._loadData()
  },
  methods: {
    _loadData () {
      VPNService.getVPNServer(this.page, this.row)
      .then((res) => {
        if (res.errcode === 0) {
          this.tableData = res['2']['2'].map((item) => {
            let arr = item.split('|')
            return {
              id: arr[0],
              ip: arr[1],
              port: arr[2],
              username: arr[3],
              pip: arr[4],
              password: '',
              isEditting: false
            }
          })
          this.dupTableData = JSON.parse(JSON.stringify(this.tableData))
          this.total = res['2'].count         
        }
      })
    },
    editRow (row) {
      row.isEditting = true
    },
    updateRow (row) {
      $('.el-message').remove()
      let params = {
        '2': [
          row.id + '|' +
          row.ip + '|' +
          row.port + '|' +
          row.username + '|' +
          row.password + '|' +
          row.pip
        ]
      }
     
      VPNService.updateVPNserver(params)
        .then((res) => {
          if (res.errcode === 0) {
            row.isEditting = false
            this.$message({
              showClose: true,
              message: '更新成功',
              type: 'success',
              duration: '1500'
            })
          }
        })
    },
    editRowCancel (row) {
      row.isEditting = false
      let originRows = this.dupTableData.filter(item => {
        return item.id === row.id
      })

      if (originRows.length) {
        row.username = originRows[0].username
        row.password = originRows[0].password
        row.ip = originRows[0].ip
        row.port = originRows[0].port
        row.pip = originRows[0].pip
      }
    },
    handleSizeChange (val) {
      this.row = val
      this._loadData()
    },
    handleCurrentChange (curpage) {
      this.page = curpage
      this._loadData()
    },
    del () {
      $('.el-message').remove()
      if (!this.selectArr.length) {
        this.$message({
          showClose: true,
          message: '请选择要删除的VPN服务器配置',
          type: 'error',
          duration: 0
        })
        return
      }
      this.$confirm('确定删除该VPN服务器配置?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      .then(() => {
        let idStr = this.selectArr.map(item => {
          return item.id
        }).join(',')

        VPNService.delVPNServer({'2': idStr})
        .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              showClose: true,
              message: '删除成功！',
              type: 'success',
              duration: '1500'
            })
            this._loadData()
          }
        })
      })
      .catch(() => { })
    },
    clearAll () {
      $('.el-message').remove()
      this.$confirm('此操作将清除所有VPN服务器配置, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        VPNService.clearVPNServer()
         .then((res) => {
           if (res.errcode === 0) {
             this.$message({
               showClose: true,
               message: '清除成功！',
               type: 'success',
               duration: '1500'
             })
             this._loadData()
           }
         })
      })
    },
    reset () {
      // this.selectAllChange(false)
      // this.selectAll = false 
      // this.selectedItems = []
      // this.batchFormData.forEach(function (item) {
      //   item.checked = false
      // })
      this.$refs['singleForm'].resetFields()
      // this.$refs['batchForm'].map((item) => {
      //   item.resetFields()
      // })
    },
    selectItem (item) {
      this.selectArr = item
    }, 
    addVPN (vpn) { 
      $('.el-message').remove()
      VPNService.createVPNServer(vpn)
      .then((res) => {
        this.isSubmitting = false
        if (res.errcode === 0) {
          this.currentPage = 1    
          this._loadData()
          this.visible = false
          this.$message({
            showClose: true,
            message: '添加成功',
            type: 'success',
            duration: '1500'
          })
        }
      }, () => {
        this.isSubmitting = false
      })
    },
    submitSingleForm () {
      this.$refs['singleForm'].validate((valid) => {
        if (valid) {
          this.isSubmitting = true
          let vpn = {
            '2': [this.singleFormData.ip + '|' + 
                  this.singleFormData.port + '|' +
                  this.singleFormData.username + '|' +
                  this.singleFormData.password + '|' +
                  this.singleFormData.pip ]
          }
          this.addVPN(vpn)
        } else {
          this.isSubmitting = false
          return false
        }
      })
    }
    // submitBatchForm () {
    //   let forms = this.$refs['batchForm']
    //   let selectedForms = forms.filter((form) => {
    //     return this.selectedItems.includes(form.model.id)
    //   })

    //   if (selectedForms.length === 0) {
    //     this.$message({
    //       showClose: true,
    //       message: '请选择要提交的VPN服务器配置',
    //       type: 'error',
    //       duration: 1500
    //     })
    //     return false
    //   }

    //   let isValid = true
    //   selectedForms.forEach((form) => {
    //     form.validate((valid) => {
    //       if (!valid) {
    //         isValid = false
    //       }
    //     })
    //   })

    //   if (isValid) {
    //     this.isSubmitting = true
    //     let data = this.batchFormData.filter((item) => {
    //       return this.selectedItems.includes(item.id)
    //     })
    //     let vpn = data.map((item) => {
    //       return `${item.ip}|${item.port}|${item.username}|${item.password}`
    //     })
    //     this.addVPN({'2': vpn})
    //   }
    // },
    // 批量添加5个输入项
    // addBatch () {
    //   this.selectAll = false
    //   for (let i = 0; i < 5; i++) {
    //     let obj = {
    //       id: i + 5,
    //       ip: ''
    //     }
    //     this.batchFormData.push(obj)
    //   }
    //   this.nextIcon = false
    // },
    // // 选择单个配置，在批量添加时候
    // selectSingleItem (item) {
    //   this.setupSelectedItems(item)
    //   var selectedItem = this.batchFormData.filter((it) => { return it.id === item.id })[0]

    //   if (selectedItem && !selectedItem.checked) {
    //     let selectedForm = this.$refs['batchForm'].filter((form) => {
    //       return form.model.id === item.id
    //     })[0]

    //     if (selectedForm) {
    //       selectedForm.clearValidate()
    //     }
    //   }
    // },
    // // 全部选择, 或者全部不选
    // selectAllChange (isSelectAll) {
    //   let _this = this

    //   if (isSelectAll) {
    //     if (this.selectedItems.length !== this.batchFormData.length) {
    //       this.selectedItems = []
    //       this.batchFormData.forEach(function (item) {
    //         _this.setupSelectedItems(item)
    //       })
    //       this.batchFormData = this.batchFormData.map(function (item) {
    //         item.checked = true
    //         return item
    //       })
    //     }
    //   } else {
    //     // 如果全不选，那就清除所有校验结果
    //     this.$refs['batchForm'].forEach((form) => {
    //       form.clearValidate()
    //     })
    //     if (this.selectedItems.length === this.batchFormData.length) {
    //       this.selectedItems = []
    //       this.batchFormData = this.batchFormData.map(function (item) {
    //         item.checked = false
    //         return item
    //       })
    //     }
    //   }
    // },
    // // 添加已勾选的数据
    // setupSelectedItems (item) {
    //   let index = this.selectedItems.indexOf(item.id)
    //   if (index === -1) {
    //     this.selectedItems.push(item.id)
    //   } else {
    //     this.selectedItems.splice(index, 1)
    //   }
    // }
    
  }
}
</script>
<style scoped>
.content-inner {
  margin-top: 10px;
}
.butn{
  width: 300px;
  margin-top: 30px;
  margin-left: 83px;
}
th,td{
  padding-left: 10px;
  padding-right: 10px;
}
.formtitle {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 15px;
}
.el-row{
  margin-bottom: 10px;
}
.el-input{
  width: 100%;
}
.el-select{
  width: 100px;
}
.el-form{
  margin-top: 10px;
}
.el-pagination{
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.green{
  color: #5daf34;
}
.red{
  color: #ff3600;
}
</style>