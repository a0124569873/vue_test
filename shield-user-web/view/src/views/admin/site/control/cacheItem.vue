<template>
  <div class="cache-item" :class="{ active: isActive }">
    <div class="left col" @click="toggle">
      <i class="el-icon-circle-check icon"></i>
      <span class="label">{{label}}</span>
    </div>
    <div class="right col">
      <input type="text"
             v-model.trim.number="number" @change="changeTime">
      <div class="select-wrap">{{options[unit]}}
        <i class="el-icon-caret-bottom ml-sm"></i>
        <select v-model="unit" tabindex="-1">
          <option v-for="(item, i) in options"
                  :value="i"
                  :key="i">{{item}}</option>
        </select>
      </div>
    </div>
  </div>
</template>
<script>
const checkNumber = value => !isNaN(value) && Number(value) >= 0

export default {
  props: {
    label: String,
    value: String
  },
  data() {
    return {
      options: ['天', '小时', '分钟'],
      unitMap: ['day', 'hour', 'minute'],
    }
  },
  computed: {
    splitVal() {
      return this.value ? this.value.split(':') : []
    },
    number: {
      get() {
        const val = Number(this.splitVal[0])
        return val === 0 ? '' : val
      },
      set(val) {
        if(!checkNumber(val)) return
        this.$emit('input', `${val}:${this.unitMap[this.unit]}`)
      }
    },
    unit: {
      get() {
        const val = this.splitVal[1]
        return val ? this.unitMap.indexOf(val) : 1
      },
      set(val) {
        if(!checkNumber(val)) return
        this.$emit('input', `${this.number}:${this.unitMap[val]}`)
      }
    },
    isActive() {
      return Number(this.number) !== 0
    }
  },
  methods: {
    toggle() {
      const value = this.isActive ? '0:minute' : '1:hour'
      this.$emit('input', value)
    },
    changeTime() {
      const value = this.isActive ? `${this.number}:${this.unitMap[this.unit]}` : '0:minute'
      this.$emit('input', value)
    }
  }
}
</script>
<style lang="less" scoped>
@import '~@/less/variables.less';

.cache-item {
    margin-bottom: 15px;
    display: flex;
    height: 40px;
    border: 1px solid #ddd;
    white-space: nowrap;
    &.active {
      .left {
        color: #fff;
        background: @brand-color;
      }
      .icon {
        color: #fff
      }
    }
}
.col {
    width: 50%;
}
.left {
    text-align: center;
    line-height: 40px;
    font-size: 14px;
    border-right: 1px solid #ddd;
    cursor: pointer;
}
.icon {
    color: #bbb;
    font-size: 18px;
    margin-right: 3px;
    vertical-align: text-top
}

.right {
    padding: 0 10px;
    display: flex;
    align-items: center;
}
input {
    border: none;
    width: 40px;
    height: 24px;
    padding: 0 4px;
    text-align: center;
    background: #eee;
}
.select-wrap {
    position: relative;
    width: 40px;
    margin-left: 10px;
    cursor: pointer;
}
select {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    &[disabled] {
      cursor: default
    }
}
</style>
