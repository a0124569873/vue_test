<template>
  <div class="wrap">
      <headerbar
      :zzxn="zxn"
      ></headerbar>
      <!-- <div style="display: flex;"> -->
      <router-view></router-view>
      <!-- </div> -->
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
    },
    data(){
        return {
            zxn:"props_zxn"
        }
    }
  
}
</script>
