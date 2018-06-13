<template>
  <el-card v-loading="loading">
    <header slot="header"
            class="f-x f-js">
      <div class="domain-search f-x f-ac">
        <span>业务类型：</span>
        <el-select size="mini"
                    v-model="type"
                    class="mr-lg ml-sm">
          <el-option :value="-1"
                    label="全部"></el-option>
          <el-option :value="1"
                    label="共享型"></el-option>
          <el-option :value="2"
                    label="独享型"></el-option>
        </el-select>
        <el-input size="mini"
                  ref="keyword"
                  v-model="keyword"
                  :placeholder="`输入要查找的${kwType === 1 ? '域名' : 'IP'}`"
                  @keyup.native.enter="search">
          <el-select slot="prepend"
                    v-model="kwType">
            <el-option :value="1"
                      label="域名"></el-option>
            <el-option :value="2"
                      label="IP"></el-option>
          </el-select>
          <el-button type="primary" slot="append"
                    icon="el-icon-search"
                    @click="search" size="mini">查询</el-button>
        </el-input>
      </div>
      <el-button type="primary" size="mini" @click="showGuide">添加域名</el-button>
    </header>
    <detailList :data="list" :total="total" :pageSize="pageSize" selection @pageChange="pageChange" @remove="delItem" class="domain-table">
      <template slot="header" slot-scope="{ row }">
        <div>{{row.id}}</div>
        <div>
          <router-link class="btn-link mr-lg" :to="`/site/${row.id}/control`" @click.native="$event.stopPropagation()">控制面板</router-link>
          <router-link class="btn-link ml-lg" :to="`/site/${row.id}/report`" @click.native="$event.stopPropagation()">报表</router-link>
        </div>
      </template>
      <el-row slot-scope="{ row }" :gutter="20">
        <el-col :span="10">
          <div class="tit">域名信息</div>
          <div class="x-detail-item">
            <label>CNAME：</label>
            <span v-if="row.cname" class="mr-sm">{{row.cname}}</span>
            <div v-if="hasNextStep(row.status)">
              <i class="el-icon-warning orange"></i>
              <span class="ml-md mr-md" v-if="row.status < 3">未完成接入</span>
              <a class="btn-link" @click="toGuide(row)">继续</a>
            </div>
          </div>
          <div class="x-detail-item">
            <label>源站IP：</label>
            <span>{{row.upstream}}</span>
          </div>
          <div class="x-detail-item">
            <label>端口：</label>
            <span>{{row | sitePort}}</span>
          </div>
          <div class="x-detail-item">
            <el-button type="text" size="small" @click="editSrc(row)">编辑</el-button>
          </div>
        </el-col>
        <el-col :span="7">
          <div class="tit">高防实例与线路</div>
          <template v-if="row.type !== 0">
            <div class="x-detail-item">{{typeOption[row.type] || typeOption[0]}}</div>
            <div class="x-detail-item">
              <a v-if="hasNextStep(row.status)"
                class="orange"
                @click="toGuide(row)">{{statusOption[row.status]}}</a>
              <span v-else
              :class="statusClass(row.status)">{{statusOption[row.status]}}</span>
            </div>
            <div class="x-detail-item">
              <el-button type="text" size="small" @click="editIp(row)">编辑</el-button>
            </div>
          </template>
          <div class="x-detail-item" v-else>—</div>
        </el-col>
        <el-col :span="7">
          <div class="tit">业务状态</div>
          <template v-if="row.type !== 0">
            <div class="x-detail-item">
              <label class="mr-sm">HTTPS</label>
              <div v-if="Number(row.https) === 1">
                <template v-if="!row.https_cert">
                  <i class="el-icon-warning orange"></i>
                  <span class="ml-md">未上传证书</span>
                  <router-link v-if="!row.https_cert" class="ml-md btn-link" :to="`/site/${row.id}/control/https`">上传</router-link>
                </template>
                <i class="el-icon-success green" v-else></i>
              </div>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
            <div class="x-detail-item">
              <label class="mr-sm">HTTP</label>
              <span>
                <i class="el-icon-success green" v-if="Number(row.http) === 1"></i>
                <i class="el-icon-circle-close orange" v-else></i>
              </span>
            </div>
            <div class="x-detail-item">
              <label class="mr-sm">缓存</label>
              <i class="el-icon-success green" v-if="!!row.cache"></i>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
            <div class="x-detail-item">
              <label class="mr-sm">黑白名单</label>
              <i class="el-icon-success green" v-if="!!row.filter"></i>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
          </template>
          <div class="x-detail-item" v-else>—</div>
        </el-col>
      </el-row>
    </detailList>

    <vd-dialog
      :visible.sync="srcVisible"
      :title="scrFormTitle"
      :width="'500px'"
      :confirmText="'保存'"
      @onCancel="srcVisible = false"
      @onConfirm="saveSrc"
      >
      <el-form label-width="100px" :model="srcForm" :rules="srcFormRules" ref="srcForm">
        <el-form-item label="源站IP：" prop="upstream">
          <el-input type="textarea" :rows="5" v-model="srcForm.upstream"></el-input>
        </el-form-item>
        <el-form-item label="协议类型：">
          <el-checkbox :true-label="1" :false-label="0" v-model="srcForm.http">HTTP</el-checkbox>
          <el-checkbox :true-label="1" :false-label="0" v-model="srcForm.https">HTTPS</el-checkbox>
        </el-form-item>
      </el-form>
    </vd-dialog>

    <vd-dialog
      :visible.sync="ipVisible"
      :title="ipFormTitle"
      :width="'750px'"
      :confirmText="'保存'"
      @onCancel="ipVisible = false"
      @onConfirm="saveNewIp"
      :cofirmDisabled="!ipForm.linked_ips.length">
      <selectIp v-model="ipForm.linked_ips" :type="ipForm.type"></selectIp>
    </vd-dialog>
  </el-card>
