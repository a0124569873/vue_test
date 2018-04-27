<template>
    <el-row>
      <el-col v-loading='false' element-loading-background="rgba(255, 255, 255, 1)" :span='24'>     
        <div class="content">
          <div class="formtitle">
            <div style="text-align:right;margin-bottom:20px">
              <div class="my-upload" style="">
                <el-upload
                  ref="upload"
                  action="/maskpools/upload"
                  :file-list="file"
                  :on-success = "uploadSuccess"
                  :auto-upload="false"
                  >
                  <el-button slot="trigger" size="mini" type="primary">选取文件</el-button>
                  <el-button style="margin-left: 10px;" size="mini" type="primary" @click="submitUpload">导入伪装原型池</el-button>
                </el-upload>
              </div>
              <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="add">添 加</el-button>
              <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
            </div>
          </div>
          <el-table border :data='tabData' @selection-change='changeSelection'>
            <el-table-column type='selection' align='center' width='50px'></el-table-column>
            <el-table-column type='index' align='center' label='序号' width='60px'></el-table-column>
            <el-table-column prop='ip' align='center' label='伪装原型IP'></el-table-column>
          </el-table>
          <div class="page" style="margin-top:15px" v-if="total>0">
            <el-pagination  @size-change="pageSizeChange" @current-change="handleCurrentChange" :current-page="currentPage"
                :page-sizes="[10, 20, 40, 80, 100]" :page-size="pageSize" layout="sizes, prev, pager, next " :total="total">
            </el-pagination>      
          </div>       
          <el-dialog title='添加配置' :visible.sync='visible' style="text-align:center" width='500px' @close='reset'>
                <el-alert
                  style="margin-top:20px;text-align:left"
                  title="IP地址格式：192.168.0.1，192.168.0.1/24，子网掩码范围[1, 32]"
                  type="info"
                  :closable="false"
                  show-icon>
                </el-alert>
                <!-- <el-tabs v-model="activeName"> -->
                  <!-- <el-tab-pane label="单个添加" name="first"> -->
                    <el-form 
                      style="margin: 0 auto; margin-top:30px; width:100%" 
                      label-width="82px" 
                      :label-position="'right'" 
                      :model="singleFormData" 
                      :rules="rules" 
                      ref="singleForm"
                      @submit.native.prevent
                      @keyup.enter.native="submitSingleForm">
                      <el-form-item label='服务器IP:' prop='ip'>
                        <el-input v-model="singleFormData.ip" placeholder="请输入IP，IP/掩码"></el-input>
                      </el-form-item>
                    </el-form>
                    <div style="text-align:right;margin-top:30px;">
                      <el-button type='primary' @click="submitSingleForm" :loading="isSubmitting">{{loadingSubmitText}}</el-button>
                      <el-button @click='visible=false'>取 消</el-button>
                    </div>
                  <!-- </el-tab-pane> -->
                  <!-- <el-tab-pane label="批量添加" name="second">
                    <div>
                      <el-form :inline="true" style="position:relative;height:40px">
                        <el-form-item style="position:absolute;left:14px">
                          <el-checkbox v-model="selectAll" @change="selectAllChange">
                            全选
                          </el-checkbox>
                        </el-form-item>
                        <el-form-item style='position:absolute;left:162px;width:70px;display:inline-block;'>
                          <span>服务器IP</span>
                        </el-form-item>
                      </el-form>
                      <el-form :inline="true" ref="batchForm" :rules="rules" :model="item" v-for="(item,index) in batchFormData" :key="item.id">
                        <el-form-item style="margin-left:7px">
                          <el-checkbox v-model="item.checked" @change='selectSingleItem(item)'></el-checkbox>
                        </el-form-item>
                        <el-form-item prop="ip" style="margin-top:-2px">
                          <el-input v-model="item.ip" placeholder="请输入IP，IP范围，IP/掩码" style='width:280px'></el-input>
                        </el-form-item>
                      </el-form>
                    </div>
                    <p>
                      <i v-if='nextIcon' class='el-icon-arrow-down showNext' style='font-size:15px;' @click='addBatch'></i>
                    </p> 
                    <div style="text-align:right;margin-top:20px;margin-right:18px;">
                      <el-button type='primary' @click="submitBatchForm" :loading="isSubmittingForBatch">{{loadingSubmitTextForBatch}}</el-button>
                      <el-button @click="reset">取 消</el-button>
                    </div>
                  </el-tab-pane> -->
                <!-- </el-tabs> -->
            
          </el-dialog>
        </div>
    </el-col>
  </el-row>
