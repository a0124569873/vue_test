<template>
  <div class="x-detail-list">
    <div class="x-detail-row" v-for="item in data" :key="item[nodeKey]">
      <div class="x-detail-header" @click="clickHeader(item[nodeKey])">
        <el-checkbox v-if="selection" @click.native.stop=""
          :value="isChecked(item[nodeKey])"
          @change="onCheck(item[nodeKey])"></el-checkbox>
        <div class="x-detail-header-content">
          <slot name="header" :row="item"></slot>
        </div>
        <i class="el-icon-arrow-down x-detail-arrow" :class="{ active: isShow(item[nodeKey]) }"></i>
      </div>
      <el-collapse-transition>
        <div class="x-detail-content" v-show="isShow(item[nodeKey])">
          <slot :row="item"></slot>
        </div>
      </el-collapse-transition>
    </div>
    <div v-if="!data.length" class="x-detail-empty">
      暂无数据
    </div>
    <div class="x-detail-footer">
      <div>
        <el-checkbox :indeterminate="indeterminate"
          :value="isCheckAll" :checked="isCheckAll"
          class="mr-md" @change="onCheckAll"></el-checkbox>
        <el-button size="mini" type="primary" @click="remove">删除</el-button>
      </div>
      <el-pagination background
          @current-change="pageChange"
          :current-page.sync="currentPage"
          :page-size="pageSize"
          layout="total, prev, pager, next"
          :total="total"></el-pagination>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    selection: Boolean, // 是否显示复选框
    data: Array,
    nodeKey: {
      type: String,
      default: 'id'
    },
    pageSize: {
      type: Number,
      default: 5
    },
    total: Number
  },
  data() {
    return {
      collapseKeys: [],
      checkedKeys: [],
      currentPage: 1
    }
  },
  computed: {
    indeterminate() {
      const len = this.checkedKeys.length
      return len > 0 && len < this.data.length
    },
    isCheckAll() {
      const len = this.data.length
      return len > 0 ? this.checkedKeys.length === len : false
    }
  },
  methods: {
    isShow(key) {
      return this.collapseKeys.indexOf(key) < 0
    },
    clickHeader(key) {
      const isShow = this.isShow(key)
      this.collapseKeys = isShow ? this.collapseKeys.concat(key) : this.collapseKeys.filter(item => item !== key)
    },
    onCheck(key) {
      const checked = this.isChecked(key)
      this.checkedKeys = checked ? this.checkedKeys.filter(item => item !== key)
        : this.checkedKeys.concat(key)
      this.$emit('checkChange', this.checkedKeys)
    },
    isChecked(key) {
      return this.checkedKeys.indexOf(key) >= 0
    },
    onCheckAll() {
      this.checkedKeys = this.isCheckAll ? [] : this.data.map(item => item[this.nodeKey])
      this.$emit('checkChange', this.checkedKeys)
    },
    pageChange(cur) {
      this.$emit('pageChange', cur)
    },
    remove() {
      if(!this.checkedKeys.length) return
      this.$emit('remove', this.checkedKeys)
    }
  }
}
</script>
<style lang="less">
.x-detail-list {

}
.x-detail-row {
  margin-bottom: 10px;
}
.x-detail-header {
  display: flex;
  align-items: center;
  height: 50px;
  padding: 0 20px;
  background: #efefef;
  cursor: pointer;
}
.x-detail-header-content {
  flex: 1 1 auto;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 40px 0 15px;
}
.x-detail-content {
  padding: 20px;
  background: #f9f9f9;
  .tit {
    font-weight: 600;
    margin-bottom: 10px
  }

}
.x-detail-item {
  display: flex;
  align-items: center;
  font-size: 12px;
  line-height: 24px;
  label {
    color: #888
  }
}
.x-detail-footer {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
  padding: 20px 20px 10px;
  border-top: 1px solid #ededed;
}
.x-detail-arrow {
  float: right;
  color: #666;
  transition: transform .3s;
  &.active {
    transform: rotate(180deg)
  }
}
.x-detail-empty {
  height: 100px;
  line-height: 100px;
  text-align: center;
}
</style>
