<template>  
  <div class="content" v-loading="isLoading" :element-loading-text="loadingText" element-loading-background="rgba(255, 255, 255, .8)">          
    <div class="content-inner">
      <div class="formtitle">
        <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visible = true">添 加</el-button>
        <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
        <el-button icon='el-icon-remove-outline' type='primary' @click="clearAll">清 空</el-button>
      </div>
      <el-table border :data='tableData'  @selection-change='selectItem'>
        <el-table-column type='selection' style="width:50px;" align='center'></el-table-column>
        <el-table-column type='index' label="序号" align='center'></el-table-column>
        <el-table-column label='虚拟VPN' prop='v_vpn' align='center'></el-table-column>
        <el-table-column label="真实VPN" prop="r_vpn" align='center'></el-table-column>
      </el-table>
      <el-pagination small layout="sizes, prev, pager, next" :total="total"
       @size-change="handleSizeChange" @current-change="handleCurrentChange"></el-pagination>

      <div>
      <el-dialog 
        title='添加配置' 
        :visible.sync='visible' 
        width='600px' 
        @close='reset'
        style="text-align:center">
        <el-row>                 
          <el-alert  title="VPN不能配置相同IP。" type="info" :closable="false" show-icon></el-alert>                 
        </el-row>
        <el-row>
          <el-col :span='24'>
            <!-- <el-tabs v-model="activeName"> -->
              <!-- <el-tab-pane label="单个添加" name="first"> -->
                <div style="margin-top:15px;display: flex; justify-content: center;">
                  <div>
                    <el-form 
                      style="width:500px;" 
                      :model="singleFormData"  
                      :rules="rules" 
                      ref="singleForm"
                      @keyup.enter.native="submitSingleForm">
                      <el-form-item label='虚拟VPN:' label-width="80px" prop='new_vvpn'>
                        <el-input v-model="singleFormData.new_vvpn" placeholder="请输入IP"></el-input>
                      </el-form-item>
                      <el-form-item label='真实VPN:' label-width="80px" prop='new_rvpn'>
                        <el-input v-model="singleFormData.new_rvpn" placeholder="请输入IP"></el-input>
                      </el-form-item>
                    </el-form>
                    <div style="text-align:right">
                      <el-button type='primary' @click="submitSingleForm" :loading="isSubmitting">确 定</el-button>
                      <el-button @click='visible=false'>取 消</el-button>
                    </div>
                  </div>
                </div>          
              <!-- </el-tab-pane> -->
              <!-- <el-tab-pane label="批量添加" name="second">
                <table>
                  <tr>
                    <th style="width:100px;text-align:left">
                      <el-checkbox v-model="selectAll" @change="selectAllChange"><span>全选</span> </el-checkbox>
                    </th>
                    <th  style="width:45%;"><span>虚拟VPN</span></th>  <th style="width:45%;" ><span>真实VPN</span></th>
                  </tr>
                </table> 
                <el-form ref="batchForm" v-for="(item,index) in batchFormData" :key="item.id" :rules="rules" :model="item"  >
                  <table>
                    <tr >
                      <td style="width:100px;text-align:left">
                        <el-form-item>
                          <el-checkbox v-model="item.checked" @change='selectSingleItem(item)'></el-checkbox>
                        </el-form-item>
                      </td>
                      <td style="width:45%;">
                        <el-form-item prop="new_vvpn">
                          <el-input v-model="item.new_vvpn" placeholder="请输入IP"></el-input>
                        </el-form-item>
                      </td>
                      <td style="width:45%;">
                        <el-form-item prop="new_rvpn">
                          <el-input v-model="item.new_rvpn" placeholder="请输入IP"></el-input>
                        </el-form-item> 
                      </td>     
                    </tr>
                  </table> 
                </el-form>       
                <p>
                  <i v-if='nextIcon' class='el-icon-arrow-down showNext' style='font-size:15px;' @click='addBatch'></i>
                </p>
                <div style="text-align:right;margin-right:15px;">
                  <el-button type='primary' @click="submitBatchForm">确 定</el-button>
                  <el-button @click='visible=false'>取 消</el-button>
                </div>
              </el-tab-pane> -->
            <!-- </el-tabs> -->
          </el-col>
        </el-row>
      </el-dialog>
      </div>
    </div>
  </div>
