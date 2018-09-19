/* eslint-disable
 * @Author: zxn
 * @Date: 2018-05-07 15:13:49
 * @Last Modified by: zxn
 * @Last Modified time: 2018-05-07 16:45:22
 */

<template>
  <div class="main">
    <headerbar :islogin="login"></headerbar>
    <router-view></router-view>
		<footerbar></footerbar>
  </div>
</template>
<script>

// import headerbar from '@components/main/header'
// import footerbar from '@components/main/footer'
import headerbar from './header'
import footerbar from './footer'

export default {
  components:{
		headerbar,
		footerbar
	}
}
</script>

