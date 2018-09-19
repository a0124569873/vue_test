<template>
<div class="edit-part">
  <el-card v-loading="loading">
      <header slot="header"
            class="f-x f-js">
      <div class="domain-search f-x f-ac">
        <span>类型：</span>
        <el-select size="mini"
                    v-model="type"
                    @change="searchType"
                    class="mr-lg ml-sm">
          <el-option :value="-1"
                    label="全部"></el-option>
          <el-option :value="0"
                    label="应用ID"></el-option>
          <el-option :value="1"
                    label="高防IP"></el-option>
          <el-option :value="2"
                    label="转发端口"></el-option>
          <el-option :value="3"
                    label="源站IP"></el-option>
          <el-option :value="4"
                    label="源站端口"></el-option>
        </el-select>
        <el-input size="mini"
                  ref="keyword"
                  v-model="keyword"
                  :placeholder="`输入要查找的${kwType}`"
                  @keyup.native.enter="search">
          <!-- <el-select slot="prepend"
                    v-model="kwType">
            <el-option :value="1"
                      label="应用ID"></el-option>
            <el-option :value="2"
                      label="IP"></el-option>
          </el-select> -->
          <el-button slot="append"
                    icon="el-icon-search"
                    @click="search" size="mini">查询</el-button>
        </el-input>
      </div>
      <el-button type="primary" size="mini" @click="showRule">添加规则</el-button>
    </header>
      <detailList :data="list" selection :total="total" :pageSize="pageSize" @pageChange="pageChange" @remove="delItem">
        <template slot="header" slot-scope="{ row }">
          <div>{{row.id}}</div>
          <div>
            <router-link class="btn-link mr-lg" :to="`/app/${row.id}/control`" @click.native="$event.stopPropagation()">控制面板</router-link>
            <router-link class="btn-link ml-lg" :to="`/app/${row.id}/report/banWidth`" @click.native="$event.stopPropagation()">报表</router-link>
          </div>
        </template>
        <el-row slot-scope="{ row }" :gutter="20">
          <el-col :span="8">
            <div class="tit">应用信息</div>
            <div class="x-detail-item">
              <label>源站IP：</label>
              <span>{{row.server_ip.join(',')}}</span>
            </div>
            <div class="x-detail-item">
              <label>源站端口：</label>
              <span>{{row.server_port}}</span>
            </div>
            <div class="x-detail-item">
              <el-button type="text" size="small" @click="editServeIp(row)">编辑</el-button>
            </div>
          </el-col>
          <el-col :span="8">
            <div class="tit">防护配置</div>
            <div class="x-detail-item">
              <label>高防IP数：</label>
              <span>{{row.proxy_ip.length}}</span>
            </div>
            <div class="x-detail-item">
              <label>转发协议：</label>
              <span>{{row.protocol}}</span>
            </div>
            <div class="x-detail-item">
              <label>转发端口：</label>
              <span>{{row.proxy_port}}</span>
            </div>
            <div class="x-detail-item">
              <el-button type="text" size="small" @click="editHighIp(row)">编辑</el-button>
            </div>
          </el-col>
          <el-col :span="8">
            <div class="tit">业务状态</div>
            <div class="x-detail-item">
              <label class="mr-sm">DDOS防护</label>
              <i class="el-icon-success green" v-if="1"></i>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
            <div class="x-detail-item">
              <label class="mr-sm">黑白名单</label>
              <i class="el-icon-success green" v-if="row.filter"></i>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
            <div class="x-detail-item">
              <label class="mr-sm">CNAME自动调度</label>
              <i class="el-icon-success green" v-if="row.name &&row.cname"></i>
              <i class="el-icon-circle-close orange" v-else></i>
            </div>
          </el-col>
        </el-row>
      </detailList>
    </el-card>
    <vd-dialog
      :title="scrFormTitle"
      :visible.sync="editIpVisible"
      :width="'500px'"
      @onCancel="editIpVisible=false"
      @onConfirm="editItem"
      :confirmLoading="subLoading"
      :confirmText="'保存'">
      <el-form
        size="mini"
        :model="editServeIpData"
        label-width="72px"
        ref="editServeIpData"
        :rules="rules"
        @submit.native.prevent
        v-loading="dialogLoading">
        <el-form-item label='源站IP:' prop="server_ips">
          <el-input type="textarea" v-model="editServeIpData.server_ips" resize="none" placeholder="请输入源站IP" class="dialog-textarea"></el-input>
        </el-form-item>
        <el-form-item label='源站端口:' prop="server_port">
          <el-input v-model="editServeIpData.server_port" placeholder="请输入源站端口" style="width: 120px;"></el-input>
        </el-form-item>
      </el-form>
    </vd-dialog>
    <vd-dialog
      :title="ipFormTitle"
      :visible.sync="editHighVisible"
      :width="'750px'"
      @onCancel="editHighVisible=false"
      @onConfirm="editHigh"
      :cofirmDisabled="!editHighIpData.proxy_ips.length"
      :confirmText="'保存'">

      <div>
        <selectIp v-model="editHighIpData.proxy_ips" :type="editHighIpData.type"></selectIp>
        <div class="f-x" style="margin-top: 20px;">
          <div>转发协议：</div>
          <el-select size="mini" style="width: 120px;" v-model="editHighIpData.protocol">
            <el-option :value="'TCP'"
              label="TCP"></el-option>
            <el-option :value="'UDP'"
              label="UDP"></el-option>
          </el-select>
        </div>
        <div class="f-x" style="margin-top: 20px;">
          <div>转发端口：</div>
          <el-input v-model="editHighIpData.proxy_port" size="mini" placeholder="请输入转发端口" style="width: 120px;"></el-input>
        </div>
      </div>
    </vd-dialog>
    </div>
