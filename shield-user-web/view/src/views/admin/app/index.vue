<template>
  <div class="nonnet-protect">
    <secondBar title="应用防护"></secondBar>
    <addRules @add="addItem" ref="addRules"></addRules>
    <domainList :list="tableData" :total="total" :pageIndex="pageIndex" :pageSize="pageSize" :loading="loading" @pageChange="pageChange" @remove="delayFetch"></domainList>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import domainList from './domainList'
import addRules from './addRules'
import portService from 'services/portService'

export default {
  components: {
    secondBar,
    domainList,
    addRules
  },
  data () {
    return {
      total: 0,
      pageIndex: 1,
      tableData: [],
      pageSize: 5,
      loading: false
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    loadData (params = {}) {
      if(this.loading) return
      this.loading = !this.tableData.length
      portService.nonSiteList({
        _from: (this.pageIndex - 1) * 5,
        _size: this.pageSize,
        ...params
      })
      .then(recvdata => {
        if (recvdata.errcode === 0) {
          this.loading = false
          this.tableData = recvdata.list
          this.total = recvdata.total
        } else {
          this.$message.error(recvdata.errmsg)
        }
      })
    },
    delayFetch(interval = 800) {
      setTimeout(() => this.loadData(), interval)
    },
    addItem(interval = 800) {
      this.delayFetch()
    },
    pageChange(cur) {
      this.pageIndex = cur
    },
    showRules() {
      this.$refs.addRules.collapse = false
      this.loadData()
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';
.domain-cer {
  .el-dialog__body {
    padding-bottom: 0
  }
}
.nonnet-protect {
  &-form {
    .el-select {
      width: 90px;
      .el-input__inner {
        padding-right: 30px
      }
    }
    .el-input__inner {
      padding-left: 10px;
    }
    .el-form-item {
      margin-bottom: 12px;
      &__label {
        font-size: 12px
      }
    }
    .port {
      width: 120px;
      .el-input-group__append {
        padding: 0 12px
      }
    }
  }
  .domain-search {
    .el-select .el-input {
        width: 110px;
    }
    .el-input {
      width: 300px
    }
    .el-input__inner {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
    }
    .el-input-group__append {
      background:#FFFFFF;
      border: none;
      padding-left: 5px;
      .el-button {
        background:#409EFF;
        color:#FFFFFF;
        margin-left: 10px;
      }
    }
  }
}
</style>
