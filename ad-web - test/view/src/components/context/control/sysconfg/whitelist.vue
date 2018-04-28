<template>
    <el-row id ='bwlist'>
      <el-col v-loading='false' element-loading-background="rgba(255, 255, 255, 1)" :span='24'>     
        <div class="content" id='BWlist'>
          <div class="formtitle">
            <div>
              <el-form :inline='true' :model="query" @submit.native.prevent>
                <el-form-item label='查询IP' prop="ip">
                  <el-input 
                    placeholder="请输入查询IP" 
                    size='mini' 
                    v-model="query.ip" 
                    @keyup.enter.native="queryByIp" 
                    clearable>
                  </el-input>
                </el-form-item>
                <el-form-item label='设置集' prop="gether">
                  <el-select v-model="query.gether" style="width: 70px;" @change='getherChange'>
                    <el-option v-for="(item, key) in 16" :key="item.value" :label='key' :value='key'></el-option>
                  </el-select>
                </el-form-item>
                <el-form-item>
                  <el-button icon='el-icon-search' type='primary' size='mini' @click='queryByIp'>查 询</el-button>
                </el-form-item>
              </el-form>
            </div>
            <div>
              <el-button type='primary' size='mini' icon='el-icon-circle-close-outline' @click="clearList">清 空</el-button>
              <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visible = true">添 加</el-button>
              <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
            </div>
          </div>
          <el-table 
            border 
            :data='tableData' 
            @selection-change='changeSelection' 
            :row-class-name="setClassName"
            ref="table">
            <el-table-column type='selection' align='center' width='50px'></el-table-column>
            <el-table-column type='index' align='center' label='序号' width='60px'></el-table-column>
            <el-table-column align='center' label='IP/IP范围'>
              <template slot-scope="scope">
                <el-button v-if='scope.row.isRange' type="text">{{scope.row.ip}}</el-button>
                <span v-else>{{scope.row.ip}}</span>
              </template>
            </el-table-column>
            <el-table-column prop='desc' align='center' label='备注'>
              <template slot-scope="scope">
                {{scope.row.desc ? scope.row.desc : '-'}}
              </template>
            </el-table-column>
            <el-table-column prop='createTime' align='center' label='添加时间'></el-table-column>
            <!-- <el-table-column type='expand' align='center' width='0'>
              <template slot-scope="props">
                <expandVue 
                  :childData='props.row.children' 
                  :total="props.row.count" 
                  :uid='props.$index' 
                  :detailStatus='detailStatus'
                  :ip="props.row.ip"
                  :gether="gether"
                  :type="list_type">
                </expandVue>
              </template>
            </el-table-column> -->
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
          <el-dialog title='添加配置' :visible.sync='visible' width='545px' @close='reset'>
            <el-row style="margin-bottom: 15px">               
              <el-alert title="先选择设置集，然后添加配置" type="info" :closable="false" show-icon></el-alert>
            </el-row>            
            <el-row>
              <el-col :span='24'>
                <el-form 
                  :model="singleFormData" 
                  :rules="rules" 
                  label-width="100px" 
                  ref="singleForm" 
                  @submit.native.prevent>
                  <el-form-item label='IP:' prop='ip' style="width:370px">
                    <el-input v-model="singleFormData.ip" placeholder="IP，IP范围，IP/掩码"></el-input>
                  </el-form-item>
                  <el-form-item label='备注:' prop='desc' style="width:370px;">
                    <el-input 
                      type="textarea" 
                      v-model="singleFormData.desc" 
                      placeholder="备注"
                      :autosize="{minRows: 2, maxRows: 4}"></el-input>
                  </el-form-item>
                  <div style="width:100%;text-align:right">
                    <el-button type='primary' @click="addBlackWhiteList">确 定</el-button>
                    <el-button @click='visible=false'>取 消</el-button>
                  </div>
                </el-form>
              </el-col>
            </el-row>
          </el-dialog>
        </div>
    </el-col>
  </el-row>
