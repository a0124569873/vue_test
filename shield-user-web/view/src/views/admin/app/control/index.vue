<template>
  <subView view="控制面板" :title="domain" :routes="menus" backUrl="/app"></subView>
</template>
<script>
import subView from 'components/subView'

export default {
  name: 'appControl',
  components: {
    subView
  },
  data() {
    return {
      menus: [{
        to: {
          name: 'appConf'
        },
        text: '基本配置'
      }, {
        to: {
          name: 'appBwlist'
        },
        text: '黑白名单'
      }, {
        to: {
          name: 'appCname'
        },
        text: 'CNAME自动调度'
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
