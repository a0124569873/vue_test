<template>
  <el-row>
    <el-col v-loading='false' element-loading-background="rgba(255, 255, 255, 1)" :span='24'>
      <div class="content" id='protectScope'>
        <!-- 查询、添加、删除 -->
        <div class="formtitle">
          <div>
            <el-form :inline='true' @submit.native.prevent>
            <el-form-item label='查询IP'>
              <el-input placeholder="请输入查询IP" size='mini' v-model="searchIp" @keyup.enter.native="searchIP()"></el-input>
            </el-form-item>
            <el-form-item>
              <el-button style="margin-left:20px;" icon='el-icon-search' type='primary' size='mini' @click='searchIP()'>查 询</el-button>
            </el-form-item>
          </el-form>
          </div>
          <div>
              <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="addBtn">添 加</el-button>
              <el-button type='primary' size='mini' icon='el-icon-edit' @click='editBtn'>修改</el-button>
              <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
            </div>
          </div>
          <!-- 表格 -->
          <el-table 
            border 
            :data='tableData' 
            @selection-change='changeSelection'
            ref="table" 
            :row-class-name="setClassName"
            @row-click="handleDetailData"
            style="font-size:0">
             
            <el-table-column type='selection' align='center'></el-table-column>
            <el-table-column type='index' align='center' label='序号'></el-table-column>
            <el-table-column prop='ip' align='center' label='主机IP' style="width:180px">
              <template slot-scope="props">
                <el-button v-if='props.row.isRange' type="text">{{props.row.ip}}</el-button>
                <p v-else>
                  {{props.row.ip}} 
                  <i class="el-icon-edit" style="cursor:pointer" @click="props.row.isRange ? editModal(props.row) : editModal(props.row, props)"></i>
                </p>
              </template>  
            </el-table-column>
            <el-table-column label='设置集' align='center'>
              <el-table-column prop='host' align='center' label='主机防护参数'></el-table-column>
              <el-table-column prop='tcp' align='center' label='TCP端口防护'>
                <template slot-scope="scope">
                  {{String(scope.row.tcp) || '-'}}
                </template>
              </el-table-column>
              <el-table-column prop='udp' align='center' label='UDP端口防护'>
                <template slot-scope="scope">
                  {{String(scope.row.udp) || '-'}}
                </template>
              </el-table-column>
              <el-table-column prop='blacklist' align='center' label='黑名单'>
                <template slot-scope="scope">
                  {{String(scope.row.blacklist) || '-'}}
                </template>
              </el-table-column>
              <el-table-column prop='whitelist' align='center' label='白名单'>
                <template slot-scope="scope">
                  {{String(scope.row.whitelist) || '-'}}
                </template>
              </el-table-column>
              <el-table-column prop='dnswhitelist' align='center' label='DNS白名单'>
                <template slot-scope="scope">
                  {{String(scope.row.dnswhitelist) || '-'}}
                </template>
              </el-table-column>
            </el-table-column>         
            <el-table-column prop='flow_confg' align='center' label='流量策略'></el-table-column>

            <el-table-column type='expand' align='center' width='0'>
              
              <!-- 详情 -->
              <template slot-scope="props">
                <div class="expand-div">
                  <el-table 
                    :data="props.row.children" 
                    height="300" 
                    v-loading="loading" 
                    :empty-text="emptyText"
                    style="width:100%;font-size:0">
                    <el-table-column type='index' align='center' label='序号'></el-table-column>
                    <el-table-column prop='ip' align='center' label='主机IP'>
                      <template slot-scope="scope">
                        <p>{{scope.row.ip}}<i class="el-icon-edit" style="cursor:pointer" @click="editModal(scope.row, scope, props.row)"></i></p>
                      </template>
                    </el-table-column>
                    <el-table-column align='center' label='设置集' width="239">
                      <el-table-column prop='host' align='center' label='主机防护参数' ></el-table-column>
                      <el-table-column prop='tcp' align='center' label='TCP端口防护' >
                        <template slot-scope="scope">
                          {{String(scope.row.tcp) || '-'}}
                        </template>
                      </el-table-column>
                      <el-table-column prop='udp' align='center' label='UDP端口防护' >
                        <template slot-scope="scope">
                          {{String(scope.row.udp) || '-'}}
                        </template>
                      </el-table-column>
                      <el-table-column prop='blacklist' align='center' label='黑名单'>
                        <template slot-scope="scope">
                          {{String(scope.row.blacklist) || '-'}}
                        </template>
                      </el-table-column>
                      <el-table-column prop='whitelist' align='center' label='白名单'>
                        <template slot-scope="scope">
                          {{String(scope.row.whitelist) || '-'}}
                        </template>
                      </el-table-column>
                      <el-table-column prop='dnswhitelist' align='center' label='DNS白名单'>
                        <template slot-scope="scope">
                          {{String(scope.row.dnswhitelist) || '-'}}
                        </template>
                      </el-table-column>
                    </el-table-column>
                    <el-table-column prop='flow_confg' align='center' label='流量策略'></el-table-column>
                  </el-table> 
                  <el-pagination 
                    v-if='props.row.count > 0' 
                    layout="sizes, prev, pager, next" 
                    :total="props.row.count" 
                    :current-page='props.row.page' 
                    :page-sizes="[10, 20, 40, 80, 100]" 
                    :page-size='props.row.size' 
                    @current-change='(val) => { handleDetailCurrentChange(props.row, val) }' 
                    @size-change="(val) => { pageDetailSizeChange(props.row, val) }"
                    style="margin:15px 0;">
                  </el-pagination>
                </div>
              </template>
              </el-table-column>
          </el-table>
          <el-pagination 
            v-if='total > 0' 
            layout="sizes, prev, pager, next" 
            :total="total" 
            :current-page='currentPage' 
            :page-sizes="[10, 20, 40, 80, 100]" 
            :page-size='pageSize' 
            @current-change='handleCurrentChange' 
            @size-change="pageSizeChange"
            style="margin:15px 0;">
          </el-pagination>
      </div>
      <!-- 添加编辑弹窗 -->
      <el-dialog id='protect' :title="oper==='add' ? '添加配置' : '修改配置'" :visible.sync='visible' width='545px' @close='reset'>
        <el-row>
          <el-col :span='24'>
            <el-form :model="FormData" :inline="true" :rules="rules" ref="protectForm">
                <el-form-item label='主机IP' prop='ip' label-width="130px" required>
                  <el-input v-model="FormData.ip" placeholder="请输入 " :disabled="oper==='edit'" style="width: 200px;"></el-input>
                </el-form-item>

                <el-form-item label='主机防护设置集' prop='host' label-width="130px" required>
                  <el-select v-model="FormData.host" placeholder="请选择" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>   

                <el-form-item label='TCP端口设置集' prop='tcp' label-width="130px" required>
                  <el-select v-model="FormData.tcp" placeholder="请选择" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>   

                <el-form-item label='UDP防护设置集' prop='udp' label-width="130px" required>
                  <el-select v-model="FormData.udp" placeholder="请选择" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item> 

                <el-form-item label='黑名单设置集' prop="blacklist" label-width="130px">
                  <el-select v-model="FormData.blacklist" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>

                <el-form-item label='白名单设置集' prop="whitelist" label-width="130px">
                  <el-select v-model="FormData.whitelist" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>

                <el-form-item label='DNS白名单设置集' prop="dnswhitelist" label-width="130px">
                  <el-select v-model="FormData.dnswhitelist" style="width: 200px;">
                    <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>

                <el-form-item label='流量策略' prop='flow_confg_key' label-width="130px" required>
                  <el-select v-model="FormData.flow_confg_key" placeholder="请选择" style="width: 200px;">
                    <el-option key="1" label='忽略所有流量' value='1'></el-option>
                    <el-option key="2" label='屏蔽所有流量' value='2'></el-option>
                    <el-option key="3" label='流量超出屏蔽' value='3'></el-option>
                  </el-select>
                </el-form-item>       
            </el-form>
            <div style="text-align:right">
              <el-button type='primary' @click="submitForm">确 定</el-button>
              <el-button @click='visible=false'>取 消</el-button>
            </div>
          </el-col>
        </el-row>
      </el-dialog>
    </el-col>
  </el-row>
