<template>
  <div class="home-view">
    <el-alert class="notice" type="success" title="公告公告" show-icon closable></el-alert>
    <el-row :gutter="20">
      <el-col :span="16">
        <el-card class="group-1">
          <el-row :gutter="30" align="middle">
            <el-col :span="6">
              <div class="user-face"></div>
              <div class="user-name">{{indexData.user_email}}</div>
            </el-col>
            <el-col :span="7" class="f-y">
              <router-link to="/user">
                <el-button size="small" type="success" plain class="fn-btn wx">绑定手机</el-button>
              </router-link>
              <a href="http://www.veda.com/Magic_Shield/" target="_blank">
                <el-button size="small" type="primary" plain class="fn-btn cp">产品交流</el-button>
              </a>
              <a href="#">
                <el-button size="small" type="warning" plain class="fn-btn ll">查看流量</el-button>
              </a>
            </el-col>
            <el-col :span="10">
              <div>
                <header>共添加域名</header>
                <div class="f-x f-js f-ac">
                  <div><span class="fs-num">{{indexData.all_domain_counts}}</span>个</div>
                  <router-link to="/site"><el-button type="primary" plain class="val-btn">查看</el-button></router-link>
                </div>
              </div>
              <hr class="hr-spl">
              <div>
                <header>余额</header>
                <div class="f-x f-js f-ac">
                  <div class="fs-num">￥{{indexData.account}}</div>
                  <el-button type="primary" class="val-btn" @click="recharge">充值</el-button>
                </div>
              </div>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="group-1">
          <h3 slot="header">紧急通知</h3>
          <div class="epmty-list f-y f-ac">
            <i class="el-icon-bell"></i>
            <p class="text-gray">暂无紧急通知</p>
          </div>
        </el-card>
      </el-col>
    </el-row>
    <el-row :gutter="20">
      <el-col :span="16">
        <el-card class="group-2">
          <h3 slot="header">接入信息</h3>
          <el-row>
            <el-col :span="12" class="part left-part">
              <h1>网站防护</h1>
              <div class="f-x f-js f-ac">
                <div>全部域名</div>
                <div>共<span class="font-color">{{indexData.joined_site_counts}}</span>个</div>
              </div>
              <div class="f-x f-je f-ac">
                <router-link to="/site"><el-button type="primary" class="to-details" size="mini">前往详情</el-button></router-link>
              </div>
            </el-col>
            <el-col :span="12" class="part right-part">
              <h1>非网站防护</h1>
              <div class="f-x f-js f-ac">
                <div>应用个数</div>
                <div>共<span class="font-color">{{indexData.joined_port_counts}}</span>个</div>
              </div>
              <div class="f-x f-je f-ac">
                <router-link to="/app"><el-button type="primary" class="to-details" size="mini">前往详情</el-button></router-link>
              </div>
            </el-col>
          </el-row>
        </el-card>
      </el-col>
      <el-col :span="8">
        <el-card class="group-2">
          <header slot="header" class="f-x f-js">
            <h3>产品公告</h3>
            <a href="" class="btn-link">更多</a>
          </header>
          <div class="epmty-list f-y f-ac">
            <i class="el-icon-info"></i>
            <p class="text-gray">暂无产品公告</p>
          </div>
          <!-- <ul class="gg-list">
            <li v-for="n in 5">
              <a href="" class="ellipsis">产品公告产品公告产品公告产品公告产品公告产品公告产品公告产品公告</a>
            </li>
          </ul> -->
        </el-card>
      </el-col>
    </el-row>
    <el-dialog :title='title' :visible.sync='visible' width='400px' center label-width="80" class="paybox">
      <el-form ref="payForm" :model="payForm" size="mini" v-show="show==1?true:false">
        <el-form-item label="充值账户">
          <span>{{indexData.user_email}}</span>
        </el-form-item>
        <el-form-item label="充值金额">
          <el-input-number v-model="payForm.money" :min="10" :step="10"></el-input-number>
          <p>最低10元起充</p>
        </el-form-item>
        <el-form-item label="付款方式">
          <el-radio-group v-model="payForm.pay_type">
            <el-radio label="支付宝"><img src="../../../assets/img/pay-zhifubao.png" style="width:60px;"></el-radio>
            <el-radio label="微信支付"><img src="../../../assets/img/pay-weixin.png" style="width:30px;">
              <span style="vertical-align:-2px">微信支付</span>
            </el-radio>
          </el-radio-group>
        </el-form-item>
        <el-row style="margin-bottom: 15px">
          <el-alert title="注意：如需网银转账，请联系在线客服" type="info" :closable="false" show-icon></el-alert>
        </el-row>
        <el-form-item style="text-align:center">
          <el-button type="primary" @click="payNow">立即支付</el-button>
        </el-form-item>
      </el-form>
      <el-row class="pay-success" v-show="show==2?true:false">
      <el-col :span='24'>
        <img src="/static/img/pay-success.png">
      </el-col>
      <el-col :span='24'>
        充值成功后请点击按钮确认，若充值中遇到问题请咨询在线客服，谢谢！
      </el-col>
      <el-col :span='24'>
        <el-button type="primary" @click="paySuccess" size="mini">充值成功</el-button>
      </el-col>
    </el-row>
    </el-dialog>
  </div>
