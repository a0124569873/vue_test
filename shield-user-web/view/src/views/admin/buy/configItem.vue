<template>
  <div class="ip-config-item f-x">
    <label class="ip-config-lbl f-0">{{label}}</label>
    <div class="ip-config-control f-1">
      <slot></slot>
      <div class="ip-config-help" v-if="$slots.desc">
        <slot name="desc"></slot>
      </div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    label: String,
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.ip-config- {
  &item {
    padding: 10px 0;
  }
  &lbl {
    width: 120px;
    line-height: 30px;
    color: @text-gray-color
  }
  &help {
    padding-top: 10px;
    font-size: 12px;
    color: @text-gray-color;
    span {
      color: #f71410;
    }
    a {
      color: @text-color;
      &:hover {
        text-decoration: underline
      }
    }
  }
}
</style>

