<template>
  <el-card class="bw_cont">
    <h3 slot="header">黑白名单设置</h3>
    <el-row :gutter="40">
      <el-col :span="12">
        <div class="f-x f-js mb-sm">
          <span>IP黑名单</span>
          <a class="btn-link" @click="edited1 = true">添加</a>
        </div>
        <editTable :data="blacklist" :edited.sync="edited1" @change="updateBlack">
          <editTableCol label="IP或IP段" :required="true"></editTableCol>
        </editTable>
        <p class="red tips">含有这些关键词的网址禁止访问。</p>
      </el-col>
      <el-col :span="12">
        <div class="f-x f-js mb-sm">
          <span>IP白名单</span>
          <a class="btn-link" @click="edited2 = true">添加</a>
        </div>
        <editTable :data="whitelist" :edited.sync="edited2" @change="updateWhite">
          <editTableCol label="IP或IP段" :required="true"></editTableCol>
        </editTable>
        <p class="green tips">含有这些关键词的网址允许访问。</p>
      </el-col>
    </el-row>
  </el-card>
</template>
<script>
import editTable, { editTableCol } from 'components/editTable'
import { getIp2Lo, ipExistsInRange } from 'utils/ip'
import matcher from 'utils/matcher'
import { whiteBlackList } from 'services/portService'

export default {
  components: {
    editTable,
    editTableCol
  },
  data() {
    return {
      blacklist: [],
      edited1: false,
      whitelist: [],
      edited2: false,
    }
  },
  computed: {
    portId() {
      return this.$route.params.id
    }
  },
  methods: {
    fetch() {
      whiteBlackList.get(this.portId).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        const { ipBlacklist, ipWhitelist } = res.data
        this.blacklist = ipBlacklist
        this.whitelist = ipWhitelist
      })
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
    updateBlack(ip, ipBlacklist, next) {
       if (!this.checkIp(ip)) {
        return
      }
      
      if (ip && !this.isIpAvailable(ip, this.ipBlack)) {
        this.$message.error('IP已经存在于黑名单中')
        return
      }
      console.log(ipBlacklist)
      this.updateList('Black', { ipBlacklist }, () => {
        this.blacklist = ipBlacklist
        next()
      })
    },
    updateWhite(ip, ipWhitelist, next) {
       if (!this.checkIp(ip)) {
        return
      }
      
      if (ip && !this.isIpAvailable(ip, this.ipBlack)) {
        this.$message.error('IP已经存在于黑名单中')
        return
      }
      this.updateList('White', { ipWhitelist }, () => {
        this.whitelist = ipWhitelist
        next()
      })
    },
    updateList(type, body, next) {
      console.log(body)
      whiteBlackList[`set${type}`](this.portId, body).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.$message.success('更新成功！')
        next()
      })
    }
  },
  created() {
    this.fetch()
  }
}
</script>
<style scoped lang="less">
.bw_cont {
  max-width: 1000px;
  margin: 0 auto;
  h3 {
    color: #666666;
    font-size: 16px;
    font-weight: normal;
  }
}
.tips {
  margin-top: 5px;
  font-size: 12px
}
</style>
