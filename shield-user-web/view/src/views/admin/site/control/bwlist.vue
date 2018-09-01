<template>
  <el-card class="bwlist-card">
    <div class="header f-x">
      <span>白名单设置</span>
      <a class="ml-md btn-link">如何设置？</a>
    </div>
    <el-row :gutter="40">
      <el-col :md="12" :sm="24">
        <div class="f-x f-js mb-sm">
          <div>
            <span>网址白名单</span>
            <el-tooltip placement="bottom" effect="light">
              <i class="el-icon-question qsicon"></i>
              <div class="tip-txt" slot="content">
                对某些网址不进行安全检查，如您的网站“http://www.abcdefg.com/admin/bak_database.php”是后台管理员专用的敏感操作，
                会被误拦截，其它第三方未授权用户都不会访问，则可以在网址中抽取关键词“admin”或“database”填写到防御白名单设置项中，
                这样网址中含有这些关键词的网址都不会进行安全拦截，避免误报。
              </div>
            </el-tooltip>
          </div>
          <a class="btn-link" @click="edited1 = true">添加</a>
        </div>
        <editTable :data="urlWhite" :edited.sync="edited1" @change="editUrlWhite">
          <editTableCol label="网址关键词" :required="true"></editTableCol>
        </editTable>
        <p class="green tips">含有这些关键词的网址允许访问。</p>
      </el-col>
      <el-col :md="12" :sm="24">
        <div class="f-x f-js mb-sm">
          <div>
            <span>IP白名单</span>
            <el-tooltip placement="bottom" effect="light">
              <i class="el-icon-question qsicon"></i>
              <div class="tip-txt"  slot="content">
                对某些IP不进行拦截，如您设置1.1.1.1后，该IP在访问您的网站时即使发出攻击请求也不会拦截。
                也可以设置IPC段，如设置1.1.1后，该IPC段所有IP将不做安全检查。
                如设置1.1.1.1-1.1.1.24，该段范围内IP不做安全检查。IP黑名单优先级高于IP白名单。
              </div>
            </el-tooltip>
          </div>
          <a class="btn-link" @click="edited2 = true">添加</a>
        </div>
        <editTable :data="ipWhite" :edited.sync="edited2" @change="editIpWhite">
          <editTableCol label="IP或IP段" :required="true"></editTableCol>
        </editTable>
        <p class="green tips">以上IP或IP段允许访问。</p>
      </el-col>
    </el-row>

    <div class="header f-x">
      <span>黑名单设置</span>
      <a class="ml-md btn-link">如何设置？</a>
    </div>
    <el-row :gutter="40">
      <el-col :md="12" :sm="24">
        <div class="f-x f-js mb-sm">
          <div>
            <span>网址黑名单</span>
            <el-tooltip placement="bottom" effect="light">
              <i class="el-icon-question qsicon"></i>
              <div class="tip-txt"  slot="content">
                对某些网址屏蔽访问，如您的网站后台网址是“http://www.abcdefg.com/admin/”，
                可以在此输入“/admin/”，那么当黑客访问您的网站后台时即使拥有管理员账号密码也无法访问，
                当您需要访问时，可以临时打开后台访问。
              </div>
            </el-tooltip>
          </div>
          <a class="btn-link" @click="edited3 = true">添加</a>
        </div>
        <editTable :data="urlBlack" :edited.sync="edited3" @change="editUrlBlack">
          <editTableCol label="网址关键词" :required="true"></editTableCol>
        </editTable>
        <p class="red tips">含有这些关键词的网址禁止访问。</p>
      </el-col>
      <el-col :md="12" :sm="24">
        <div class="f-x f-js mb-sm">
          <div>
            <span>IP黑名单</span>
            <el-tooltip placement="bottom" effect="light">
              <i class="el-icon-question qsicon"></i>
              <div class="tip-txt"  slot="content">
                对某些IP强制拦截，如您设置1.1.1.1后，该IP再访问您的网站将直接拦截。
                也可以拦截IPC段，如设置1.1.1后，该IPC段所有IP将被拦截。如设置1.1.1.1-1.1.1.24，该段范围内所有IP将被拦截。
                IP黑名单优先级高于IP白名单。
              </div>
            </el-tooltip>
          </div>
          <a class="btn-link" @click="edited4 = true">添加</a>
        </div>
        <editTable :data="ipBlack" :edited.sync="edited4" @change="editIpBlack">
          <editTableCol label="IP或IP段" :required="true"></editTableCol>
        </editTable>
        <p class="red tips">以上IP或IP段禁止访问。</p>
      </el-col>
    </el-row>
  </el-card>
