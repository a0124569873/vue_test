<template>
  <el-card class="wrap">
    <el-popover ref="pop1" placement="bottom" width="180" v-model="cacheVisible" popper-class="pop">
      <div class="f-x f-ac mb-md">
        <label class="lbl">关键字</label>
        <input type="text" v-model.trim="cacheForm.keyword" placeholder="输入关键字" class="ipt"></input>
      </div>
      <div class="f-x f-ac mb-md">
        <label class="lbl">缓存时间</label>
        <div class="ipt-wrap">
          <input type="text" v-model.number.trim="cacheForm.expire" class="ipt">
          <select v-model="cacheForm.unit" class="select">
            <option v-for="(val, key) in timeUnits" :key="key" :value="key" :label="val"></option>
          </select>
        </div>
      </div>
      <div style="text-align: center">
        <el-button type="primary" size="mini" :disabled="!cacheForm.keyword || !cacheForm.expire" @click="saveCacheRule">保存</el-button>
      </div>
    </el-popover>
    <el-popover ref="pop2" placement="bottom" width="180" v-model="blackVisible" popper-class="pop">
      <div class="f-x f-ac mb-md">
        <label class="lbl">关键字</label>
        <input type="text" v-model.trim="blackKeyWord" placeholder="输入关键字" class="ipt"></input>
      </div>
      <div style="text-align: center">
        <el-button type="primary" size="mini" :disabled="!blackKeyWord" @click="saveBlackList">保存</el-button>
      </div>
    </el-popover>
    <div><strong>快速设置</strong></div>
    <p class="tips">通过设置你需要的缓存项，来起到加速效果，缓存内容越多加速越明显。请根据实际情况设置，某些内容的缓存也可能会引起网站部分功能失效，建议谨慎设置。</p>
    <el-row :gutter="20">
      <el-col :lg="6" :sm="12">
        <cacheItem label="静态资源" v-model="cacheExpire.static_expire"></cacheItem>
      </el-col>
      <el-col :lg="6" :sm="12">
        <cacheItem label="静态页面" v-model="cacheExpire.html_expire"></cacheItem>
      </el-col>
      <el-col :lg="6" :sm="12">
        <cacheItem label="首页" v-model="cacheExpire.index_expire"></cacheItem>
      </el-col>
      <el-col :lg="6" :sm="12">
        <cacheItem label="目录" v-model="cacheExpire.directory_expire"></cacheItem>
      </el-col>
    </el-row>
    <div style="text-align:right;">
      <el-button size="small" type="primary" :loading="loading" @click="saveExpire">保存</el-button>
    </div>
    <hr class="hr">
    <div><strong class="mr-sm">高级设置</strong><a class="btn-link">如何设置？</a></div>
    <el-row :gutter="20">
      <el-col :span="12">
        <div class="f-x f-js f-ac mb-md">
          <span>自定义缓存策略</span>
          <div>
            <a class="btn-link mr-lg" v-popover:pop1>添加</a>
            <a class="btn-link warn" @click="delCacheRule">删除</a>
          </div>
        </div>
        <el-table border max-height="217" :data="cacheRules" class="table" ref="ruleTable">
          <el-table-column type="selection" width="36"></el-table-column>
          <el-table-column label="网址关键字" prop="keyword" min-width="84"></el-table-column>
          <el-table-column label="缓存时间">
            <template slot-scope="{ row }">{{row.time | timeParse}}</template>
          </el-table-column>
        </el-table>
        <p class="tips">对某些网址进行自定义缓存加速，如您的域名cc.aineuro.com中有特殊缓存需求，可以抽取关键字填写到自定义缓存设置中，如网址http://cc.aineuro.com/soft.asp?id=*，抽取关键字“soft”进行缓存设置，并设置相应缓存时间。自定义缓存策略设置优先于快速设置。</p>
      </el-col>
      <el-col :span="12">
        <div class="f-x f-js f-ac mb-md">
          <span>缓存黑名单</span>
          <div>
            <a class="btn-link mr-lg" v-popover:pop2>添加</a>
            <a class="btn-link warn" @click="delBlackList">删除</a>
          </div>
        </div>
        <el-table border max-height="217" :data="blackList" ref="blackTable" class="table">
          <el-table-column type="selection" width="36"></el-table-column>
          <el-table-column label="网址关键字">
            <template slot-scope="{ row }">{{row}}</template>
          </el-table-column>
        </el-table>
        <p class="tips">对某些特定网址不缓存加速，如http://cc.aineuro.com/caption.jpg不需要缓存，则可以在网址中抽取关键词“caption”填写到缓存黑名单设置项中即可。含有这个关键词的黑名单设置优先于自定义缓存策略设置。</p>
      </el-col>
    </el-row>
  </el-card>
</template>
<script>
import cacheItem from './cacheItem'
import siteService from 'services/siteService'

const timeUnits = {
  day: '天',
  hour: '小时',
  minute: '分钟'
}

