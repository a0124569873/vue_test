<template>
    <div class="wrap">
        <div>
            <img src="../img/error.png">
            <div>
                <p>Sorry,您访问的页面未找到.....</p>
                <p>点击<a @click='jumpTo(0)'>返回上级</a>或<a @click='jumpTo(1)'> 首 页 </a></p>
            </div>
        </div>     
    </div>
</template>
<script>
    export default {
      mounted () {
        $('.wrap').css('height', $(window).height())
      },
      methods: {
        jumpTo (index) {
          if (index === '1') {
            this.$router.push('/')
          } else {
            window.history.back()
          }
        }
      }
    }
</script>
<style scoped>
    .wrap{
        display: flex;
        justify-content: center;
        background: #ffffff;
    }
    .wrap div{
        margin-top: 80px;
    }
    img{
        width:300px;
    }
    p{
        line-height:30px;
        font-size :14px;
    }
    a{
        color: #20A0FF;
    }
</style>
