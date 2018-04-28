<template>
<div class="expandDiv">
    <el-table :data='childData' max-height="300" v-loading="detailStatus">
      <el-table-column type='index' align='center' label='序号' width='60px'></el-table-column>
      <!-- <el-table-column prop='type' align='center' label='类型'></el-table-column> -->
      <el-table-column prop='ip' align='center' label='IP/IP范围'></el-table-column>
      <el-table-column prop='time' align='center' label='添加时间'></el-table-column>
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
</template>
<script>
import ControlService from 'services/controlService'
export default {
  props: {
    childData: {
      type: Array
    },
    uid: {
      type: Number
    },
    detailStatus: {
      type: Boolean
    },
    total: {
      type: Number
    },
    ip: {
      type: String
    },
    gether: {
      type: String
    },
    type: {
      type: String
    }
  },
  data () {
    return {
      currentPage: 1,
      pageSize: 10
    }
  },
  methods: {
    handleCurrentChange (val) {
      this.currentPage = val
      let params = {
        ip: this.ip,
        type: this.type,
        gether: this.gether,
        page: this.currentPage,
        row: this.pageSize
      }
      ControlService.getIpRangeDetail(params)
          .then((res) => {
            if (res.errcode === 0) {
              this.childData = res['6'].data.map((item) => {
                let listArr = item.split('|')
                let obj = {
                  ip: listArr[0],
                  time: listArr[1]
                }
                return obj
              })
              this.total = res['6'].count
            }
          })
    },
    pageSizeChange (val) {
      this.pageSize = val
      this.currentPage = 1
      let params = {
        ip: this.ip,
        type: this.type,
        gether: this.gether,
        page: this.currentPage,
        row: this.pageSize
      }
      ControlService.getIpRangeDetail(params)
          .then((res) => {
            if (res.errcode === 0) {
              this.childData = res['6'].data.map((item) => {
                let listArr = item.split('|')
                let obj = {
                  ip: listArr[0],
                  time: listArr[1]
                }
                return obj
              })
              this.total = res['6'].count
            }
          })
    }
  }
}
</script>
<style scoped>
.expandDiv{
  padding: 20px 50px;
  background: #e4e4e4ab;
}
</style>


