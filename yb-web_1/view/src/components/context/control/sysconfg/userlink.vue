<template>
  <div class="content" v-loading="isLoading" :element-loading-text="loadingText" element-loading-background="rgba(255, 255, 255, .8)">  
    <el-row>                 
      <el-alert 
        title=""
        :center="false" 
        type="info" 
        :closable="false" 
        show-icon>
        <template>
          <ul class="tip-list">
            <li>用户名格式为数字、字母、下划线组成、长度不超过20</li>
            <li>用户名不能为CA，TA，SERVER，INDEX，SERIAL，DH1024，DH2048，DH4096；忽略大小写</li>
            <li>私有IP只能是单个IP</li>
            <li>伪装原型IP可以选择多个</li>
          </ul>
        </template>
      </el-alert>                 
    </el-row>        
    <div class="content-inner">
      <div :class="['formtitle', {'hide-table': isHide}]">
        <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visibleDialog = true; isFormSuccess = false">添 加</el-button>
        <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
      </div>

      <el-table id="my-table" ref="myTable" :class="{'hide-table': isHide}" border :data='tableData' @selection-change='selectItem'>
        <el-table-column type='selection' style="width:50px;" align='center'></el-table-column>
        <el-table-column label='用户名' align='center'>
          <template slot-scope="scope">
            <span>
              {{ scope.row.username }}
            </span>
            <!-- <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.username"></el-input> -->
          </template>
        </el-table-column>
        <el-table-column label="VPN服务器" align='center'>
          <template slot-scope="scope">
            <span>
              {{ scope.row.vpn_server }}
            </span>
            <!-- <el-select v-show="scope.row.isEditting" v-model="scope.row.vpn_server" placeholder="请选择" style="width:100%">
              <el-option
                v-for="item in vpnServer"
                :key="item.id"
                :label="item.ip"
                :value="item.ip"
                >
              </el-option>
            </el-select> -->
          </template>
        </el-table-column>
        <el-table-column label="私有IP" align='center'>
          <template slot-scope="scope">
            <span>
              {{ scope.row.p_ip }}
            </span>
            <!-- <el-input v-show="scope.row.isEditting" size="mini" v-model="scope.row.p_ip" @blur="pIpBlur(scope.row, $event)"></el-input>
            <div class="error-text" v-show="scope.row.isError">{{scope.row.errorText}}</div> -->
          </template>
        </el-table-column>
        <el-table-column label="线路ID" align='center'>
          <template slot-scope="scope">
            <span v-show="!scope.row.isEditting">
              {{ scope.row.uid ? scope.row.uid : '--/--' }}
            </span>
            <el-select v-show="scope.row.isEditting" style="width:100%" v-model="scope.row.uid" placeholder="请选择线路ID">
              <el-option v-for="item in idsarr" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </template>
        </el-table-column>

        <el-table-column label="伪装原型IP" align='center' min-width="160px">
          <template slot-scope="scope">
            <div v-show="!scope.row.isEditting"> 
              <ul v-if="scope.row.hide_ip.length">
                <li v-for="ip in scope.row.hide_ip" :key="ip">
                  {{ ip }}
                </li>
              </ul>
              <span v-else>
               --/--
              </span>
            </div>
            <el-select v-show="scope.row.isEditting" multiple v-model="scope.row.hideIps" placeholder="请选择">
              <el-option v-for="item in maskIp" :key="item" :label="item" :value="item"></el-option>
            </el-select>
          </template>
        </el-table-column>

        <el-table-column label="IP变换时间" align='center' min-width="160px">
          <template slot-scope="scope">
            <div v-show="!scope.row.isEditting">
              {{ scope.row.interval.desc }}
            </div>
            <div v-show="scope.row.isEditting">
              <el-input 
                v-model="scope.row.interval.day" 
                style="width:60px;margin-bottom:10px" 
                placeholder="天" 
                @keyup.native="scope.row.interval.day = scope.row.interval.day.replace(/[^\d]/g,'')">
              </el-input>
              <span>天</span>
              <el-input 
                v-model="scope.row.interval.hour" 
                style="width:60px;margin-bottom:10px" 
                placeholder="时"
                @keyup.native="scope.row.interval.hour = scope.row.interval.hour.replace(/[^\d]/g,'')">
              </el-input>
              <span>时</span>
              <el-input 
                v-model="scope.row.interval.minute" 
                style="width:60px" 
                placeholder="分"
                @keyup.native="scope.row.interval.minute = scope.row.interval.minute.replace(/[^\d]/g,'')">
              </el-input>
              <span>分</span>
              <el-input 
                v-model="scope.row.interval.second" 
                style="width:60px" 
                placeholder="秒"
                @keyup.native="scope.row.interval.second = scope.row.interval.second.replace(/[^\d]/g,'')">
              </el-input>
              <span>秒</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" align='center'>
          <template slot-scope="scope">
            <a :href="'/uconnect/getfile?filename=' + scope.row.username + '.tar'" :download="scope.row.username + '.tar'">
              <el-button type="text" size="small">下载证书</el-button>
            </a>
            <el-button v-show="!scope.row.isEditting" type="text" size="small" @click="editRow(scope.row)">编辑</el-button>
            <el-button v-show="scope.row.isEditting" type="text" size="small" @click="updateRow(scope.row)">更新</el-button>
            <el-button v-show="scope.row.isEditting" type="text" size="small" @click="editRowCancel(scope.row)">取消</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="page" v-if="total>0">
        <el-pagination  @size-change="pageSizeChange" @current-change="handleCurrentChange" :current-page="curPage"
            :page-sizes="[10, 20, 40, 80, 100]" :page-size="pageSize" layout="sizes, prev, pager, next " :total="total">
        </el-pagination>      
      </div>
        
      <el-dialog 
        title="添加配置" 
        style="text-align:center" 
        :visible.sync='visibleDialog' 
        @close='reset' 
        width="500px">      
        <div 
          v-if="!isFormSuccess"
          v-loading="isSubmitting" 
          element-loading-text="正在生成证书" 
          element-loading-background="rgba(255, 255, 255, .8)">
          <el-alert 
            title=""
            style="text-align:left;margin-top:-28px;margin-bottom:20px" 
            type="info" 
            :closable="false"
            show-icon>
            <template>
              <ul class="tip-list">
                <li>用户名格式为数字、字母、下划线组成</li>
                <li>用户名不能为CA，TA，SERVER，INDEX，SERIAL，DH1024，DH2048，DH4096；忽略大小写</li>
                <li>私有IP只能是单个IP</li>
                <li>伪装原型IP可以选择多个</li>
              </ul>
            </template>
          </el-alert>                 
          
          <el-form 
            :model="formData" 
            status-icon 
            label-position="right" 
            label-width="95px" 
            :rules="rules" 
            ref="myForm">
            <el-form-item label="用户名" prop="username">
              <el-input v-model="formData.username" placeholder="请输入用户名"></el-input>
            </el-form-item>
             <el-form-item label="VPN服务器" prop="vpn_server">
              <el-select class="my-select" v-model="formData.vpn_server" placeholder="请选择">
                <el-option
                  v-for="(_, key) in vpnServer"
                  :key="key"
                  :label="key"
                  :value="key"
                >
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="私有IP" prop="p_ip">
              <el-input v-model="formData.p_ip" placeholder="请输入IP"></el-input>
              <p style="text-align:left;color:#878d99">({{pipPlaceholder}})</p>
            </el-form-item>
            <el-form-item label="线路ID" prop="uid">
              <el-select class="my-select" v-model="formData.uid" placeholder="请选择线路ID">
                <el-option v-for="item in idsarr" :key="item" :label="item" :value="item"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="伪装原型IP" prop="hideIps">
              <el-select multiple v-model="formData.hideIps" placeholder="请选择" class="my-select">
                <el-option v-for="item in maskIp" :key="item" :label="item" :value="item"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="IP变换时间" prop="interval">
              <el-input 
                v-model="formData.interval.day" 
                style="width:60px;" 
                placeholder="0" 
                @keyup.native="formData.interval.day = formData.interval.day.replace(/[^\d]/g,'')">
              </el-input>
              <span style="margin: 0 5px">天</span>
              <el-input 
                v-model="formData.interval.hour" 
                style="width:60px;" 
                placeholder="0"
                @keyup.native="formData.interval.hour = formData.interval.hour.replace(/[^\d]/g,'')">
              </el-input>
              <span style="margin: 0 5px">时</span>
              <el-input 
                v-model="formData.interval.minute" 
                style="width:60px" 
                placeholder="0"
                @keyup.native="formData.interval.minute = formData.interval.minute.replace(/[^\d]/g,'')">
              </el-input>
              <span style="margin: 0 5px">分</span>
              <el-input 
                v-model="formData.interval.second" 
                style="width:60px" 
                placeholder="0"
                @keyup.native="formData.interval.second = formData.interval.second.replace(/[^\d]/g,'')">
              </el-input>
              <span style="margin: 0 5px">秒</span>
            </el-form-item>
            <div style="text-align:right;margin-right:3px;">
              <el-button type='primary' @click="submitForm('myForm')">确定</el-button>
              <el-button @click="visibleDialog=false">取 消</el-button>
            </div>
          </el-form>
        </div>
        <div v-else>
          <div> 
            <i class="el-icon-success" style="font-size: 80px;color: #09BB07"></i>
            <p style="font-size:18px;margin-top:20px">添加成功</p>
            <div> 
              <a :href="'/uconnect/getfile?filename=' + this.formData.username + '.tar'" :download="this.formData.username + '.tar'">
              <el-button class="download-btn" type="primary" size="big">下载证书</el-button>
              </a>
            </div>
          </div>
        </div>
      </el-dialog>
    </div>
  </div>