</template>
<script>
import VPNService from 'services/vpnService'
export default {
  data () {
    const regHandle = (callback, status) => {
      if (!status) {
        callback(new Error('虚拟VPN格式错误'))
      } else {
        callback()
      }
    }
    const checkVIP = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('不能为空'))
      }

      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/

      // 192.168.1.1-192.168.2.1
      if (value.indexOf('-') !== -1) {
        let ips = value.split('-')
        if (ips.length) {
          let status = ips.every((ip) => {
            return regIp.test(ip)
          })
          regHandle(callback, status)
          return true
        }
      }

      // 192.168.1.1/32
      if (value.indexOf('/') !== -1) {
        let ips = value.split('/')
        if (ips.length) {
          let status = regIp.test(ips[0]) && /^\d+$/.test(ips[1]) && Number(ips[1]) <= 32
          regHandle(callback, status)
          return true
        }
      }

      // 如果只是一个ip
      let status = regIp.test(value)
      regHandle(callback, status)
    }
    const checkIP = (rule, value, callback) => {
      if (!value) {
        return callback(new Error(`不能为空`))
      }
      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      let status = regIp.test(value)
      if (!status) {
        callback(new Error('格式错误'))
      } else {
        callback()
      }
    } 
   
    return {
      isSubmitting: false,
      isLoading: true,
      isLoading2: true,
      loadingText: '',
      tableData: [],
      total: 0,
      page: 1,
      row: 10,
      visible: false,
      activeName: 'first',
      selectArr: [],
      singleFormData: {
        new_vvpn: '',
        new_rvpn: ''
      },
      batchFormData: [
        {
          id: 0,
          new_vvpn: '',
          new_rvpn: '',
          checked: false
        },
        {
          id: 1,
          new_vvpn: '',
          new_rvpn: '',
          checked: false
        },
        {
          id: 2,
          new_vvpn: '',
          new_rvpn: '',
          checked: false
        },
        {
          id: 3,
          new_vvpn: '',
          new_rvpn: '',
          checked: false
        },
        {
          id: 4,
          new_vvpn: '',
          new_rvpn: '',
          checked: false
        }
      ],
      rules: {
        new_vvpn: [{ validator: checkIP, trigger: 'blur' }],
        new_rvpn: [{ validator: checkIP, trigger: 'blur' }]
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
      VPNService.getVPN(this.page, this.row)
      .then((res) => {
        if (res.errcode === 0) {
          this.tableData = res.vpn 
          this.total = res.count         
        }
      })
      .always(() => {
        this.isLoading = false
        this.loadingText = ''
      })
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
          message: '请选择要删除的VPN配置',
          type: 'error',
          duration: 0
        })
        return
      }
      this.$confirm('确定删除该VPN配置?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      .then(() => {
        this.isLoading = true 
        this.loadingText = '正在删除中...'
        let idStr = this.selectArr.map(item => {
          return item.id
        }).join(',')

        VPNService.delVPN(idStr)
        .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              showClose: true,
              message: '删除成功！',
              type: 'success',
              duration: '1500'
            })
          }
          this._loadData()
        })
        .always(() => {
          this.isLoading = false 
          this.loadingText = ''
        })
      })
      .catch(() => { })
    },
    clearAll () {
      $('.el-message').remove()
      this.$confirm('此操作将清除所有VPN配置, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.isLoading = true 
        this.loadingText = '正在清除中...'
        VPNService.clearVPN()
         .then((res) => {
           if (res.errcode === 0) {
             this.$message({
               showClose: true,
               message: '清除成功！',
               type: 'success',
               duration: '1500'
             })
           }
           this._loadData()
         })
         .always(() => {
           this.isLoading = false 
           this.loadingText = ''
         })
      })
    },
    reset () {
      this.selectAllChange(false)
      this.selectAll = false 
      this.selectedItems = []
      this.batchFormData.forEach(function (item) {
        item.checked = false
      })
      this.$refs['singleForm'].resetFields()
      this.$refs['batchForm'].map((item) => {
        item.resetFields()
      })
    },
    selectItem (item) {
      this.selectArr = item
    }, 
    addVPN (vpn) {
      $('.el-message').remove()
      VPNService.addVPN(vpn)
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
            duration: '1000'
          })
        }
      })
    },
    submitSingleForm () {
      this.$refs['singleForm'].validate((valid) => {
        if (valid) {
          this.isSubmitting = true
          let vpn = [`${this.singleFormData.new_vvpn}|${this.singleFormData.new_rvpn}`]
          this.addVPN(vpn)
        } else {
          this.isSubmitting = false
          return false
        }
      })
    },
    submitBatchForm () {
      $('.el-message').remove()
      let forms = this.$refs['batchForm']
      let selectedForms = forms.filter((form) => {
        return this.selectedItems.includes(form.model.id)
      })

      if (selectedForms.length === 0) {
        this.$message({
          showClose: true,
          message: '请选择要提交的VPN配置',
          type: 'error',
          duration: 1500
        })
        return false
      }

      let isValid = true
      selectedForms.forEach((form) => {
        form.validate((valid) => {
          if (!valid) {
            isValid = false
          }
        })
      })

      if (isValid) {
        let data = this.batchFormData.filter((item) => {
          return this.selectedItems.includes(item.id)
        })
        let vpn = data.map((item) => {
          return `${item.new_vvpn}|${item.new_rvpn}`
        })
        this.addVPN(vpn)
      }
    },
    // 批量添加5个输入项
    addBatch () {
      this.selectAll = false
      for (let i = 0; i < 5; i++) {
        let obj = {
          id: i + 5,
          ip: ''
        }
        this.batchFormData.push(obj)
      }
      this.nextIcon = false
    },
    // 选择单个配置，在批量添加时候
    selectSingleItem (item) {
      this.setupSelectedItems(item)
      var selectedItem = this.batchFormData.filter((it) => { return it.id === item.id })[0]

      if (selectedItem && !selectedItem.checked) {
        let selectedForm = this.$refs['batchForm'].filter((form) => {
          return form.model.id === item.id
        })[0]

        if (selectedForm) {
          selectedForm.clearValidate()
        }
      }
    },
    // 全部选择, 或者全部不选
    selectAllChange (isSelectAll) {
      let _this = this

      if (isSelectAll) {
        if (this.selectedItems.length !== this.batchFormData.length) {
          this.selectedItems = []
          this.batchFormData.forEach(function (item) {
            _this.setupSelectedItems(item)
          })
          this.batchFormData = this.batchFormData.map(function (item) {
            item.checked = true
            return item
          })
        }
      } else {
        // 如果全不选，那就清除所有校验结果
        this.$refs['batchForm'].forEach((form) => {
          form.clearValidate()
        })
        if (this.selectedItems.length === this.batchFormData.length) {
          this.selectedItems = []
          this.batchFormData = this.batchFormData.map(function (item) {
            item.checked = false
            return item
          })
        }
      }
    },
    // 添加已勾选的数据
    setupSelectedItems (item) {
      let index = this.selectedItems.indexOf(item.id)
      if (index === -1) {
        this.selectedItems.push(item.id)
      } else {
        this.selectedItems.splice(index, 1)
      }
    }
    
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