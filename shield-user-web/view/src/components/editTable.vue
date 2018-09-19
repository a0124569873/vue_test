<template>
  <div class="g-edit-table">
    <div class="head">
      <span class="ckb">
        <el-checkbox :indeterminate="indeterminate" v-model="isCheckAll"></el-checkbox>
      </span>
      <span class="col" v-for="item in columns">{{item.label}}</span>
      <span class="tools">
        <a v-show="selected.length > 0" @click="onDelete" class="red">删除</a>
      </span>
    </div>
    <el-checkbox-group v-model="selected" @change="onChange">
      <ul class="body">
        <li class="row edit-row" v-show="edited">
          <span class="ckb"></span>
          <span v-for="item in columns" class="col">
            <input type="text" v-model.trim="form[item.prop || 'default']" required :placeholder="`输入${item.label}`" @keyup.enter="save">
          </span>
          <span class="tools">
            <a class="el-icon-circle-close act off" title="取消" @click="cancel"></a>
            <a class="el-icon-circle-check act btn-link" title="保存" @click="save"></a>
          </span>
        </li>
        <li v-for="(row, i) in data" class="row">
          <span class="ckb">
            <el-checkbox :label="i"></el-checkbox>
          </span>
          <span v-for="item in columns" class="col">{{item.prop ? row[item.prop] : row}}</span>
          <span class="tools"></span>
        </li>
      </ul>
    </el-checkbox-group>
  </div>
</template>
<script>
export const editTableCol = {
  props: {
    label: String,
    prop: String,
    required: Boolean
  }
}

export default {
  props: {
    data: {
      type: Array,
      default() {
        return []
      }
    },
    edited: Boolean
  },
  data() {
    return {
      form: {},
      selected: [],
      indeterminate: false,
      columns: []
    }
  },
  computed: {
    isCheckAll: {
      get() {
        const len = this.data.length
        return len === 0 ? false : this.selected.length === this.data.length
      },
      set(checked) {
        this.selected = checked ? this.data.map((e, i) => i) : []
        this.indeterminate = false
      }
    }
  },
  methods: {
    onDelete() {
      const selected = this.selected
      if(!selected.length) return
      this.$confirm('确定要删除吗？', '提示', {
        type: 'warning'
      }).then(() => {
        const data = this.data.filter((e, i) => selected.indexOf(i) < 0)
        this.$emit('change', null, data, () => {
          this.selected = []
        })
      }).catch()
    },
    cancel() {
      this.form = {}
      this.$emit('update:edited', false)
    },
    save() {
      if(this.columns.some(col => col.required && !this.form[col.prop || 'default'])) return
      const newValue = this.form.default || { ...this.form }
      const data = new Set(this.data.concat(newValue))
      if(this.data.length === data.size) {
        this.$message.warning('内容已存在！')
        return
      }
      this.$emit('change', newValue, [...data], () => this.cancel())
    },
    onChange(value) {
      const count = value.length
      this.indeterminate = count > 0 && count < this.data.length
    }
  },
  created() {
    this.columns = this.$slots.default.reduce((arr, slot) => {
      const options = slot.componentOptions
      return options ? arr.concat(options.propsData) : arr
    }, [])
  }
}
</script>
<style lang="less">
@import '~@/less/variables.less';

.g-edit-table {
  width: 100%;
  border: 1px solid #ddd;
  .head {
    height: 42px;
    background: #f6f6f6;
    border-bottom: 1px solid #ddd;
  }
  .head,
  .row {
    display: flex;
    align-items: center;

  }
  .row {
    border-bottom: 1px dashed #ddd;
    &:hover {
      background: #eaf4ff
    }
  }
  .edit-row {
    background: #eaf4ff;
    input {
      width: 100%;
      max-width: 200px;
      height: 26px;
      padding: 0 6px;
      border: 1px solid #eee;
      &:focus {
        border-color: @brand-color
      }
    }
  }
  .body {
    height: 220px;
    font-size: 14px;
    overflow-y: auto;
  }
  .col {
    flex: 1 1 auto;
    padding: 6px 10px;
    color: #666;
  }
  .ckb {
    width: 40px;
    text-align: center;
  }
  .tools {
    width: 60px;
    text-align: right;
    white-space: nowrap;
    padding: 6px 10px 6px 0;
    .act {
      display: inline-block;
      margin: 0 2px;
      font-size: 18px;
      transition: .2s ease-in-out;
      &:hover {
        transform: scale(1.1);
      }
    }
    .off {
      color: #bbb
    }
  }
  .el-checkbox__label {
    display: none
  }
}
</style>

