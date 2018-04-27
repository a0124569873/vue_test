<template>
  <div class="backlink">   
    <div class="content" v-loading="isLoading" :element-loading-text="loadingText" element-loading-background="rgba(255, 255, 255, .8)">
      <div class="content-inner">
        <div class="formtitle">
          <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visible = true">添 加</el-button>
          <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
           <el-button icon='el-icon-remove-outline' type='primary' @click="clearAll">清 空</el-button>
        </div>
        <el-table border :data='tableData' @selection-change='selectItem'>
          <el-table-column type='selection' style="width:50px;" align='center'></el-table-column>
          <el-table-column type='index' label="序号" align='center'></el-table-column>
          <el-table-column label="源IP" align='center'>
            <template slot-scope="scope">
              <span>{{scope.row.source_ip ? scope.row.source_ip : '--/--'}}</span>
            </template>
          </el-table-column>
          <el-table-column label="目的IP" prop="dest_ip" align='center'></el-table-column>
          <el-table-column label="源端口" prop="source_port" align='center'>
            <template slot-scope="scope">
              <span>{{scope.row.source_port ? scope.row.source_port : '--/--'}}</span>
            </template>
          </el-table-column>    
          <el-table-column label="目的端口" align='center'>
            <template slot-scope="scope">
              <span>{{scope.row.dest_port ? scope.row.dest_port : '--/--'}}</span>
            </template>
          </el-table-column>
          <el-table-column label="真实端口" align='center'>
            <template slot-scope="scope">
              <span>{{scope.row.real_port ? scope.row.real_port : '--/--'}}</span>
            </template>
          </el-table-column>
          <el-table-column label="私有IP" prop="p_ip" align='center'></el-table-column>
          <el-table-column label="协议类型" prop="protocol" align='center'></el-table-column>
        </el-table>
        <el-pagination small layout="sizes, prev, pager, next" :total="total"
            @size-change="handleSizeChange" @current-change="handleCurrentChange">
        </el-pagination>

        <el-dialog title='添加配置' width="60%" style="text-align:center" class="backlink-dialog" :visible.sync='visible'  @close='reset'>
          <el-alert  style="margin-top:10px;text-align:left" type="info" :closable="false" show-icon
            title="">
            <template>
              <ul>
                <li>源IP, 目的IP支持单个IP和掩码格式，掩码范围[1, 32]</li>
                <li style="margin-top:5px;">真实端口的范围是[1, 65535]，源端口和目的端口范围是[32768, 65535]</li>
                <li style="margin-top:5px;">源端口和真实端口只支持单个端口，目的端口支持单个端口和范围，比如 32768，30000:65535</li>
              </ul>
            </template>
          </el-alert>
              <!-- <el-tabs v-model="activeName"> -->
                <!-- <el-tab-pane label="单个添加" name="first"> -->
                  <div style="margin-top:25px;">
                    <el-form :model="singleFormData" :rules="rules" ref="singleForm" style="width:400px;margin:0 auto">
                      <el-form-item label='源IP' label-width="80px" prop='source_ip'>
                        <el-input v-model="singleFormData.source_ip" placeholder="请输入单个IP， 或者IP/掩码"></el-input>
                      </el-form-item>
                      <el-form-item label='目的IP' label-width="80px" prop='dest_ip' required>
                        <el-input v-model="singleFormData.dest_ip" placeholder="请输入单个IP， 或者IP/掩码"></el-input>
                      </el-form-item>
                      <el-form-item label='私有IP' label-width="80px" prop='p_ip' required>
                        <el-select class="single-select" v-model="singleFormData.p_ip">
                          <el-option v-for="(ip, index) in privateIp" :key="index" :label="ip" :value="ip"></el-option>
                        </el-select>
                        <p class="err"></p>
                      </el-form-item>
                      <el-form-item label='协议类型' label-width="80px" prop='protocol' required>
                        <el-select class="single-select" v-model="singleFormData.protocol" @change="isClearport(singleFormData)">
                          <el-option v-for="item in protocal_ar" :key="item.value" :label="item.text" :value="item.value"> </el-option>
                        </el-select>
                      </el-form-item>
                      <el-form-item label='源端口' label-width="80px" prop='source_port'>
                        <el-input 
                          v-model="singleFormData.source_port" 
                          :disabled="singleFormData.protocol!='6'&&singleFormData.protocol!='17'" 
                          @blur="checkPort(singleFormData.protocol,singleFormData.source_port, $event)">
                        </el-input>
                        <p class="err"></p>
                      </el-form-item>
                      <el-form-item label='目的端口' label-width="80px" prop='dest_port' required>
                        <el-input 
                          v-model="singleFormData.dest_port" 
                          :disabled="singleFormData.protocol!='6'&&singleFormData.protocol!='17'" 
                          @blur="checkPort(singleFormData.protocol,singleFormData.dest_port, $event)">
                        </el-input>
                        <p class="err"></p>
                      </el-form-item>
                      <el-form-item label='真实端口' label-width="80px" prop='real_port' required>
                        <el-input 
                          v-model="singleFormData.real_port" 
                          :disabled="singleFormData.protocol!='6'&&singleFormData.protocol!='17'" 
                          @blur="checkPort(singleFormData.protocol,singleFormData.dest_port, $event)">
                        </el-input>
                        <p class="err"></p>
                      </el-form-item>
                      <div style="text-align:right">
                        <el-button type='primary' @click="submitSingleForm" :loading="isSubmitting">确 定</el-button>
                        <el-button @click='visible=false'>取 消</el-button>
                      </div>
                    </el-form>
                    
                  </div>          
                <!-- </el-tab-pane> -->
                <!-- <el-tab-pane label="批量添加" name="second">
                  <table style="width:100% !important;table-layout:fixed">
                    <tr>
                      <th style="width:34px;text-align:left">
                        <el-checkbox v-model="selectAll" @change="selectAllChange"></el-checkbox>
                      </th>
                      <th style="position: relative;left: -20px;">
                        <div>源IP</div>
                      </th>
                      <th style="position: relative;left: -20px;">
                        <div class="required-label">目的IP</div>
                      </th>
                      <th style="position: relative;left: -17px;">
                        <div class="required-label">私有IP</div>
                      </th>
                      <th style="position: relative;left: -9px;">
                        <div>协议类型</div>
                      </th>
                      <th style="position: relative;left: -5px;">
                        <div class="el-form-item__content">源端口</div>
                      </th>
                      <th style="position: relative;left: -3px;">
                        <div class="el-form-item__content">目的端口</div>
                      </th>
                    </tr>
                  </table>
                  <el-form class="batch-form" style="margin-bottom:15px" ref="batchForm" v-for="(item,index) in batchFormData" :key="item.id" :rules="rules" :model="item">
                    <table>
                      <tr >
                        <td style="padding-top:0px;text-align:left">
                          <el-checkbox v-model="item.checked" @change='selectSingleItem(item)'></el-checkbox>
                        </td>
                        <td style="margin-left:40px">
                          <el-form-item prop="source_ip" >
                            <el-input v-model="item.source_ip" placeholder="请输入单个IP， 或者IP/掩码"></el-input>
                          </el-form-item> 
                        </td>  
                        <td style="">
                          <el-form-item prop="dest_ip" required>       
                            <el-input v-model="item.dest_ip" placeholder="请输入单个IP， 或者IP/掩码"></el-input>
                          </el-form-item> 
                        </td>  
                        <td style="">
                          <el-form-item prop="p_ip" required>
                            <el-select class="single-select" v-model="item.p_ip">
                              <el-option v-for="(ip, index) in privateIp" :key="index" :label="ip" :value="ip"></el-option>
                            </el-select>
                            <p class="err"></p>
                          </el-form-item> 
                        </td>   
                        <td style="">
                          <el-form-item prop="protocol" required>
                            <el-select class="single-select" v-model="item.protocol" @change="isClearport(item)">
                              <el-option v-for="item in protocal_ar" :key="item.value" :label="item.text" :value="item.value"></el-option>
                            </el-select>
                          </el-form-item> 
                        </td>
                        <td style="">
                          <el-form-item prop="source_port">       
                            <el-input v-model="item.source_port" placeholder="请输入源端口" :disabled="item.protocol!='6'&&item.protocol!='17'" @blur="checkPort(item.protocol,item.source_port, $event)"></el-input>
                            <p class="err"></p>
                          </el-form-item> 
                        </td>
                        <td style="">
                          <el-form-item prop="dest_port">       
                            <el-input v-model="item.dest_port" placeholder="请输入目的端口" :disabled="item.protocol!='6'&&item.protocol!='17'" @blur="checkPort(item.protocol,item.source_port, $event)"></el-input>
                            <p class="err"></p>
                          </el-form-item> 
                        </td>   
                      </tr>
                    </table> 
                  </el-form>       
                  <p>
                    <i v-if='nextIcon' class='el-icon-arrow-down showNext' style='font-size:15px;' @click='addBatch'></i>
                  </p>
                  <div style="text-align:right;margin-right:15px;">
                    <el-button type='primary' @click="submitBatchForm" :loading="isSubmitting">确 定</el-button>
                    <el-button @click='visible=false'>取 消</el-button>
                  </div>
                </el-tab-pane> -->
              <!-- </el-tabs> -->
        </el-dialog>
      </div>
    </div>
  </div>