</template>
<script>
import ControlService from 'services/controlService'
// import expandVue from './expand'
export default {
  // components: {
  //   expandVue
  // },
  data () {
    const regHandle = (callback, status) => {
      if (!status) {
        callback(new Error('IP格式错误'))
      } else {
        callback()
      }
    }
    const checkIp = (rule, value, callback) => {
      if (!value) {
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
    const checkVal = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('请选择'))
      }
      return callback()
    }
    return {
      load: true,
      currentPage: 1,
      pageSize: 20,
      total: 0,
      visible: false,
      query: {ip: '', gether: '0'},
      activeName: 'first',
      singleFormData: {
        ip: '',
        desc: ''
      },
      rules: {
        ip: [{ validator: checkIp, trigger: 'blur' }],
        gether: [{ validator: checkVal, trigger: 'change' }]
      },
      tableData: [],
      selectArr: [],
      selectAll: false,
      nextIcon: true,
      detailStatus: true,
      list_type: '0' // 白名单
    }
  },
  created () {
    this.getBlackWhiteList({
      page: 1,
      row: 20,
      type: this.list_type,
      gether: this.query.gether
    })
  },
  methods: {
    getherChange (val) {
      this.query.gether = val
      let params = {
        row: this.pageSize,
        filterIp: this.query.ip,
        gether: this.query.gether,
        type: this.list_type
      }
      this.getBlackWhiteList(params)
    },
    getBlackWhiteList (params) {
      // this.currentPage, this.pageSize, this.searchIp.ip, this.searchIp.type
      if (!params) {
        params = {
          page: 1,
          row: this.pageSize,
          filterIp: this.query.ip,
          gether: this.query.gether,
          type: this.list_type
        }
      }
      ControlService.getBlackWhiteList(params).then(res => {
        if (res.errcode === 0) {
          this.load = false
          // let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
          this.tableData = res['6']['data'].map(item => {
            let dataArr = item.split('|')
            return {
              ip: dataArr[0],
              expand: false,
              isRange: false,
              desc: dataArr[1],
              createTime: dataArr[2]
            }
          })
          this.total = res['6']['count']
        }
      })
    },
    expand (row, expandedRows) {
      if (row.isRange) {
        this.$refs.table.toggleRowExpansion(row, false)
        return
      }
      if (row.expand) {
        this.$refs.table.toggleRowExpansion(row, false)
        row.expand = false
      } else {
        this.$refs.table.toggleRowExpansion(row, true)
        this.detailStatus = true
        let params = {
          ip: row.ip,
          type: this.list_type,
          gether: this.query.gether
        }
        ControlService.getIpRangeDetail(params)
          .then((res) => {
            if (res.errcode === 0) {
              row.children = res['6'].data.map((item) => {
                let listArr = item.split('|')
                let obj = {
                  ip: listArr[0],
                  time: listArr[1]
                }
                return obj
              })
              row.count = res['6'].count
              this.detailStatus = false
            }
          })
          .fail(() => {
            this.detailStatus = false
          })
        row.expand = true
      } 
    },
    queryByIp () {
      let params = {
        filterIp: this.query.ip,
        gether: this.query.gether,
        row: this.pageSize,
        type: this.list_type
      }
      this.getBlackWhiteList(params)
    },
    reset () {
      this.$refs['singleForm'].resetFields()
    },
    del () {
      if (this.selectArr.length < 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '请选择要删除的配置'
        })
      } else {
        this.$confirm('此操作将删除当前所选白名单, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
          .then(() => {
            let ips = this.selectArr.map(item => {
              return item.ip
            }).join(',')
            let params = {6: `${this.query.gether}|${this.list_type}|${ips}`}
            ControlService.delBlackWhiteList(params)
              .then(res => {
                if (res.errcode === 0) {
                  this.$message({
                    type: 'success',
                    duration: 1500,
                    message: '删除成功'
                  })
                  this.getBlackWhiteList()
                }
              })
          })
          .catch(() => {
            
          })
      }
    },
    clearList () {
      if (this.tableData.length < 1) {
        this.$message({
          type: 'warning',
          duration: 1500,
          message: '当前配置集日志为空'
        })
      } else {
        this.$confirm('此操作将清空当前所有日志, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
            .then(() => {
              let params = {6: `${this.query.gether}|${this.list_type}`}
              ControlService.clearBlackWhiteList(params)
          .then((res) => {
            if (res.errcode === 0) {    
              this.$message({
                type: 'success',
                duration: 1500,
                message: '清空成功'
              })
              this.getBlackWhiteList()
            }
          })
            })  
        .catch(() => {
          
        })
      }
    },
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      this.getBlackWhiteList()
    },
    changeSelection (selection) {
      this.selectArr = selection
    },
    handleCurrentChange (val) {
      this.currentPage = val
      this.getBlackWhiteList()
    },
    // 配置接口, 提交数据
    // setWhiteList (data) {
    //   ControlService.setWhiteList(data)
    //     .then((res) => {
    //       if (res.errcode === 0) {
    //         this.currentPage = 1
    //         this.searchIp.ip = ''
    //         this.getBlackWhiteList()
    //         this.visible = false
    //         this.$message({
    //           message: '添加成功',
    //           type: 'success',
    //           duration: '1000'
    //         })
    //       }
    //     })
    // },
    // 单个提交
    addBlackWhiteList () {
      this.$refs['singleForm'].validate((valid) => {
        if (valid) {
          let params = {6: `${this.query.gether}|${this.list_type}|${this.singleFormData.ip}|${this.singleFormData.desc}`}
          ControlService.addBlackWhiteList(params)
            .then((res) => {
              if (res.errcode === 0) {
                this.$message({
                  type: 'success',
                  duration: 1500,
                  message: '添加成功'
                })
                this.query.ip = ''
                this.getBlackWhiteList()
                this.visible = false
              }
            })
        } else {
          return false
        }
      })
    },
    setClassName (scope, index) {
      // 通过自己的逻辑返回一个class或者空
      return scope.row.isRange ? 'expand' : ''
    }
  }
}
</script>
<style scoped lang="scss">
#bwlist .el-form {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
}
#bwlist .el-select {
  width: 100px;
}
ul li {
  display: flex;
  align-items: center;
  justify-content: center;
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
  justify-content: space-between;
  padding-left: 20px;
}
.el-select .el-input {
  width: 130px;
}
.input-with-select .el-input-group__prepend {
  width:100px;
  background-color: #fff;
}
.showNext {
  cursor: pointer;
}
</style>
<style>
.expand .el-table__expand-column .cell {
    display: none;
}
#BWlist  .el-table__expanded-cell[class*=cell], .el-table__expanded-cell{
  padding: 0;
}
</style>

