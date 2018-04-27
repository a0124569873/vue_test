<template>
<div>
      <i className="el-icon-edit"></i>
      <i className="el-icon-share"></i>
      <i className="el-icon-delete"></i>
      <Button type="primary" icon="search">搜索</Button>
    </div>
</template>
<script>
export default {
	data(){
		return {

		}
	},
	mounted(){
		console.log("----------------------------")
		function cook(){
			console.log('cook')
			var p = new Promise(function(resolve, reject) {
				setTimeout(() => {
					console.log('cooked')
					// resolve('apple')
					reject('reject!')
				}, 1000);
			})
			return p
		}

		function eat(data) {
			console.log("eat " + data)
			var p = new Promise(function (resolve, reject) {
				setTimeout(() => {
					console.log('eated')
					resolve('clean')
				}, 2000);
			})

			return p


		}

		cook()
		.then(eat,(data)=>{
			console.log(data)
			return new Promise(function (resolve, reject) {
				resolve('clean')
			})
		})
		.then(function (data) {
			console.log("begin " + data)
			console.log('cleaned')
		})

		
	}
}
</script>