</template>
<script>
import UserService from 'services/userService'
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

    const checkHideIps = (rule, value, callback) => {
      if (value.length === 0) {
        callback(new Error('不能为空'))
      } else {
        callback()
      }
    } 
    const checkName = (rule, value, callback) => {
      let name = String(value).trim().toUpperCase()
      switch (name) {
      case 'CA':
      case 'TA': 
      case 'SERVER':
      case 'INDEX': 
      case 'SERIAL':
      case 'DH1024': 
      case 'DH2048': 
      case 'DH4096':
        return callback(new Error('用户名格式不正确'))
      }
      let reg = /^(\w+)$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('用户名格式不正确'))
      } else {
        callback()
      }
    }
    return {
      isFormSuccess: false,
      isSubmitting: false,
      once: true,
      isHide: true,
      isLoading: true,
      loadingText: '',
      tableData: [],
      dupTableData: [],
      curPage: 1,
      pageSize: 10,
      total: 0,
      visibleDialog: false,
      selectArr: [],
      formData: {
        username: '',
        p_ip: '',
        vpn_server: '',
        uid: '',
        hideIps: [],
        interval: {
          day: '',
          hour: '',
          minute: '',
          second: ''
        }
      },
      rules: {
        username: [
          {required: true, message: '不能为空', trigger: 'blur'},
          {validator: checkName, trigger: 'blur'}],
        p_ip: [
          {required: true, message: '不能为空', trigger: 'blur'}, 
          {validator: checkIP, trigger: 'blur'}
        ],
        vpn_server: [
          {required: true, message: '不能为空', trigger: 'change'}
        ],
        uid: [
          {required: true, message: '不能为空', trigger: 'change'}
        ],
        hideIps: [
          {type: 'array', validator: checkHideIps, required: true, message: '不能为空', trigger: 'change'}
        ]
      },
      idsarr: [],
      vpnServer: [],
      maskIp: [] // 伪装原型所有IP
    }
  },
  computed: {
    pipPlaceholder: function () {
      return this.formData.vpn_server ? 'IP只能输入' + this.vpnServer[this.formData.vpn_server] + '这个网段范围的' : '请输入IP'
    }
  },
  created () {
    this._getUids()
    this._getVPNServer()
    this._getMaskIp()
  },
  mounted () {
    this._loadData()
  },
  methods: {
    pIpBlur (row, e) { 
      let value = row.p_ip
      if (value === undefined || value === null || String(value).trim().length === 0) {
        $(e.target).addClass('is-error')
        row.isError = true
        row.errorText = '不能为空'
        return
      } 

      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      let status = regIp.test(value)

      if (!status) {
        $(e.target).addClass('is-error')
        row.isError = true
        row.errorText = 'IP格式错误'
        return
      }
      $(e.target).removeClass('is-error')
      row.isError = false
      row.errorText = ''
    },

    _loadData () {
      if (!this.isLoading) {
        this.isLoading = true
      }
      UserService.getUsers(this.curPage, this.pageSize)
      .then((res) => {
        if (res.errcode === 0) {
          this.tableData = res.connect.map((item) => {
            item.isEditting = false
            item.isError = false
            item.errorText = ''
            item.hideIps = []
            if (item.hide_ip) {
              if (item.hide_ip.indexOf('|') !== -1) {
                item.hide_ip = item.hide_ip.split('|')
              } else if (item.hide_ip.length > 0) {
                item.hide_ip = [item.hide_ip]
              }
            }

            let time = this.$format.tranformSecondsToArr(item.interval)
            item.interval = {
              desc: this.$format.formatTime(item.interval),
              day: time.day,
              hour: time.hour,
              minute: time.minute,
              second: time.second
            }
            
            return item
          })

          this.dupTableData = JSON.parse(JSON.stringify(this.tableData))
          this.total = res.count        
        }
      })
      .always(() => {
        this.isLoading = false
        this.isHide = false
      })
    },

    _getUids () {
      UserService.getUids()
      .then((res) => {
        if (res.errcode === 0) {
          this.idsarr = res.uids 
        }
      })
    },

    _getVPNServer () {
      UserService.getVPNServers()
        .then((res) => {
          if (res.errcode === 0) {
            let ret = {}
            res['ips'].map((item, index) => {
              let arr = item.split('|')
              ret[arr[0]] = arr[1]
              if (index === 0) {
                this.formData.vpn_server = arr[0]
              }
            })
            this.vpnServer = ret
          }
        })
    },

    // 获取伪装原型所有IP
    _getMaskIp () {
      UserService.getMaskIp().then((res) => {
        if (res.errcode === 0) {
          this.maskIp = res.pool     
        }
      })
    },
   
    showHideIPs (scope) {
      this.$refs['myTable'].toggleRowExpansion(scope.row)
    },
    pageSizeChange (val) {
      this.pageSize = val
      this._loadData()
    },
    handleCurrentChange (val) {
      this.curPage = val
      this._loadData()
    },
    editRow (row) {
      if (row.uid === 0) {
        row.uid = ''
      }
      
      if (row.hide_ip.length) {
        row.hideIps = row.hide_ip.map((item) => {
          return item
        })
      }
      
      row.isEditting = true
    },
    updateRow (row) {
      $('.el-message').remove()
      if (!String(row.uid).length) {
        this.$message({
          showClose: true,
          message: '线路ID不能为空',
          type: 'error',
          duration: 0
        })
        return
      }

      if (row.hideIps.length === 0) {
        this.$message({
          showClose: true,
          message: '伪装原型IP不能为空',
          type: 'error',
          duration: 0
        })
        return
      }
      this.isLoading = true
      this.loadingText = '正在更新中...'

      let params = {
        id: row.id
      }

      if (row.uid) {
        params.uid = row.uid
      }

      if (row.hideIps.length) {
        params.hide_ip = row.hideIps.join('|')
      }

      params.interval = this.$format.formatSeconds(row.interval.day,
                                                   row.interval.hour,
                                                   row.interval.minute,
                                                   row.interval.second)
      
      UserService.updateUser(params)
        .then((res) => {
          if (res.errcode === 0) {
            // row.hide_ip = row.hideIps.slice(0)
            row.isEditting = false
            this.$message({
              showClose: true,
              message: '更新成功',
              type: 'success',
              duration: '1500'
            })
          }
          this.loading = false
          this.loadingText = ''
          this._loadData()
        }, () => {
          this.loading = false
          this.loadingText = ''
          this._loadData()
        })
    },
    editRowCancel (row) {
      row.hideIps = []
      row.isEditting = false
      let originRows = this.dupTableData.filter(item => {
        return item.id === row.id
      })

      if (originRows.length) {
        row.username = originRows[0].username
        row.uid = originRows[0].uid
        row.p_ip = originRows[0].p_ip
        row.hide_ip = originRows[0].hide_ip
        row.interval = originRows[0].interval
      }
    },

    del () {
      $('.el-message').remove()
      if (!this.selectArr.length) {
        this.$message({
          showClose: true,
          message: '请选择要删除的用户',
          type: 'error',
          duration: 0
        })
        return
      }
      
      this.$confirm('确定删除该用户?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        this.isLoading = true
        this.loadingText = '正在删除中...'

        let ids = this.selectArr.map(item => {
          return item.id
        }).join(',')

        UserService.delUser({ids: ids})
          .then((res) => {
            if (res.errcode === 0) {
              this.$message({
                showClose: true,
                message: '删除成功！',
                type: 'success',
                duration: '1500'
              })
            } 
            this.isLoading = false
            this.loadingText = ''
            this._loadData()
          }, () => {
            this.isLoading = false
            this.loadingText = ''
            this._loadData()
          })
      })
      .catch(() => {})
    },
    reset () {
      this.formData = {
        username: '',
        p_ip: '',
        vpn_server: Object.keys(this.vpnServer)[0],
        uid: '',
        hideIps: []
      }
      if (this.$refs['myForm']) {
        this.$refs['myForm'].clearValidate()// 清除校验结果
      }
    },
    selectItem (item) {
      this.selectArr = item
    },
    submitForm (formName) {
      $('.el-message').remove()
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.isSubmitting = true
          let params = {
            username: this.formData.username,
            p_ip: this.formData.p_ip,
            vpn_server: this.formData.vpn_server
          }
          if (this.formData.uid) {
            params.uid = this.formData.uid
          }

          if (this.formData.hideIps.length) {
            params.hide_ip = this.formData.hideIps.join('|')
          }

          params.interval = this.$format.formatSeconds(this.formData.interval.day, 
                                                       this.formData.interval.hour, 
                                                       this.formData.interval.minute, 
                                                       this.formData.interval.second)

          UserService.addUser(params)
            .then((res) => {
              this.isSubmitting = false
              if (res.errcode === 0) {
                this.$message({
                  showClose: true,
                  message: '添加成功',
                  type: 'success',
                  duration: '1500'
                })
                this._loadData()
                this.$refs['myForm'].clearValidate()// 清除校验结果
                this.isFormSuccess = true
              }
            })
            .always(() => {
              this.isSubmitting = false
            })
        }
      })
    }
  }
}
</script>
<style lang="scss">
.tip-list {
  li:not(:last-child) {
    margin-bottom: 10px
  }
}
.download-btn,
.download-btn:focus {
  margin-top:30px;
  border-color:#09BB07;
  background-color:#09BB07
}
.download-btn:hover {
  border-color:rgba(9, 187, 7, .7);
  background-color:rgba(9, 187, 7, .7);
}
.is-error,
.is-error:hover {
  border-color: #fa5555;
}
.error-text {
  color: #fa5555;
  line-height: 1;
  padding-top: 1px
}
.my-select {
  width: 100%;
  .el-input {
    width: 100% !important
  }
}
.expand-table,
.expand-table table {
  height: 200px;
  overflow: auto
}
.expand-table table {
  width: 100% !important;
}
#my-table table {
  table-layout: auto
}
.demo-table-expand {
  font-size: 0;
}
.demo-table-expand label {
  width: 0;
  color: #99a9bf;
}
.demo-table-expand .el-form-item {
  margin-right: 0;
  margin-bottom: 0;
  width: 0
}
.hide-table {
  opacity: 0;
}
.pwd-input .el-input__inner {
  border: 0;
  background: transparent
}
</style>
<style scoped>
.content-inner {
  margin-top: 10px;
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
.el-row{
  margin-bottom: 10px;
}
.el-input{
  width: 100%;
}
.green{
  color: #5daf34;
}
.red{
  color: #ff3600;
}
.page{
  margin: 15px 0;
}
</style>