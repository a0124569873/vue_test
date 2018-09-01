<template>
  <subView view="报表统计" :title="domain" :routes="menus" backUrl="/site"></subView>
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
          name: 'siteOverview'
        },
        text: '网站访问概况'
      }, {
        to: {
          name: 'siteBanWidth'
        },
        text: 'IN/OUT带宽趋势'
      }, {
        to: {
          name: 'siteFlow'
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
