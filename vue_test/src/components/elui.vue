<template>
  <div>
    <link rel="stylesheet/less" type="text/css" href="../theme/elui.less">
      <div>
  <el-button>默认按钮</el-button>
  <el-button type="primary" class="testbtn">主要按钮</el-button>
  <el-button type="success">成功按钮</el-button>
  <el-button type="info">信息按钮</el-button>
  <el-button type="warning">警告按钮</el-button>
  <el-button type="danger">危险按钮</el-button>
</div>

<div style="margin: 20px 0">
  <el-button plain>朴素按钮</el-button>
  <el-button type="primary" plain>主要按钮</el-button>
  <el-button type="success" plain>成功按钮</el-button>
  <el-button type="info" plain>信息按钮</el-button>
  <el-button type="warning" plain>警告按钮</el-button>
  <el-button type="danger" plain>危险按钮</el-button>
</div>

<div>
  <el-button round>圆形按钮</el-button>
  <el-button type="primary" round>主要按钮</el-button>
  <el-button type="success" round>成功按钮</el-button>
  <el-button type="info" round>信息按钮</el-button>
  <el-button type="warning" round>警告按钮</el-button>
  <el-button type="danger" round>危险按钮</el-button>
</div>

<el-button type="primary" icon="el-icon-edit"></el-button>
<el-button type="primary" icon="el-icon-share"></el-button>
<el-button type="primary" icon="el-icon-delete"></el-button>
<el-button type="primary" icon="el-icon-search">搜索</el-button>
<el-button type="primary">上传<i class="el-icon-upload el-icon--right"></i></el-button>

<el-button type="primary" :loading="fff" v-on:click="dddd()" >加载中</el-button>

<!-- <input type="button" name="aaaa" v-on:click="dddd()" v-bind:value="bt_val" /> -->


<el-carousel :interval="5000" arrow="always">
    <el-carousel-item v-for="item in 4" :key="item">
      <h3>{{ item }}</h3>
    </el-carousel-item>
</el-carousel>

  <el-button :plain="true" @click="open2">成功</el-button>
  <el-button :plain="true" @click="open3">警告</el-button>
  <el-button :plain="true" @click="open">消息</el-button>
  <el-button :plain="true" @click="open4">错误</el-button>

<el-button type="text" @click="open10">点击打开 Message Box</el-button>

  <div>
    <elTable/>
  </div>

  </div>

</template>
<script>

import elTable from '@/components/el-table'

export default {
    components:{
      elTable
    },
  data:{
			fff:false
			// bt_val:"fdsfdsfdsf"
		},

		methods: {

			open10: function(){
				this.$alert('这是一段efrfdfds内容', '标题dsfdsf名称', {
		          confirmButtonText: '确定',
		          callback: action => {
		            this.$message({
		              type: 'info',
		              message: `action: ${ action }`
		            });
		          }
		        });
		  		// this.$alert('aaa','bbb',);
			},

	    dddd: function () {
	      // this.fff = this.fff?false:true;
	      if(this.fff){
	      		this.fff = false;
	      }else{
	      	this.fff = true;
	      }
    	},
    	open: function() {
	        this.$message('这是一条消息提示');
	      },
      open2: function() {
        this.$message({
          message: '恭喜你，这是一条成功消息',
          type: 'success'
        });
      },

      open3: function() {
        this.$message({
          message: '警告哦，这是一条警告消息',
          type: 'warning'
        });
      },

      open4: function() {
        this.$message.error('错了哦，这是一条错误消息');
      }
	 }
}
</script>

