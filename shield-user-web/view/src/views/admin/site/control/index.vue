<template>
  <subView view="控制面板" :title="domain" :routes="menus" backUrl="/site"></subView>
</template>
<script>
import subView from 'components/subView'

export default {
  name: 'siteControl',
  components: {
    subView
  },
  data() {
    return {
      menus: [{
        to: {
          name: 'siteBwlist'
        },
        text: '黑白名单'
      }, {
        to: {
          name: 'siteSpeed'
        },
        text: '缓存加速'
      }, {
        to: {
          name: 'siteHttps'
        },
        text: 'HTTPS证书'
      }]
    }
  },
  computed: {
    domain() {
      return this.$route.params.id
    }
  }
}
</script>
