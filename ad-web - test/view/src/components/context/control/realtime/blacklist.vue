<template>
    <el-row id ='bwlist'>
      <el-col v-loading='false' element-loading-background="rgba(255, 255, 255, 1)" :span='24'>     
        <div class="content" id='BWlist'>
          <div class="formtitle">
            <!-- <div>
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
                <el-form-item>
                  <el-button icon='el-icon-search' type='primary' size='mini' @click='queryByIp'>查 询</el-button>
                </el-form-item>
              </el-form>
            </div> -->
            <div>
              <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
            </div>
          </div>
          <el-table 
            border 
            :data='tableData' 
            @selection-change='changeSelection' 
            ref="table">
            <el-table-column type='selection' align='center' width='50px'></el-table-column>
            <el-table-column type='index' align='center' label='序号' width='60px'></el-table-column>
            <el-table-column align='center' label='目标IP' prop="target_ip">
            </el-table-column>
            <el-table-column align='center' label='攻击IP' prop="attack_ip">
            </el-table-column>
          </el-table>
          
          <!-- <el-pagination 
            v-if='total > 0' 
            layout="sizes, prev, pager, next" 
            :total="total" 
            :current-page='currentPage' 
            :page-sizes="[10, 20, 40, 80, 100]" 
            :page-size='pageSize' 
            @current-change='handleCurrentChange' 
            @size-change="pageSizeChange"
            style="margin:15px 0;">
          </el-pagination> -->
        </div>
    </el-col>
  </el-row>
</template>
<script>
import CurrentService from 'services/currentService'
// import PageData from 'libs/PageData'
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
      query: {ip: ''},
      activeName: 'first',
      singleFormData: {
        ip: ''
      },
      rules: {
        ip: [{ validator: checkIp, trigger: 'blur' }]
      },
      tableData: [],
      selectArr: [],
      selectAll: false,
      nextIcon: true,
      detailStatus: true,
      list_type: '1', // 黑名单,
      timer: null,
      data: []
    }
  },
  created () {
    this.getTmpBlackWhiteList({
      page: 1,
      row: 20,
      type: this.list_type
    })
  },
  methods: {
    getTmpBlackWhiteList (params) {
      // this.currentPage, this.pageSize, this.searchIp.ip, this.searchIp.type
      if (!params) {
        params = {
          page: 1,
          row: this.pageSize,
          filterIp: this.query.ip,
          type: this.list_type
        }
      }
      CurrentService.getTmpBlackWhiteList(params).then(res => {
        if (res.errcode === 0) {
          this.load = false
          this.tableData = res['19'].b_list.map(item => {
            let dataArr = item.split('|')
            return {
              target_ip: dataArr[0],
              attack_ip: dataArr[1]
            }
          })

          this.timer && clearTimeout(this.timer)
          this.timer = setTimeout(() => {
            this.getTmpBlackWhiteList()
          }, 3000)
        }
      })
    },
    queryByIp () {
      let params = {
        filterIp: this.query.ip,
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
        this.$confirm('此操作将删除当前所选黑名单, 是否继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        })
          .then(() => {
            let ips = this.selectArr.map(item => {
              return `${item.target_ip}|${item.attack_ip}`
            }).join(',')
            let params = {19: ips}
            CurrentService.delTmpBlackWhiteList({params: params, type: this.list_type})
              .then(res => {
                if (res.errcode === 0) {
                  this.$message({
                    type: 'success',
                    duration: 1500,
                    message: '删除成功'
                  })
                  this.getTmpBlackWhiteList()
                }
              })
          })
          .catch(() => {
            
          })
      }
    },
    changeSelection (selection) {
      this.selectArr = selection
      if (this.selectArr.length !== 0) {
        this.timer && clearTimeout(this.timer)
      }
    }
  },
  beforeDestroy () {
    this.timer && clearTimeout(this.timer)
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
  justify-content: flex-end;
  padding-left: 20px;
  margin-bottom: 15px
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