</template>
<script>
import ControlService from 'services/controlService'
import expandVue from './expand'
export default {
  components: {
    expandVue
  },
  data () {
    const regHandle = (callback, status) => {
      if (!status) {
        callback(new Error('IP格式错误'))
      } else {
        callback()
      }
    }
    const checkIp = (rule, value, callback) => {
      if (!String(value)) {
        return callback(new Error('IP不能为空'))
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
    const checkEmpt = (rule, value, callback) => {
      if (!String(value)) {
        callback(new Error('不能为空，请选择'))
      } else {
        callback()
      }
    }
    const checkNum = (rule, value, callback) => {
      if (!String(value)) {
        callback(new Error('不能为空，请输入'))
      }
      value = Number(value)
      if (!Number.isInteger(value) || value < 0) {
        callback(new Error('请输入非负整数值'))
      } else {
        callback()
      }
    }
    return {
      tableData: [],
      total: 0,
      currentPage: 1,
      pageSize: 10,
      visible: false,
      selectArr: [],
      searchIp: '',
      oper: '',
      FormData: {
        id: '',
        ip: '',
        host: 0,
        tcp: 0,
        udp: 0,
        blacklist: '',
        whitelist: '',
        dnswhitelist:'',
        in_flow: '',
        in_pps: '',
        out_flow: '',
        out_pps: '',
        flow_confg_key: ''
      },
      rules: {
        ip: [{ validator: checkIp, trigger: 'blur' }],
        host: [{ validator: checkEmpt, trigger: 'change' }],
        tcp: [{ validator: checkEmpt, trigger: 'change' }],
        udp: [{ validator: checkEmpt, trigger: 'change' }],
        in_flow: [{ validator: checkNum, trigger: 'blur' }],
        in_pps: [{ validator: checkNum, trigger: 'blur' }],
        out_flow: [{ validator: checkNum, trigger: 'blur' }],
        out_pps: [{ validator: checkNum, trigger: 'blur' }],
        flow_confg_key: [{ validator: checkEmpt, trigger: 'change' }]
      },
      loading: true,
      emptyText: ' ',
      isRange: {
        status: false,
        index: null
      }
    }
  },
  created () {
    this.getProtectScope()
  },
  methods: {
    searchIP () {
      this.getProtectScope()
    },
    getProtectScope () {
      let params = {
        page: this.currentPage,
        row: this.pageSize,
        ip: this.searchIp
      }
      ControlService.getProtectScope(params)
        .then((res) => {
          if (res.errcode === 0) {
            let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
            this.tableData = res['7'].data.map((item) => {
              let itemArr = item.split('|')
              let isRange = !regIp.test(itemArr[1])
              let obj = {}
              // if (!isRange) {
              //   obj = {
              //     id: itemArr[0],
              //     ip: itemArr[1],
              //     isRange: !regIp.test(itemArr[1]),
              //     expand: false,
              //     host: '-',
              //     tcp: '-',
              //     udp: '-',
              //     blacklist: '-',
              //     whitelist: '-',
              //     flow_confg_key: '-',
              //     flow_confg: '-',
              //     createTime: itemArr[8]
              //   }
              // } else {
              obj = {
                id: itemArr[0],
                ip: itemArr[1],
                isRange: isRange,
                expand: false,
                host: itemArr[2],
                tcp: String(itemArr[3]),
                udp: String(itemArr[4]),
                blacklist: String(itemArr[5]),
                whitelist: String(itemArr[6]),
                dnswhitelist: String(itemArr[7]),
                flow_confg_key: itemArr[8],
                flow_confg: this.cmpt_flowConfg(itemArr[8]),
                createTime: itemArr[9]
              }
              // }
              return obj
            })
            this.total = res['7']['count']
          } 
        })
    },
    handleDetailData (row, expandedRows) {
      if (!row.isRange) {
        this.$refs.table.toggleRowExpansion(row, false)
        return
      }
      if (row.expand) {
        this.$refs.table.toggleRowExpansion(row, false)
        row.expand = false
      } else {
        this.$refs.table.toggleRowExpansion(row, true)
        this.getIPRangeData(row)
        row.expand = true
      } 
    },
    getIPRangeData (row) {
      this.loading = true
      let params = {
        ipRange: row.ip, 
        page: row.page ? row.page : 1, 
        row: row.row ? row.row : 10
      }
      ControlService.getProtectDetail(params)
        .then((res) => {
          this.loading = false
          this.emptyText = '暂无数据'
          if (res.errcode === 0) {
            row.children = res['7'].data.map((item) => {
              let itemArr = item.split('|')
              return {
                id: itemArr[0],
                ip: itemArr[1],
                host: itemArr[2],
                tcp: itemArr[3],
                udp: itemArr[4],
                blacklist: itemArr[5],
                whitelist: itemArr[6],
                dnswhitelist: itemArr[7],
                flow_confg_key: itemArr[8],
                flow_confg: this.cmpt_flowConfg(itemArr[8]),
                createTime: itemArr[9],
                isRange: false
              }
            })

            row.page = params.page 
            row.row = params.row
            row.count = res['7'].count
          }
        })
        .fail(() => {
          this.loading = false
        })
    },
    submitForm () {
      this.$refs['protectForm'].validate((valid) => {
        if (valid) {
          if (this.oper === 'add') {
            this.add()
          } else if (this.oper === 'edit') {
            this.edit()
          }
        }
      })
    },
    addBtn () {
      this.oper = 'add'
      this.visible = true
    },
    add () {
      let param = {
        '7': `${this.FormData.ip}|${this.FormData.host}|${this.FormData.tcp}|${this.FormData.udp}|${this.FormData.blacklist}|${this.FormData.whitelist}|${this.FormData.dnswhitelist}|${this.FormData.flow_confg_key}`
      }
      ControlService.addProtect(param)
        .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              type: 'success',
              duration: 1500,
              message: '添加成功'
            })

            this.getProtectScope()
            this.visible = false
          }
        })
    },
    editBtn () {
      if (this.selectArr.length < 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '请选择要修改的配置'
        })
        return
      } 
      if (this.selectArr.length > 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '只能修改一项配置'
        })
        return
      }
      this.editBtnModal(this.selectArr[0])
    },
    // detail 表示详情里单条记录所属的上级记录数据
    editModal (modData, scope, detail) {
      this.oper = 'edit'
      // 赋值
      if (scope.row.isRange) {
        this.isRange = {
          status: true,
          index: scope.$index
        }
      } else {
        this.isRange = {
          status: false,
          index: null
        }

        if (detail) {
          this.isRange.isDetail = true
          this.isRange.detail = detail
        }
      }
      this.FormData = JSON.parse(JSON.stringify(modData)) 
      this.visible = true
    },
    editBtnModal (row) {
      this.oper = 'edit'
      if (row.isRange) {
        this.isRange = {
          status: true,
          isChecked: true
        }
      } else {
        this.isRange = {
          status: false,
          index: null
        }
      }

      this.FormData = JSON.parse(JSON.stringify(row)) 
      this.visible = true
    },
    edit () {
      let param = {
        '7': `${this.FormData.id}|${this.FormData.host}|${this.FormData.tcp}|${this.FormData.udp}|${this.FormData.blacklist}|${this.FormData.whitelist}|${this.FormData.dnswhitelist}|${this.FormData.flow_confg_key}`
      }
      let type = this.isRange.status ? 'multi' : ''
      ControlService.editProtect(param, type)
        .then((res) => {
          if (res.errcode === 0) {
            this.$message({
              type: 'success',
              duration: 1500,
              message: '编辑成功'
            })
            if (this.isRange.status) {
              if (this.isRange.isChecked) {
                this.getProtectScope()
              } else {
                this.getIPRangeData(this.tableData[this.isRange.index])
              }
            } else if (this.isRange.isDetail) {
              // isRange: {ip, page, row}
              this.getIPRangeData(this.isRange.detail)
            } else {
              this.getProtectScope()
            }
            this.visible = false
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
        this.$confirm('此操作将删除当前防护范围, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          let ips = this.selectArr.map(item => {
            return item.ip
          }).join('|')
          let param = { '7': ips }
          ControlService.delProtect(param)
            .then((res) => {
              if (res.errcode === 0) {
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '删除成功'
                })
                this.getProtectScope()
                this.visible = false
              }
            })
        })
      }
    },
    reset () { 
      this.$refs['protectForm'].resetFields()
      this.FormData = {
        id: '',
        ip: '',
        host: '',
        tcp: '0',
        udp: '0',
        blacklist: '',
        whitelist: '',
        dnswhitelist: '',
        in_flow: '',
        in_pps: '',
        out_flow: '',
        out_pps: '',
        flow_confg_key: ''
      }
    },
    changeSelection (selection) {
      this.selectArr = selection
    },

    // 一级IP
    // 处理页码跳转
    handleCurrentChange (val) {
      this.currentPage = val
      this.getProtectScope()
    },

    // 处理每页条数改变
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      this.getProtectScope()
    },

    // 二级IP
    // 处理页码跳转
    handleDetailCurrentChange (row, val) {
      row.page = val
      this.getIPRangeData(row)
    },
    // 处理每页条数改变
    pageDetailSizeChange (row, val) {
      row.row = val 
      row.page = 1
      this.getIPRangeData(row)
    },

    cmpt_flowConfg (type) {
      switch (type) {
      case '1':
        return '忽略所有流量'
      case '2':
        return '屏蔽所有流量'
      case '3':
        return '流量超出屏蔽'
      default:
        break
      }
    },
    cmpt_plugins (type) {
      switch (type) {
      case '1':
        return 'WEB插件'
      case '2':
        return 'Game插件'
      case '3':
        return 'DNS插件'
      case '4':
        return 'CC插件'
      default:
        break
      }
    },
    setClassName (scope, index) {
      // 通过自己的逻辑返回一个class或者空
      return !scope.row.isRange ? 'expand' : ''
    }
  }
}
</script>
<style scoped>
  .formtitle {
    display: flex;
    justify-content: space-between;
    padding-left: 20px;
    margin-bottom: 10px;
  }
  i{
    margin-left: 10px;
  }
  .expand-div{
    padding: 15px 45px;
    background: #e4e4e4ab;
  }
  .unit{
    font-size: 12px;
    color:#b4bccc;
    position: absolute;
    top: 0;
    left: 210px;
    width: 80px;
    text-align: left;
  }
</style>
<style>
.expand .el-table__expand-column .cell {
    display: none;
}
#protectScope .el-table__expanded-cell[class*=cell], .el-table__expanded-cell{
  padding: 0;
}
</style>


