<template>
  <el-row :element-loading-text="loading_text">
      <el-col v-loading='load' element-loading-background="rgba(255, 255, 255, 1)" :span="24">
          <div class="content">           
            <el-table :data='linkInfo' border>
              <el-table-column type='index' label='序号' width="80" align="center"></el-table-column>
              <el-table-column label='线路ID' prop="uid" align="center"></el-table-column>
              <el-table-column label='前端机WAN口IP' prop="wan" align="center"></el-table-column>
              <el-table-column label='前端机LAN口IP' prop="lan" align="center"></el-table-column>
              <el-table-column label='G设备IP' prop="gip" align="center"></el-table-column>
              <el-table-column label='链路号' prop="linkNum" align="center"></el-table-column>
              <el-table-column label='二层封装格式' prop="dataType" align="center"></el-table-column>
            </el-table>
            <div class="page" style="margin-top:15px" v-if="total>0">
              <el-pagination  @size-change="pageSizeChange" @current-change="handleCurrentChange" :current-page="currentPage"
                  :page-sizes="[10, 20, 40, 80, 100]" :page-size="pageSize" layout="sizes, prev, pager, next " :total="total">
              </el-pagination>      
            </div>       
          </div>
      </el-col>
  </el-row>
</template>
<script>
import LinkInfoService from 'services/linkInfoService'
const THRESHHOLD = 5000

export default {
  data () {
    return {
      load: false,
      loading_text: '',  
      currentPage: 1,
      pageSize: 20,
      linkInfo: [],
      total: 0,
      timer: null
    }
  },
  created () { 
    this.loading_text = '正在努力加载中...'
    // 排序组件会自动调用接口，所以这里不需要再次调用，否则会一开始出现两次调用
    // this._loadData({orderby: this.orderby, limit: this.limit})
  },
  mounted () {
    this.getLinkInfo()
  },
  methods: {
    getLinkInfo () {
      LinkInfoService.getLinkInfo(this.currentPage, this.pageSize)
        .then(
          res => {
            if (res.errcode === 0) {
              if (res['4'] && res['4']['link_stat'] && res['4']['link_stat']['link_stat']) {
                this.linkInfo = res['4']['link_stat']['link_stat'].map((item) => {
                  let key = Object.keys(item)[0]
                  let values = item[key].split(',')
                  return {
                    uid: key,
                    wan: values[0],
                    lan: values[1],
                    gip: values[2],
                    linkNum: values[3],
                    dataType: values[4]
                  }
                })
                this.total = res['4']['link_stat']['count'] * 1
              }
              this.timer && clearTimeout(this.timer) // 先清除timer
              this.timer = setTimeout(() => {
                this.getLinkInfo()
              }, THRESHHOLD)
            }
          }
        ).always(() => {
          this.load = false
        })
    },
    pageSizeChange (val) {
      this.currentPage = 1
      this.pageSize = val 
      this.getLinkInfo()
    },
    handleCurrentChange (val) {
      this.currentPage = val
      this.getLinkInfo()
    }
  },
  beforeDestroy () {
    this.timer && clearTimeout(this.timer)
  }
}
</script>
<style scoped>
.title {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
  font-size: 16px;
}
.monitor-text {
  position:relative;
  top:-2px
}
</style>
