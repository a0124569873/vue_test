<template>
  <subView view="报表统计" :title="domain" :routes="menus" backUrl="/high"></subView>
</template>
<script>
import subView from 'components/subView'

export default {
  name: 'highControl',
  components: {
    subView
  },
  data() {
    return {
      menus: [{
        to: {
          name: 'highBanWidth'
        },
        text: 'IN/OUT带宽趋势'
      }, {
        to: {
          name: 'highFlow'
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