</template>
<script>
import ProtocolData from 'components/common/ProtocolData'
import BacklinkService from 'services/backlinkService'
export default {
  data () {
    const checkIp = (rule, value, callback) => {
      if (rule.field === 'source_ip') {
        if (value === undefined || value === null || String(value).trim().length === 0) {
          return callback()
        }
      }
      let regIp = /^(((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?))(\/([1-9]|[1-2][0-9]|3[0-2]))?$/
      let status = regIp.test(value)
      if (!status) {
        callback(new Error('IP格式错误'))
      } else {
        callback()
      }
    }
    const checkBlank = (rule, value, callback) => {
      if (!value) {
        return callback(new Error(`不能为空`))
      } else {
        callback()
      }
    }
    const checkUID = (rule, value, callback) => {
      if (!value) {
        return callback(new Error(`不能为空`))
      } 
      
      let regNum = /^\d+$/
      let status = regNum.test(value)
      if (value < 2 || value > 65535 || !status) {
        callback(new Error('UID格式不正确，格式为2-65535的整数'))
      } else {
        callback()
      }
    }
    return {
      isSubmitting: false,
      isLoading: true,
      loadingText: '',
      type: '3',
      tableData: [],
      total: 0,
      page: 1,
      row: 10,
      visible: false,
      activeName: 'first',
      selectArr: [],
      singleFormData: {
        source_ip: '',
        dest_ip: '',
        source_port: '',
        dest_port: '',
        real_port: '',
        p_ip: '',
        protocol: '6'
      },
      rules: {
        source_ip: [{ validator: checkIp, trigger: 'blur' }],
        dest_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { required: true, message: '不能为空', trigger: 'change' },
          { validator: checkIp, trigger: 'blur' }
        ],
        p_ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { required: true, message: '不能为空', trigger: 'change' },
          { validator: checkIp, trigger: 'blur' }
        ],
        dest_port: [
          { required: true, message: '不能为空', trigger: 'blur' }
        ],
        real_port: [
          { required: true, message: '不能为空', trigger: 'blur' }
        ]
      },
      protocal_ar: ProtocolData.data,
      selectedItems: [],
      selectAll: false,
      nextIcon: true,
      privateIp: []
    }
  },
  created () {
    this._privatIP()
    this._loadData()
  },
  methods: {
    _loadData () {
      BacklinkService.getBacklink(this.page, this.row)
      .then((res) => {
        if (res.errcode === 0) {
          this.tableData = res.link.map((item) => {
            let arr = item.split('|')
            let ret = {}
            ret.uid = arr[0] === 0 ? '' : arr[0]
            ret.source_ip = arr[1]
            ret.dest_ip = arr[2]
            ret.source_port = arr[3]
            ret.dest_port = arr[4]
            ret.real_port = arr[5]
            ret.p_ip = arr[6]
            ret.protocol = ProtocolData.value(arr[7])
            return ret
          })
          this.total = res.count   
        }
      })
      .always(() => {
        this.isLoading = false
      })
    },
    _privatIP () {
      BacklinkService.getPriIP().then((res) => {
        if (res.errcode === 0) {
          this.privateIp = res.ips     
        }
      })
    },
    isClearport (item) {
      if (item.protocol !== '6' && item.protocol !== '17') { 
        item.source_port = ''
        item.dest_port = ''
        $('.success-border').removeClass('success-border')
        $('.error-border').removeClass('error-border')
        $('.err').hide()
      }
    },
    checkPort (protocol, value, ev) {
      let $dom = $(ev.target)
      if (protocol !== '6' && protocol !== '17') {
        $dom.removeClass('success-border')
        $dom.removeClass('error-border')
        $dom.parent().parent().find('.err').hide()
        return true
      }
      // if (value === undefined || value === null || String(value).trim().length === 0) {
      //   $dom.removeClass('success-border')
      //   $dom.addClass('error-border')
      //   $dom.parent().parent().find('.err').show().html('不能为空')
      //   return false
      // } 

      // 如果不为空，才去校验端口
      if (value !== undefined && value !== null && String(value).trim().length !== 0) {
        let regPort = /^([1-9]|[1-9]\d{1,3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/
        let status = false 

        if (value.indexOf(':') !== -1) {
          value = value.split(':')
          for (let i = 0; i < value.length; i++) {
            status = regPort.test(value[i])
            if (!status) {
              break
            }
          }
        } else {
          status = regPort.test(value)
        }
        if (!status) {
          $dom.removeClass('success-border')
          $dom.addClass('error-border')
          $dom.parent().parent().find('.err').show().html(`端口格式不正确`)
          return false
        } else {
          $dom.removeClass('error-border')
          $dom.addClass('success-border')
          $dom.parent().parent().find('.err').hide()
          return true
        }
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
          message: '请选择要删除的配置',
          type: 'error',
          duration: 0
        })
        return
      }
      this.$confirm('此操作将删除该回连设置, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.isLoading = true
        this.loadingText = '正在删除中...'

        let idSelect = this.selectArr.map(item => {
          return item.uid
        })
        let idStr = idSelect.join(',')
        BacklinkService.delBacklink(idStr)
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
      .catch(() => {})
    },
    clearAll () {
      $('.el-message').remove()
      this.$confirm('此操作将清除所有回连设置, 是否继续?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.isLoading = true
        this.loadingText = '正在清除中...'

        BacklinkService.clearBacklink()
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
      .catch(() => {})
    },
    reset () {
      $('.err').hide()
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
    addBacklink (link) {
      $('.el-message').remove()
      BacklinkService.addBacklink(link)
      .then((res) => {
        if (res.errcode === 0) {
          this.$message({
            showClose: true,
            message: '添加成功',
            type: 'success',
            duration: '1500'
          })
          this.visible = false
          this.isSubmitting = false
          this._loadData()
        }
      }).always(() => {
        this.isSubmitting = false
      })
    },
    submitSingleForm () {
      if ($('.error-border').length) {
        return false
      }
      this.$refs['singleForm'].validate((valid) => {
        // let chkport = this.checkPort(this.singleFormData.protocol, this.singleFormData.dest_port, '.s_err_notice')
        if (valid) {
          this.isSubmitting = true
          let link = [this.singleFormData.source_ip + '|' +
                    this.singleFormData.dest_ip + '|' +
                    this.singleFormData.source_port + '|' +
                    this.singleFormData.dest_port + '|' +
                    this.singleFormData.real_port + '|' +
                    this.singleFormData.p_ip + '|' +
                    this.singleFormData.protocol]
          this.addBacklink(link)
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
    //       message: '请选择要提交的VPN配置',
    //       type: 'error',
    //       duration: 1500
    //     })
    //     return false
    //   }

    //   if ($('.error-border').length) {
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
      
    //   let data = this.batchFormData.filter((item) => {
    //     return this.selectedItems.includes(item.id)
    //   })

    //   // let portValid = true
    //   // data.map((item) => {
    //   //   let chkport = this.checkPort(item.protocol, item.port, '.err_notice' + item.id)
    //   //   if (!chkport) {
    //   //     portValid = false
    //   //   }
    //   // })

    //   if (isValid) {
    //     this.isSubmitting = true
    //     let link = data.map((item) => {
    //       return item.source_ip + '|' +
    //                 item.dest_ip + '|' +
    //                 item.source_port + '|' +
    //                 item.dest_port + '|' +
    //                 item.p_ip + '|' +
    //                 item.protocol
    //     })
    //     this.addBacklink(link)
    //   }
    // },
    // // 批量添加5个输入项
    // addBatch () {
    //   this.selectAll = false
    //   for (let i = 0; i < 5; i++) {
    //     let obj = {
    //       id: i + 5,
    //       ip: '',
    //       protocol: 'all'
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
<style>
.success-border,
.success-border:hover,
.success-border:focus {
  border-color: #67c23a !important
}
.error-border,
.error-border:hover,
.error-border:focus {
  border-color: #FF8E7A !important
}
.backlink-dialog .el-dialog {
  max-width: 1186px
}
.required-label:before {
  content: '*';
  color: #fa5555;
  margin-right: 4px;
}

.single-select,
.single-select.el-select .el-input {
  width: 100%
}
</style>

<style scoped>
.backlink table {
  width: auto !important;
}
.content-inner {
  margin-top: 20px;
}
.butn{
  width: 300px;
  margin-top: 30px;
  margin-left: 83px;
}
.formtitle {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 15px;
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
.err{
   position: absolute;
   color: #fa5555;
   text-align: left;
}

th,td{
  padding-left: 10px;
  padding-right: 10px;
}
.batch-form .el-form-item {
  margin-bottom: 0
}
</style>
