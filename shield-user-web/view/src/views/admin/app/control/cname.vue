<template>
  <div class="cname_cont">
    <el-row type="flex"
            justify="center">
      <el-col :span="24">
        <el-card>
          <div style="width: 360px;margin: 0 auto;">
            <el-form ref="form" :model="formData">
              <el-form-item label="域名：">
                <el-input type="textarea" placeholder="请输入域名" style="height: 120px;" v-model="formData.name"></el-input>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" size="small" :disabled="!formData.name || formData.name == name" @click="newCName">生成CNAME</el-button>
              </el-form-item>
            </el-form>
            <div class="cname-status">
              <div>CNAME：</div>
              <div class="text-cont">
                <span>{{ cname || 'N/A'}}</span>
              </div>
              <el-button 
                type="primary" 
                size="small" 
                :disabled="!hasNewCName" 
                @click="activeCName"
                style="margin-bottom: 22px;">{{name && cname? '更新': '启用'}}CNAME</el-button>
              <div>状态：</div>
              <div class="text-cont">
                <span v-if="name && cname && !hasNewCName">成功接入<i class="el-icon-success green"></i></span>
                <span v-else>未成功接入<i class="el-icon-warning orange"></i></span>
              </div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>
<style lang="less">
  .cname_cont {
    .el-textarea__inner {
      height: 120px;
    }
  }
</style>
<style scoped lang="less">
  .text-cont {
    position: relative;
    text-align:center;
    margin: 9px 0 30px 0;
    height: 120px;
    background: #efefef;
    border: 1px solid #dddddd;
    span {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      .el-icon-warning {
        display: block;
        font-size: 34px;
        margin-top: 12px;
      }
    }
  }
  .el-textarea {
    .el-textarea__inner {
      min-height: 120px!important;
    }
  }
</style>
<script>
import portService from 'services/portService'
export default {
  data() {
    return {
      formData: {
        name: '',
      },
      name: '',
      cname: '',
      hasNewCName: false
    }
  },
  computed: {
    id() {
      return this.$route.params.id
    }
  },
  created() {
    portService.getConfig(this.id)
      .then(res => {
        
        if (res.errcode != 0) {
          this.$message.error(res.errmsg)
          return
        }

        this.name = res.data.name
        this.cname = res.data.cname
        this.formData.name = res.data.name
      })
  },
  methods: {
    newCName() {
      portService.generateCName(this.id, {domain: this.formData.name})
        .then(res => {
          if (res.errcode != 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.$message.success('生成CName成功')
          this.name = this.formData.name
          this.cname = res.cname
          this.hasNewCName = true
        })
    },
    activeCName() {
      portService.activeCName(this.id, {domain: this.name, cname: this.cname})
        .then(res => {
          if (res.errcode != 0) {
            this.$message.error(res.errmsg)
            return
          }
          this.$message.success('更新CName成功')
          this.formData.name = this.name
          this.hasNewCName = false
        })
    }
  }
}
</script>
