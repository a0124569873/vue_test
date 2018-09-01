<template>
    <div class="footerbar">i am footer</div>
</template>

<script>
    export default {
        mounted(){
            console.log("footer ")
        }
    }
</script>

<style scoped>
.footerbar{
    background-color: bisque;
    text-align: center;
    bottom: 0;
    width: 100%;
    position: fixed;
}
</style>