</template>
<style lang="less">
@import '~@/less/variables.less';

.home-view {
  .el-card {
    margin-bottom: 20px;
    box-shadow: none;
    &__header {
      padding-bottom: 0;
      border-bottom: none
    }
  }
  .notice {
    width: auto;
    margin: -20px -20px 20px;
  }

  h3 {
    font-size: 14px;
  }
  .group-1 {
    height: 180px
  }
  .user-face {
    width: 90px;
    height: 90px;
    margin: 0 auto;
    padding: 8px;
    border-radius: 50%;
    border: 1px solid #ddd;
    overflow: hidden;
  }
  .user-name {
    margin-top: 10px;
    color: @text-gray-color;
    text-align: center;
    font-size: @fs-large;
    line-height: 30px;
  }

  .fn-btn {
    margin: 5px 0
  }

  .fs-num {
    font-size: 32px;
    line-height: 40px;
  }

  .hr-spl {
    border: none;
    padding: 0;
    margin: 8px 0;
    height: 1px;
    background: #ddd;
  }

  .val-btn {
    padding: 12px 30px;
  }
  .epmty-list {
    i {
      margin-bottom: 10px;
      font-size: 48px;
      color: @text-gray-lighter-color
    }
  }
  .group-2 {
    height: 260px;
    h1 {
      font-weight: normal;
      margin-bottom: 20px;
      text-align:center;
    }
  }

  .gg-list {
    margin-left: -8px;
    a {
      display: block;
      padding: 0 8px;
      line-height: 36px;
      color: @text-color;
      &:hover {
        color: @link-color;
        background: #f6f6f6
      }
      &:before {
        content: '';
        display: inline-block;
        margin-right: 8px;
        width: 10px;
        height: 10px;
        background: @link-color;
        border-radius: 50%;
      }
    }
  }
  .part {
    height: 180px;
  }
  .left-part {
    padding-right:20px;
    border-right: 1px solid #dddddd;
  }
  .right-part {
    padding-left: 20px;
  }
  .font-color {
    color: #409eff;
  }
  .to-details {
    margin-top: 20px;
  }
  .paybox {
    p {
      color:#fa5353;
      padding-left:80px;
    }
    .pay-success {
      text-align:center;
      .el-col:nth-child(2) {
        text-align: left;
        padding: 10px 0;
      }
    }
  }
}
</style>
<script>
  import siteService from 'services/siteService'
  import indexServices from 'services/indexServices'
  import userService from 'services/userService'
  export default {
    data () {
      return {
        tableData: [],
        all_domain: '',
        indexData: {},
        visible: false,
        show: false,
        payForm: {
          money: '',
          pay_type: ''
        },
        title: '充值'
      }
    },
    created () {
      this.loadData()
    },
    methods: {
      loadData () {
        siteService.list()
        .then((recvdata) => {
          if (recvdata.errcode === 0) {
            this.tableData = recvdata
            this.all_domain = recvdata.list.length
          } else {
            this.$message({
              showClose: true,
              message: this.$t('error_code.' + recvdata.errcode),
              type: 'warning',
              duration: 1000
            })
          }
        })
        indexServices.getManage()
        .then((recvdata) => {
          if (recvdata.errcode === 0) {
            this.indexData = recvdata
          }
        })
      },
      recharge () {
        this.visible = true
        this.show = 1
        this.title = '充值'
      },
      payNow () {
        this.show = 2
        this.title = '充值确认'
        userService.getOrderInfo(this.payForm.money)
        .then(recvdata => {
          if (recvdata.errcode === 0) {

          }
        })
      },
      paySuccess () {
        this.visible = false
      }
    }
  }
</script>

