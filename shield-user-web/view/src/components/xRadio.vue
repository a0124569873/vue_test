<template>
  <label class="x-radio"
         tabindex="-1">
    <input type="radio"
           :value="label"
           :name="name"
           :checked="value === label"
           tabindex="-1"
           class="x-radio-input"
           @change="onChange">
    <span class="x-radio-inner">
      <slot>{{label}}</slot>
    </span>
  </label>
</template>
<script>
export default {
  props: {
    value: [String, Number],
    label: {
      type: [String, Number],
      required: true
    },
    name: {
      type: String,
      required: true
    }
  },
  methods: {
    onChange(e) {
      this.$emit('input', this.label)
      this.$emit('change', e)
    }
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.x-radio {
    position: relative;
    display: inline-block;
    vertical-align: middle;
    word-spacing: 0;

    &-inner {
        display: block;
        line-height: 26px;
        padding: 0 10px;
        color: @text-gray-color;
        background: #eee;
        border: 1px solid transparent;
        cursor: pointer;
        transition: .2s ease-in-out;

        &:hover {
            color: @brand-color;
            border-color: @brand-color;
        }
    }

    &-input {
        position: absolute;
        top: 0;
        left: 0;
        z-index: -1;
        width: 0;
        height: 0;
        opacity: 0;
        outline: none;
        &:checked + .x-radio-inner {
            color: #fff;
            background: @brand-color;
            border-color: @brand-color;
            cursor: default;
        }
    }

    &:focus {
      outline: none
    }
}
</style>
