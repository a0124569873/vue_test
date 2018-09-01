<template>
<div>
  <secondBar title="支付结果"></secondBar>

  <div class="pay-result-body">
    <div class="pay-result-body-box">
      <div class="pay-result-msg">
        <i></i>
        <div class="content">
          <p class="content-t">支付成功！</p>
          <p>如有疑问，请联系北京卫达信息技术有限公司</p>
          <router-link to="/" class="btn">
            <el-button type="primary">首页</el-button>
          </router-link>
          <router-link to="/order" class="btn">
            <el-button type="primary">查看订单</el-button>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script>
import secondBar from 'components/secondBar'
export default {
  components: {
    secondBar
  }
}
</script>

<style scoped>
.pay-result-body{padding:80px 0;}
.pay-result-body-box{position:relative;margin: 0 auto;padding:40px 0;width:1000px;background: #fff;}
.pay-result-msg{margin:100px auto;display:table;}
.pay-result-msg i{float:left;display:block;width:95px;height:95px;margin-right:20px;background:url(../../../assets/img/correct.png);background-size:contain;}
.content{font-size:14px;padding:5px 0;color:#b8c4ce;margin-left:115px;}
.content p{word-wrap:break-word;word-break:break-all;}
.content-t{font-size:20px;color:#5d6d7e;}
.btn{display:inline-block;margin-top:10px;}
.btn button{padding: 7px 15px;}
</style>
