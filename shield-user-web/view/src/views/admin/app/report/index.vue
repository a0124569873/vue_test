<template>
  <subView view="报表统计" :title="domain" :routes="menus" backUrl="/app"></subView>
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
          name: 'appBanWidth'
        },
        text: 'IN/OUT带宽趋势'
      }, {
        to: {
          name: 'appFlow'
        },
        text: '总流量/攻击流量趋势'
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
