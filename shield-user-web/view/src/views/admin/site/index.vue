<template>
  <div class="base-protect">
    <secondBar title="网站防护"></secondBar>
    <guide :activeStep="activeStep" :form2="form2" :form4="form4" @add="onAdd" @review="step3" @linkup="step4" @complete="completed" ref="guide"></guide>
    <domainList :list="list" :total="total" :pageIndex="pageIndex" :pageSize="pageSize" :loading="loading" @pageChange="pageChange" @remove="delayFetch"></domainList>
  </div>
</template>
<script>
import secondBar from 'components/secondBar'
import guide from './guide'
import domainList from './domainList'
import siteService from 'services/siteService'

export default {
  name: 'site',
  components: {
    secondBar,
    guide,
    domainList
  },
  data() {
    return {
      list: [],
      total: 0,
      pageIndex: 1,
      pageSize: 5,
      loading: false,
      activeStep: 1,
      form2: {},
      form4: {}
    }
  },
  methods: {
    poll(params = {}) {
      if(this.loading) return
      this.loading = !this.list.length
      siteService.list({
        _from: (this.pageIndex - 1) * 5,
        _size: this.pageSize,
        ...params
      }).then(res => {
        this.loading = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.list = res.list
        this.total = res.total
      })
    },
    delayFetch(interval = 800) {
      setTimeout(() => this.poll(), interval)
    },
    pageChange(cur) {
      this.pageIndex = cur
      this.poll()
    },
    step1() {
      this.$refs.guide.collapse = false
      this.activeStep = 1
      this.poll()
    },
    step2(id) {
      siteService.textCode(id).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        const { vdomain, utextcode, way } = res
        this.form2 = {
          domain: id,
          vdomain,
          textCode: utextcode,
          way
        }
        this.$refs.guide.collapse = false
        this.activeStep = 2
        this.delayFetch()
      })
    },
    step3(id) {
      const $guide = this.$refs.guide
      $guide.form3.domain = id
      $guide.collapse = false
      this.activeStep = 3
      this.delayFetch()
    },
    step4(id, cname) {
      this.form4 = {
        cname: cname || this.list.find(item => item.id === id).cname,
        domain: id,
        way: 'CNAME'
      }
      this.$refs.guide.collapse = false
      this.activeStep = 4
      this.delayFetch()
    },
    onAdd(id) {
      this.step2(id)
      this.delayFetch()
    },
    completed() {
      this.activeStep = 1
      this.delayFetch()
    }
  },
  created() {
    this.poll()
  },
}
</script>
<style lang="less">
.base-protect {
    .el-card {
        margin-bottom: 20px;
        &__header {
          border-bottom: none;
        }
        &__body {
          padding-top: 0
        }
    }
}
</style>