</template>
<script>
import siteService from 'services/siteService'
import detailList from 'components/detailList'
import selectIp from 'components/selectIp'
import vdDialog from 'components/vdDialog'
import matcher from 'utils/matcher'

export default {
  components: {
    detailList,
    selectIp,
    vdDialog
  },
  props: {
    list: Array,
    loading: Boolean,
    total: Number,
    pageSize: Number
  },
  filters: {
    sitePort({ http, https }) {
      const arr = []
      if(Number(http) === 1) arr.push(80)
      if(Number(https) === 1) arr.push(443)
      return arr.join(', ')
    }
  },
  data() {
    return {
      type: -1,
      kwType: 1,
      keyword: '',
      typeOption: siteService.TYPE,
      statusOption: siteService.STATUS,
      srcVisible: false,
      srcForm: {},
      scrFormTitle: '',
      srcFormRules: {
        upstream: [{
          validator(rule, value, next) {
            if(!value) {
              next(new Error('请填写源站IP'))
              return
            }
            const ipArr = value.split(',')
            if(ipArr.length > 10) {
              next(new Error('最多只能输入10个IP'))
              return
            }
            let flag = true
            ipArr.forEach(str => {
              if(!matcher(str, 'ip')) {
                flag = false
                next(new Error('IP格式不正确'))
              }
            })
            if(flag) next()
          },
          trigger: 'blur'
        }]
      },
      ipVisible: false,
      ipFormTitle: '',
      ipForm: {
        linked_ips: []
      }
    }
  },
  computed: {
    tableData() {
      return this.type === -1 ? this.list
        : this.list.filter(item => item.type === this.type)
    },
  },
  methods: {
    statusClass(status) {
      return { red: status === 5 || status === 7, green: status === 4, orange: status === 2 || status === 6 }
    },
    hasNextStep(status) {
      return status === 0 || status === 1 || status === 3
    },
    delItem(ids) {
      this.$confirm('确定要删除吗？', '提示', {
        type: 'error'
      }).then(() => {
        siteService.batRemove(ids).then(res => {
          if (res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.$emit('remove')
        })
      }).catch(() => { })
    },
    toGuide({ id, status }) {
      if(status === 0) {
        this.$parent.step2(id)
      } else if(status === 1) {
        this.$parent.step3(id)
      } else {
        this.$parent.step4(id)
      }
      this.$nextTick(() => {
        this.$parent.$el.scrollTop = 0
      })
    },
    search() {
      const params = {
        _from: 0
      }
      const { kwType, keyword, type } = this
      if(keyword) {
        if(kwType === 1) {
          params.name = keyword
        } else {
          params.ip = keyword
        }
      }
      if(type !== -1) {
        params.type = type
      }
      this.$parent.poll(params)
    },
    showGuide() {
      this.$parent.step1()
    },
    pageChange(cur) {
      this.$emit('pageChange', cur)
    },
    showFileDialog(id) {
      this.cerForm = {
        id,
        certificate: '',
        certificate_key: ''
      }
      this.visible = true
    },
    editSrc({ id, http, https, upstream }) {
      this.srcForm = { id, http: Number(http), https: Number(https), upstream }
      this.scrFormTitle = `源站信息编辑 - ${id}`
      this.srcVisible = true
    },
    saveSrc() {
      this.$refs.srcForm.validate(valid => {
        if(!valid) return false
        const { id, ...body } = this.srcForm
        siteService.updateConfig(id, body).then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.srcVisible = false
          this.$message.success('源站信息修改成功！')
          this.$parent.delayFetch()
        })
      })
    },
    editIp({ id, type, proxy_ip }) {
      this.ipForm = {
        id,
        type,
        linked_ips: proxy_ip.map(item => ({
          ddos_id: item.instance_id,
          ip: item.ip,
          line: item.line
        }))
      }
      this.ipFormTitle = `高防信息编辑 - ${id}`
      this.ipVisible = true
    },
    saveNewIp() {
      const { id, linked_ips } = this.ipForm
      siteService.updateLinkup(id, { linked_ips }).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.ipVisible = false
        this.$message.success('实例信息修改成功！')
        this.$parent.delayFetch()
      })
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.domain-search {
    .el-select .el-input {
        width: 90px;
    }
    .el-input {
      width: 350px;
    }
    .el-input__inner {
      border-top-right-radius: 4px;
      border-bottom-right-radius: 4px;
    }
    .el-input-group__append {
      background:#FFFFFF;
      border: none;
      padding-left: 35px;
      .el-button {
        background:#409EFF;
        color:#FFFFFF;
      }
    }
}
.domain-cer {
  .el-dialog__body {
    padding-bottom: 0
  }
}
</style>
