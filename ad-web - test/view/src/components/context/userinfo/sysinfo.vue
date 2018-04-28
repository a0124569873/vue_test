<template>
    <div class="content">
        <div class="base">
          <h4>证书信息</h4>
          <ul>
            <li>
              <p>证书ID：{{cert.id}}</p>
              <p>证书状态：{{cert.status}}</p>
              <p>证书备注：{{cert.desc}}</p>
              <p>证书创建时间: {{cert.create_time}}</p>
            </li>
            <li>
              <!-- <p>授权类型: {{cert.type}}</p> -->
              <p>授权语言: {{cert.lang}}</p>
              <p>授权厂家: {{cert.licence_owner}}</p>   
              <p>授权日期: {{cert.start_time}}</p>
              <p>授权有效期:{{cert.end_time}}</p>
            </li>
            <li>
              <p>设备识别号: {{cert.device_id}}</p>
              <p>设备型号:{{cert.model}}</p>     
              <p>版权所有: {{cert.copy_right}}</p>
              <p>系统版本: {{cert.version}}</p>
            </li>
          </ul>        
        </div>
        <div v-if="$checkPermisstion('上传证书')" class="update">
          <h4>证书上传</h4>
          <ul>
            <li>
              <el-upload  ref="upload" drag  :limit="1"  :file-list="fileList" action="license/upload"
              :before-upload="beforeUpload" :auto-upload="false" :on-success="uploadSuccess" :on-error="uploadError">
                <div  slot="trigger">
                  <i class="el-icon-upload"></i>
                  <div class="el-upload__text">将文件拖到此处，或<em>点击选择文件</em></div>
                </div> 
                <el-button class="btn" size="mini" type="primary" @click="submitUpload">上传证书</el-button>
              </el-upload>         
            </li>   
          </ul>
          
        </div>
    </div>
</template>
<script>
import indexService from 'services/indexService'

export default {
  data () {
    return {
      cert: {
        'status': '',
        'id': '',
        'desc': '',
        'lang': '',
        'licence_owner': '',
        'copy_right': '',
        'type': '',
        'device_id': '',
        'user': '',
        'model': '',
        'create_time': '',
        'start_time': '',
        'end_time': ''
      },
      fileList: []
    }
  },
  created () {
    this.getCert()
  },
  methods: {
    getCert () {
      indexService.getCert()
        .then((res) => {
          if (res.errcode === 0) {       
            res.cert.create_time = new Date(res.cert.create_time * 1000).toLocaleString()
            res.cert.start_time = new Date(res.cert.start_time * 1000).toLocaleString()
            res.cert.end_time = new Date(res.cert.end_time * 1000).toLocaleString()
            res.cert.lang = res.cert.lang === 'chinese' ? '简体中文' : 'English'
            res.cert.status = this.statusType(res.cert.status)
            this.cert = res.cert      
          }
        })
    },
    statusType (status) {
      let type = ''
      switch (status) {
      case 'valid':
        type = '合格'
        break
      case 'expired':
        type = '系统授权文件过期'
        break
      case 'missing':
        type = '系统授权文件缺失'
        break
      case 'device_not_match':
        type = '系统授权文件和设备不匹配'
        break
      case 'malform':
        type = '系统授权文件内容格式错误'
        break
      case 'type_error':
        type = '系统授权文件类型错误'
        break
      case 'unknown':
        type = '系统授权文件状态未知'
        break
      case 'uninit':
        type = '系统未授权'
        break
      default:
        break
      }
      return type
    },
    beforeUpload (file) {
      const islic = file.name.indexOf('.lic') > 0
      if (!islic) {
        this.$message.error('上传证书只能是 lic 格式!')
      }
      return islic
    },
    submitUpload () {
      this.$refs.upload.submit()
    },
    uploadSuccess (response, file, fileList) {
      if (response.errcode === 0) {
        this.$message({
          showClose: true,
          message: '证书上传成功,即将刷新页面',
          type: 'success'
          // duration: 2000
        })
        setTimeout(() => { location.reload() }, 2000)
      } else {
        this.fileList = []
        this.$message({
          showClose: true,
          message: this.$t('error_code.' + response.errcode),
          type: 'error',
          duration: 2000
        })
      }
    },
    uploadError (err, file, fileList) {
      let errCode = JSON.parse(err.message.split(' ')[1]).errcode
      this.$message({
        showClose: true,
        message: '上传失败，' + this.$t('error_code.' + errCode),
        type: 'error',
        duration: 2000    
      })
    }
  }
}
</script>
<style lang="scss" scoped>
.base,.update{
  display: flex;
  justify-content: space-around;

  ul{
    display: flex;
    justify-content: space-around;
    width: 90%;
  }
  li{ 
    text-align: left;
    p{
      height: 25px;
    }
  }
}
.base li{
  width: 33%;
}
.update{
  margin: 20px 0 50px;
  padding-top: 30px;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  ul{
    display: flex;
    justify-content: flex-start;
    li{
      width:300px;
    }
  }
}
.btn{
  margin-top: 10px;
}
</style>



