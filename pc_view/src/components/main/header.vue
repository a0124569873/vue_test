<template>
<div>
  <div class="header" >
		<div class="left-header">
			<el-menu mode="horizontal" @select="handleSelect">
				<el-menu-item index="1">首页</el-menu-item>
				<el-menu-item index="2">详情页</el-menu-item>
			</el-menu>
		</div>
		<div class="right-header">
			<el-menu mode="horizontal" >
				<el-menu-item index="3">{{islogin ? 'aaa' : 'bbb'}}</el-menu-item>
				
				<el-menu-item index="4">主题切换</el-menu-item>
				<el-menu-item index="5">语言</el-menu-item>
			</el-menu>
		</div>
  </div>
	<!-- <router-view></router-view> -->
</div>
</template>

<script>
export default {
  props: {
		islogin: false
	},
	methods: {
		handleSelect(key,path){
      
      switch (key) {
        case "1":
        console.log(key);
          this.jumpTo("/main")
          break;
        case "2":
        console.log(key);
          this.jumpTo("/leftbar")
          break;
        case "3":
        console.log(key);
          this.jumpTo("/leftbar")
          break;
    
        default:
          break;
      }
    }
	}
}
</script>
<style>
	.header{
		display: flex;
		justify-content: space-between;
	}
</style>