export default {
  components: {
    cacheItem
  },
  filters: {
    timeParse(value) {
      const [number, unit] = value.split(':')
      return number + timeUnits[unit]
    }
  },
  data() {
    return {
      timeUnits,
      cacheExpire: {
        static_expire: '',
        html_expire: '',
        index_expire: '',
        directory_expire: ''
      },
      loading: false,
      cacheVisible: false,
      cacheForm: {
        keyword: '',
        expire: '',
        unit: 'day'
      },
      cacheRules: [],
      blackVisible: false,
      blackKeyWord: '',
      blackList: [],
      flag: false
    }
  },
  computed: {
    domain() {
      return this.$route.params.id
    }
  },
  methods: {
    saveExpire() {
      if(this.loading) return
      this.loading = true
      siteService.setCacheExpire(this.domain, this.cacheExpire)
      .then(res => {
        this.loading = false
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.$message.success('缓存有效期设置成功！')
      })
    },
    fetch() {
      siteService.cacheExpire(this.domain).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.cacheExpire = res.cache
      })
    },
    getCacheRules() {
      siteService.cacheWhiteList.get(this.domain).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.cacheRules = res.whiteList
      })
    },
    unitRules(val, time) {
      let reg = /^[0-9]*[1-9][0-9]*$/
      if (!reg.test(time)) {
        this.$message.error('时间必须是正整数！')
        this.flag = false
        return false
      } else {
        this.flag = true
        if(val === 'day') {
          if(time > 365 || time < 1) {
            this.$message.error('天数范围是1~365！')
            this.flag = false
            return false
          }
          this.flag = true
        }
        if(val === 'hour') {
          if(time > 23 || time < 1) {
            this.$message.error('小时范围是1~23！')
            this.flag = false
            return false
          }
          this.flag = true
        }
        if(val === 'minute') {
          if(time > 59 || time < 1) {
            this.$message.error('分钟范围是1~59！')
            this.flag = false
            return false
          }
          this.flag = true
        }
      }
    },
    saveCacheRule() {
      const { keyword, expire, unit } = this.cacheForm
      this.unitRules(this.cacheForm.unit, this.cacheForm.expire)
      if(!this.flag) {
        return false
      }
      siteService.cacheWhiteList.add(this.domain, {
        keyword,
        expire: `${expire}:${unit}`
      }).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.cacheVisible = false
        this.cacheForm = {
          keyword: '',
          expire: '',
          unit: 'day'
        }
        this.getCacheRules()
      })
    },
    delCacheRule() {
      const selection = this.$refs.ruleTable.selection
      if(!selection.length) return
      this.$confirm('确定要删除吗？', '提示', {
        type: 'warning'
      }).then(() => {
        const keywords = selection.map(item => item.keyword)
        siteService.cacheWhiteList.delete(this.domain, { keywords })
        .then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.getCacheRules()
        })
      })
    },
    getBlackList() {
      siteService.cacheBlackList.get(this.domain).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.blackList = res.blacklist
      })
    },
    saveBlackList() {
      siteService.cacheBlackList.add(this.domain, {
        keyword: this.blackKeyWord
      }).then(res => {
        if(res.errcode !== 0) {
          this.$message.error(res.errmsg)
          return
        }
        this.blackVisible = false
        this.blackKeyWord = ''
        this.getBlackList()
      })
    },
    delBlackList() {
      const selection = this.$refs.blackTable.selection
      if(!selection.length) return
      this.$confirm('确定要删除吗？', '提示', {
        type: 'warning'
      }).then(() => {
        siteService.cacheBlackList.delete(this.domain, {
          keywords: selection
        })
        .then(res => {
          if(res.errcode !== 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.getBlackList()
        })
      })
    }
  },
  created() {
    this.fetch()
    this.getCacheRules()
    this.getBlackList()
  }
}
</script>
<style scoped lang="less">
@import '~@/less/variables.less';

.tips {
  margin: 4px 0 10px;
  color: #999;
  font-size: 12px;
}
.wrap {
  max-width: 1100px;
  margin-left: auto;
  margin-right: auto;
}
hr {
  margin-top: 20px;
  margin-bottom: 20px
}
.table {
  min-height: 217px;
  font-size: 12px;
  th {
    font-weight: 400
  }
}
.table-append {
  margin: 0;
  padding: 4px 10px;
  background: #ddeeff
}
.btn-link {
  font-size: 12px
}
.ipt-wrap {
  position: relative;
  height: 24px;
}
.ipt {
  border: 1px solid #ededed;
  padding: 0 4px;
  width: 100%;
  height: 24px;
  line-height: 20px;
  &:focus {
    border-color: @brand-color
  }
}
.select {
  position: absolute;
  right: 1px;
  top: 1px;
  border: none;
  outline: none;
  cursor: pointer;
  height: 22px;
  font-size: 12px;
}
.lbl {
  flex: 0 0 4em;
  margin-right: 8px;
  font-size: 12px;
  text-align: right
}
.pop {
  margin-top: 6px
}
</style>
