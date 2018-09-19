<template>
  <div id="app" :class="theme">
    <vue-particles color="#dedede"></vue-particles>
    <router-view/>
  </div>
</template>

<script>
export default {
  name: 'App'
}
</script>

<style>
</style>
