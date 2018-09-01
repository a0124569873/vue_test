import Vue from 'vue'

Vue.component('G2AreaBase', resolve => { require(['@/components/charts/G2/components/Area/base.vue'], resolve) })

Vue.component('G2BarBase', resolve => { require(['@/components/charts/G2/components/Bar/base.vue'], resolve) })

Vue.component('G2ColumnBase', resolve => { require(['@/components/charts/G2/components/Column/base.vue'], resolve) })

Vue.component('G2LineBase', resolve => { require(['@/components/charts/G2/components/Line/base.vue'], resolve) })
Vue.component('G2LineStep', resolve => { require(['@/components/charts/G2/components/Line/step.vue'], resolve) })

Vue.component('G2NightingaleRoseBase', resolve => { require(['@/components/charts/G2/components/NightingaleRose/base.vue'], resolve) })

Vue.component('G2PieBase', resolve => { require(['@/components/charts/G2/components/Pie/base.vue'], resolve) })

Vue.component('G2RadarBase', resolve => { require(['@/components/charts/G2/components/Radar/base.vue'], resolve) })