</template>
<script>
import detailList from 'components/detailList'
import selectIp from 'components/selectIp'
import vdDialog from 'components/vdDialog'
import matcher from 'utils/matcher'
import portService from 'services/portService'
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
  data () {
    return {
      currentPage: 1,
      type: -1,
      kwType: '内容',
      keyword: '',
      instance: '',
      ip: '',
      editIpVisible: false,
      editHighVisible: false,
      dialogLoading: false,
      subLoading: false,
      editServeIpData: {
        id: '',
        server_ips: '',
        server_port: ''
      },
      editHighIpData: {
        proxy_ips: []
      },
      ipFormTitle: '',
      scrFormTitle: '',
      rules: {
        server_ips: [{
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
        }],
        server_port: [{
          validator(rule, value, next) {
            if(!value) {
              next(new Error('请填写源站端口'))
              return
            }
            const port = value
            let flag = true
            if(!matcher(port, 'port')) {
              flag = false
              next(new Error('端口格式不正确'))
            }
            if(flag) next()
          },
          trigger: 'blur'
        }]
      }
    }
  },
  methods: {
    delItem (id) {
      this.$confirm('确定要删除吗？', '提示', {
        type: 'error'
      }).then(() => {
        portService.batRemove(id).then(res => {
          if (res.errcode === 0) {
            this.$message({
              showClose: true,
              message: '删除成功！',
              type: 'success',
              duration: 1000
            })
            this.$emit('remove')
          } else {
            this.$message({
              showClose: true,
              message: res.errmsg,
              type: 'warning'
            })
          }
        })
      }).catch(() => { })
    },
    pageChange(cur) {
      this.$emit('pageChange', cur)
    },
    searchType(val) {
      switch(val) {
      case 0:
        this.kwType = '应用ID'
        break
      case 1:
        this.kwType = '高防IP'
        break
      case 2:
        this.kwType = '转发端口'
        break
      case 3:
        this.kwType = '源站IP'
        break
      case 4:
        this.kwType = '源站端口'
        break
      default:
        this.kwType = '内容'
      }
    },
    search() {
      const params = {
        _from: 0
      }
      const { keyword, type } = this
      if(keyword) {
        switch(type) {
        case 0:
          params.id = keyword
          break
        case 1:
          params.proxy_ip = keyword
          break
        case 2:
          params.proxy_port = keyword
          break
        case 3:
          params.server_ip = keyword
          break
        case 4:
          params.server_port = keyword
          break
        }
      }
      this.$parent.loadData(params)
    },
    showRule() {
      this.$parent.showRules()
    },
    editServeIp(row) {
      this.editIpVisible = true
      this.editServeIpData.id = row.id
      this.editServeIpData.server_ips = row.server_ip.join(',')
      this.editServeIpData.server_port = row.server_port
      this.scrFormTitle = `源站信息编辑 - ${row.id}`
    },
    editHighIp({id, type, protocol, proxy_port, proxy_ip}) {
      this.editHighIpData = {
        id,
        type,
        protocol,
        proxy_port,
        proxy_ips: proxy_ip.map(item => ({
          ddos_id: item.instance_id,
          ip: item.ip,
          line: item.line
        }))
      }

      this.ipFormTitle = `高防信息编辑 - ${id}`
      this.editHighVisible = true
    },
    editItem() {
      this.$refs.editServeIpData.validate(valid => {
        if(!valid) return false
        const { id, ...body } = this.editServeIpData
        portService.updateConfig(id, body).then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.editIpVisible = false
          this.$message.success('源站信息修改成功！')
          this.$parent.delayFetch()
        })
      })
    },
    editHigh() {
      const {id, proxy_ips, protocol, proxy_port} = this.editHighIpData
      portService.updateLinkUp(id, {proxy_ips, protocol, proxy_port}).then(res => {
        if (res.errcode === 0) {
          this.editHighVisible = false
          this.$message.success('高防信息修改成功！')
          this.$parent.delayFetch()
        } else {
          this.$message.error(res.errmsg)
        }
      })
    },
    siteInfo(row) {
      this.$router.push(`/app/${row.id}/control`)
      this.$store.commit('EDIT_SITE', {
        site_info: row
      })
    }
  }
}
</script>
<style lang="less">
.edit-part {
  .el-dialog {
    .el-textarea__inner {
      height: 112px;
    }
  }
}


</style>
