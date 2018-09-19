<template>
    <div class="layout">

        <el-container>
            <el-header>
                <headerbar></headerbar>
            </el-header>
            <router-view>
                
            </router-view>
            <el-footer>
                <footbar></footbar>
            </el-footer>
        </el-container>
    </div>
</template>

<script>
import headerbar from './header'
import footbar from './footer'
import leftbar from './leftbar'
import mainpage from '../mainPage'
    export default {
        components: {
            headerbar,
            footbar
        }
        // mounted(){
        //     this.$router.push("mainPage")
        // }

    }
</script>

<style scoped>

</style>