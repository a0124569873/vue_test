<template>
  <div class="wrap">
      <headerbar
      :zzxn="zxn"
      ></headerbar>
      <router-view></router-view>
      <!-- <systeminfo/> -->
      <footer11/>
  </div>
</template>
<script>
import headerbar from '@/components/header'
import systeminfo from '@/components/zxn'
import footer11 from '@/components/foot'

export default {
    name:'mainn_components',
    components:{
        headerbar,
        // systeminfo,
        footer11
    },
    mounted(){
      this.zxn = "fsdfdsf" 
     if (!(window.sessionStorage.getItem('login') === 'true')) {
          this.$router.push("/login")
        }
    },
    data(){
        return {
            zxn:"props_zxn"
        }
    }
  
}
</script>