</template>
<script>
import editTable, { editTableCol } from 'components/editTable'
import { getIp2Lo, ipExistsInRange } from 'utils/ip'
import matcher from 'utils/matcher'
import { urlWhiteList, urlBlackList, ipWhiteList, ipBlackList } from 'services/siteService'

export default {
  components: {
    editTable,
    editTableCol
  },
  data() {
    return {
      urlWhite: [],
      edited1: false,
      ipWhite: [],
      edited2: false,
      urlBlack: [],
      edited3: false,
      ipBlack: [],
      edited4: false
    }
  },
  computed: {
    domain() {
      return this.$route.params.id
    }
  },
  methods: {
    fetch() {
      Promise.all([
        urlWhiteList.get(this.domain),
        urlBlackList.get(this.domain),
        ipWhiteList.get(this.domain),
        ipBlackList.get(this.domain),
      ]).then((result) => {
        this.urlWhite = result[0].urlWhitelist
        this.urlBlack = result[1].urlBlacklist
        this.ipWhite = result[2].ipWhitelist
        this.ipBlack = result[3].ipBlacklist
      })
    },
    resHandler(next) {
      return res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        next(res)
        this.$message.success('更新成功！')
      }
    },
    isUrlAvailable(url, list) {
      list = list ? list : []
      return list.every(item => {
        if (url == item) {
          return false
        }
        return true
      })
    },
    editUrlWhite(url, list, next) {
      if (url) {
        if (!this.isUrlAvailable(url, this.urlBlack)) {
            this.$message.error('网址关键词已经存在于黑名单中')
            return false
          }
      }
      urlWhiteList.set(this.domain, {
        urlWhitelist: list
      }).then(this.resHandler(res => {
        this.urlWhite = list
        next()
      }))
    },
    editUrlBlack(url, list, next) {
      if (url) {
        if (!this.isUrlAvailable(url, this.urlWhite)) {
            this.$message.error('网址关键词已经存在于白名单中')
            return false
          }
      }
      urlBlackList.set(this.domain, {
        urlBlacklist: list
      }).then(this.resHandler(res => {
        this.urlBlack = list
        next()
      }))
    },
   
    isIpAvailable(ip, list) {
      list = list ? list : []
       return list.every(item => {
        if (item.indexOf('-') !== -1) {
          if (ipExistsInRange(ip, item)) {
            return false
          } 
        } else {
          if (ip == item) {
            return false
          }
        }
        return true
      })
    },
    checkIp(ip) {
      if (ip) {
        if (ip.indexOf('-') != -1) {
          if (!isIpRangeValid(ip)) {
            this.$message.error('IP段格式不正确')
            return false
          }
        } else {
          if (!matcher(ip, 'ip')) {
            this.$message.error('IP格式不正确')
            return false
          }
        }
      }

      return true
    },
    editIpWhite(ip, list, next) {
      if (!this.checkIp(ip)) {
        return
      }
      
      if (ip && !this.isIpAvailable(ip, this.ipBlack)) {
        this.$message.error('IP已经存在于黑名单中')
        return
      }

      ipWhiteList.set(this.domain, {
        ipWhitelist: list
      }).then(this.resHandler(res => {
        this.ipWhite = list
        next()
      }))
    },
    editIpBlack(ip, list, next) {
      if (!this.checkIp(ip)) {
        return
      }

      if (ip && !this.isIpAvailable(ip, this.ipWhite)) {
        this.$message.error('IP已经存在于白名单中')
        return
      }

      ipBlackList.set(this.domain, {
        ipBlacklist: list
      }).then(this.resHandler(res => {
        this.ipBlack = list
        next()
      }))
    },
  },
  created() {
    this.fetch()
  }
}
</script>
<style scoped lang="less">
@import '~@/less/variables.less';

.header {
  padding-left: 8px;
  margin-bottom: 20px;
  line-height: 16px;
  height: 16px;
  border-left: 2px solid @brand-color;
  span {
    font-size: 16px
  }
  a {
    font-size: 12px;
  }
}
.tips {
  margin-top: 5px;
  margin-bottom: 20px;
  font-size: 12px;
}
.tip-txt {
  word-wrap: break-word;
  font-size: 10px;
}
.qsicon {
  cursor: pointer;
  color: @text-gray-light-color;
  font-size: 16px
}
</style>
<style>
.bwlist-card {
  max-width: 1000px;
  margin: 0 auto
}
.bwlist-card .el-card__body {
  padding: 30px 40px
}
</style>



