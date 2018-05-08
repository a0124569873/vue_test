<template>
  <div id="zxn">
      {{msg}}
      <el-button @click="toZxn('/')">home</el-button>



      <!-- <div id="main1" style="width:500px;height:500px">
        </div> -->
      
      <p v-for="item in list">
        {{item.aaa}}
        <template slot-scope="sss">
            {{sss.row.aaa}}
        </template>
        
      </p>

      <div class="fff">
        <div class="ff1"> rwe2342341232131231231232131231231sdfsdfsdfsdfsdfsdfsdfsdfsdfsdfsdfrwerwe</div>
        <div class="ff2">werwerretertre512fdg5fd7gre12fd1v5df4g5r1et2f1dg5fe45t1ret4re51tewr</div>
        <div class="ff3">werwersdfsdfsdfsdfsdfsdfcxvcxvetertdffdvfdgertersdvsfwer</div>
        <div class="ff4">ewrwerwerwesdfsdfsdfrfrerrwetetyrthfgnfnncbncghfghrtgdfbvbdfgdfgdgertertrew</div>
        <div class="ff5">wrwerwerwerwerwerwerwdfgretretredfvdfgdfg4354g53fd8g74g5f4g5re45terwerwer</div>
      </div>
      <div class="ff1"></div>
      <div class="fff1">
        <div class="ff1"> </div>
        <div class="ff2"></div>
        <div class="ff3"></div>
        <div class="ff4"></div>
        <div class="ff5"></div>
      </div>
      <div class="ff1"></div>

  </div>

</template>
<script>

/** 引用模块 **/
// import mm from '../test_js/es6_js';
var mm = require('../test_js/common_js');

// var mm;

// require(['../test_js/b'],function (mm) {
//   alert(mm.basicNum)
//   alert(mm.add(99 , mm.basicNum));
// })

// alert(mm)

// alert(mm.basicNum)

// alert(mm.add(99 , mm.basicNum));



let myMixin = {
  created:function(){
    this.hello()
  },
  data(){
    return {
      aaa:'fdsfdsf'
    }
  },
  methods:{
    hello:function(){
      console.log("dsfdsfdsf")
    }
  }
}



export default {

  mixins:[myMixin],
  name:"zxn_components",
  data(){
    return{
        msg:"jump_test!",
        list:[
            {aaa:111,bbb:222},
            {aaa:333,bbb:444}
          ]
    }
  },
  methods:{
    toZxn(pathh){
      this.$router.push(pathh);
    }
  },
  created:function(){
    console.log(this.$data)
  },
  mounted:function(){
    this.hello()
    // this.test_mixin()
  }
//   mounted(){
//     var vvv = $("#main1")
//     var myChart = echarts.init(vvv);
//     // 指定图表的配置项和数据
//     var option = {
//         xAxis: {
//             type: 'category',
//             data: ["11","22","33","44","55","66","77"]
//         },
//         yAxis: {
//             type: 'value'
//         },
//         series: [{
//             data: [820, 932, 901, 934, 1290, 1330, 1320],
//             type: 'line',
//             smooth: true
//         }]
//     };

//     myChart.setOption(option);

//   }

    // date:{
    //     msg:"dsfdsfdsf"
    // }
}
</script>

<style rel="stylesheet/less" lang="less">
  .fff{
    width: 100%;
    height: 300px;
    background-color: red;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    flex-flow: col nowrap;
    .ff1{
      // width: 50px;
      height: 50px;
      background-color: yellow;
    }
    .ff2{
      // width: 50px;
      height: 50px;
      background-color: rgb(19, 233, 108);
    }
    .ff3{
      // width: 50px;
      height: 50px;
      background-color: rgb(20, 22, 4);
    }
    .ff4{
      // width: 50px;
      height: 50px;
      background-color: rgb(19, 33, 233);
    }
    .ff5{
      // width: 50px;
      height: 50px;
      background-color: rgb(155, 19, 233);
    }
  }
    .fff1{
    width: 100%;
    height: 300px;
    background-color: red;
    display: flex;
    // flex-direction: row;
    // flex-wrap: nowrap;
    // flex-flow: row nowrap;
    justify-content: space-evenly;
    // align-items: center;//baseline;flex-end;//center;
    // align-content: space-between;
    // order: 1;
    // flex-grow: 5;
    align-self: center;
    flex-basis: 100em;
    .ff1{
      width: 50px;
      height: 50px;
      background-color: yellow;
    }
    .ff2{
      width: 50px;
      height: 50px;
      background-color: rgb(19, 233, 108);
    }
    .ff3{
      width: 50px;
      height: 50px;
      background-color: rgb(20, 22, 4);
    }
    .ff4{
      width: 50px;
      height: 50px;
      background-color: rgb(19, 33, 233);
    }
    .ff5{
      width: 50px;
      height: 50px;
      background-color: rgb(155, 19, 233);
    }
  }
  .ff1 {
    width: 50px;
      height: 50px;
      background-color: rgb(0, 225, 255);
  }
</style>