</template>
<script>
import PoolService from 'services/poolService'

export default {
  data () {
    const regHandle = (callback, status) => {
      if (!status) {
        callback(new Error('IP格式错误'))
      } else {
        callback()
      }
    }
    const checkIp = (rule, value, callback) => {
      let regIp = /^(((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?))(\/([1-9]|[1-2][0-9]|3[0-2]))?$/

      // // 192.168.1.1-192.168.2.1
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
      // if (value.indexOf('/') !== -1) {
      //   let ips = value.split('/')
      //   if (ips.length) {
      //     let status = regIp.test(ips[0]) && /^\d+$/.test(ips[1]) && Number(ips[1]) >= 1 && Number(ips[1]) <= 32
      //     regHandle(callback, status)
      //     return true
      //   }
      // }

      // 如果只是一个ip
      let status = regIp.test(value)
      regHandle(callback, status)
    }

    return {
      file: [],
      load: true,
      currentPage: 1,
      pageSize: 10,
      total: 0,
      visible: false,
      searchIp: {ip: ''},
      activeName: 'first',
      guideIps: [],
      singleFormData: {ip: ''},
      // batchFormData: [
      //   {id: 0, ip: '', checked: false},
      //   {id: 1, ip: '', checked: false},
      //   {id: 2, ip: '', checked: false},
      //   {id: 3, ip: '', checked: false},
      //   {id: 4, ip: '', checked: false}
      // ],
      rules: {
        ip: [
          { required: true, message: '不能为空', trigger: 'blur' },
          { validator: checkIp, trigger: 'blur' }]
      },
      tabData: [],
      selectArr: [],
      selectedItems: [],
      selectAll: false,
      nextIcon: true,
      loadingSubmitText: '确定',
      isSubmitting: false,
      loadingSubmitTextForBatch: '确定',
      isSubmittingForBatch: false
    }
  },
  created () {
    this.getPool()
  },
  watch: {
    selectedItems: function (newSelectedItems) {
      if (newSelectedItems.length === this.batchFormData.length) {
        this.selectAll = true
      } else {
        this.selectAll = false
      }
    }
  },
  methods: {
    uploadSuccess () {
      this.getPool()
    },
    submitUpload () {
      this.$refs.upload.submit()
    },
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      this.getPool()
    },
   
    initializeBatchFormData () {
      let ret = []
      for (let i = 0; i < 5; i++) {
        ret.push({
          id: i,
          ip: '',
          checked: false
        })
      }
      return ret
    },
    getPool () {
      PoolService.getPool(this.currentPage, this.pageSize).then(res => {
        if (res.errcode === 0) {
          this.load = false
          this.tabData = res.pool.map(item => {
            return {ip: item}
          })
          this.total = res.count
        }
      })
    },
    add () {
      this.visible = true
      this.nextIcon = true
    },
    reset () {
      this.visible = false
      // this.batchFormData = this.initializeBatchFormData()
      // this.nextIcon = true
      // 清除checkbox状态
      // this.selectAllChange(false)
      // this.selectedItems = []
      this.$refs['singleForm'].resetFields()
      // this.$refs['batchForm'].map((item) => {
      //   item.resetFields()
      // })
      this.isSubmitting = false 
      this.loadingSubmitText = '确定'
      // this.isSubmittingForBatch = false 
      // this.loadingSubmitTextForBatch = '确定'
    },
    del () {
      $('.el-message').remove()
      if (this.selectArr.length < 1) {
        this.$message({
          showClose: true,
          type: 'error',
          duration: 0,
          message: '请选择要删除的配置'
        })
      } else {
        this.$confirm('此操作将删除所选的设置, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let ipSelect = this.selectArr.map(item => {
            return item.ip
          })
          PoolService.delPool(
            {ips: ipSelect.join(',')}
          )
           .then(res => {
             if (res.errcode === 0) {
               this.$message({
                 showClose: true,
                 type: 'success',
                 duration: 1500,
                 message: '删除成功'
               })
               this.getPool()
             }
           })
        }).catch(() => {})
      }
    },
    changeSelection (selection) {
      this.selectArr = selection
    },
    handleCurrentChange (val) {
      this.currentPage = val
      this.getPool()
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
    rowSelected (item) {
      return this.addSelect.indexOf(item.id) >= 0
    },
    // 添加已勾选的数据
    setupSelectedItems (item) {
      let index = this.selectedItems.indexOf(item.id)
      if (index === -1) {
        this.selectedItems.push(item.id)
      } else {
        this.selectedItems.splice(index, 1)
      }
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

    // 配置接口, 提交数据
    addPool (data, type) {
      $('.el-message').remove()
      PoolService.addPool(data)
        .then((res) => {
          if (res.errcode === 0) {
            this.currentPage = 1
            this.getPool()
            this.visible = false
            this.$message({
              showClose: true,
              message: '添加成功',
              type: 'success',
              duration: '1500'
            })
          }
        }).always(() => {
          if (type === 'single') {
            this.isSubmitting = false 
            this.loadingSubmitText = '确定'
          }

          if (type === 'batch') {
            this.isSubmittingForBatch = false 
            this.loadingSubmitTextForBatch = '确定'
          }
        })
    },
    /**
     * 组装单条提交的数据
     * @returns String
     */
    setupSingleData (item) {
      return {pool: [item.ip]}
    },
    // 单个提交
    submitSingleForm () {
      this.$refs['singleForm'].validate((valid) => {
        if (valid) {
          this.isSubmitting = true 
          this.loadingSubmitText = '添加中'
          this.addPool(this.setupSingleData(this.singleFormData), 'single')
        } else {
          return false
        }
      })
    },

    /**
     * 组装批量提交的数据
     * @returns Array
     */
    setupBatchFormData () {
      let data = this.batchFormData.filter((item) => {
        return this.selectedItems.includes(item.id)
      })
      let ret = {pool: []}
      ret.pool = data.map((item) => {
        return item.ip
      })
      return ret
    },
    // 批量提交
    submitBatchForm () {
      $('.el-message').remove()
      let forms = this.$refs['batchForm']
      let selectedForms = forms.filter((form) => {
        return this.selectedItems.includes(form.model.id)
      })

      if (selectedForms.length === 0) {
        this.$message({
          showClose: true,
          message: '请选择要提交的IP',
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
        this.isSubmittingForBatch = true 
        this.loadingSubmitTextForBatch = '添加中'
        this.addPool(this.setupBatchFormData(), 'batch')
      }
    }
  }
}
</script>
<style scoped lang="scss">
.my-upload {
  display: inline-block;
  padding:5px;
  border:1px dashed #d9d9d9;
}

ul li {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 20px;
}
label {
  font-weight: 600;
  margin-right: 20px;
}
.input_div {
  display: flex;
  align-items: center;
  font-size: 18px;
  span {
    margin: 0 5px;
  }
  .input {
    width: 200px;
  }
}
.butn {
  text-align: right;
  padding: 30px 205px 0;
}
.formtitle {
  display: flex;
  justify-content: flex-end;
  padding-left: 20px;
}
.showNext {
  cursor: pointer;
}

</style>
<style>
.my-upload .el-upload-list {
  float:left
}
.el-alert__content {
  line-height: 16px;
}
.el-alert__title {
  font-size: 12px;
}
.el-select .el-input {
  width: 75px;
}
.guide-1 .el-select .el-input {
  width: 225px;
}
.guide-2 .el-select .el-input {
  width: 120px;
}
.el-dialog__body {
  padding-top: 0;
}
.el-input__inner {
  padding: 0 10px;
}

</style>
