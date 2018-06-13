<!--
  <vd-dialog
    :title="'提示'"
    :width="'500px'"
    @onCancel="onCancel"
    @onConfirm="onConfirm"
    :visible.sync="showDialog"
    :cancelText="'取消'"
    :confirmDisabled="true"
  >
    <span>内容</span>
  </vd-dialog>
-->
<template>
  <el-dialog
    class="vd-dialog"
    :title="title"
    :width="width"
    :visible="visible"
    @close="onCancel"
    center>
    <slot></slot>
    <span slot="footer" class="dialog-footer">
      <el-button v-if="cancelText" type="" size="mini" @click="onCancel">{{cancelText}}</el-button>
      <el-button v-if="confirmText" type="primary" size="mini" :disabled="confirmDisabled" :loading="confirmLoading" @click="onConfirm">{{confirmText}}</el-button>
    </span>
  </el-dialog>
</template>

<style lang="less">
@import '~@/less/variables.less';
.vd-dialog {
  .el-dialog {
    max-width: 50%;
    border-radius: 8px;
  }
  .el-dialog__header {
    padding: 10px 15px;
    text-align: left;
    background-color: #efefef;
    border-radius: 8px;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    .el-dialog__title {
      line-height: 1;
      color: #666666;
      font-size: 14px;
    }
  }
  .el-dialog__footer {
    padding: 10px 15px;
    border-top: 1px solid #eeeeee;
  }
  .el-dialog__headerbtn {
    top: -10px;
    right: -15px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: @brand-color;
    border: 2px solid #ffffff;
    transform: scale(.8);
    .el-dialog__close {
      color: #ffffff;
    }
  }
}
</style>

<script>
export default {
  name: 'vdDialog',
  props: {
    title: {
      type: String,
      default: '标题'
    },
    width: {
      type: String,
      default: '500px'
    },
    visible: Boolean,
    cancelText: {
      type: String,
      default: '取消'
    },
    confirmText: {
      type: String,
      default: '确定'
    },
    confirmDisabled: Boolean,
    confirmLoading: Boolean
  },
  methods: {
    onCancel() {
      this.$emit('onCancel')
    },
    onConfirm() {
      this.$emit('onConfirm')
    }
  }
}
</script>
