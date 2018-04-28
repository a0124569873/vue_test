<template>  
  <div class="content">          
    <div class="content-inner">
      <div class="formtitle">
        <el-button type='primary' size='mini' icon='el-icon-circle-plus-outline' @click="visibleDialog = true">添 加</el-button>
        <el-button type='primary' size='mini' icon='el-icon-delete' @click='del'>删 除</el-button>
      </div>
      <el-table border :data='tableData' @select='selectItem'>
        <el-table-column type='selection' style="width:50px;" align='center'></el-table-column>
        <el-table-column type="index" width="70" label='序号' align='center'></el-table-column>
        <el-table-column label='角色' prop='group_id' align='center'>
          <template slot-scope="scope">
            {{ scope.row.group_id === 1 ? '管理员' : '用户'}}
          </template>
        </el-table-column>
        <el-table-column label="用户名" prop="username" align='center'></el-table-column>
      </el-table>
        
      <el-dialog title="添加配置" :visible.sync='visibleDialog' @close='reset' width="400px">      
        <el-row>                 
          <el-alert  title="用户名格式为数字、字母、下划线或汉字组成。" type="info" :closable="false" show-icon></el-alert>                 
        </el-row>
        <el-row>               
          <el-alert  title="密码格式为8-16位字母和数字组成。" type="info" :closable="false" show-icon></el-alert>
        </el-row>
        <el-form :model="formData" status-icon label-position="right" label-width="55px" :rules="rules" ref="myForm">
          <el-form-item label="用户名" prop="username">
              <el-input v-model="formData.username"></el-input>
          </el-form-item>
          <el-form-item label="密码" prop="password">
              <el-input v-model="formData.password"></el-input>
          </el-form-item>
          <div style="text-align:right;margin-right:3px;">
            <el-button type='primary' @click="submitForm('myForm')" :loading="addBtn.status">{{addBtn.text}}</el-button>
            <el-button @click="visibleDialog=false">取 消</el-button>
          </div>
        </el-form>
      </el-dialog>
    </div>
  </div>
</template>
<script>
import ControlService from 'services/controlService'
export default {
  data () {
    const checkPwd = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('密码不能为空'))
      }
      let reg = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('密码格式不正确'))
      } else {
        callback()
      }
    }	
    const checkName = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('用户名不能为空'))
      }
      let reg = /^\w+$/
      let status = reg.test(value)
      if (!status) {
        callback(new Error('用户名格式不正确'))
      } else {
        callback()
      }
    }
    return {
      tableData: [],
      addBtn: {
        status: false,
        text: '确 定'
      },
      visibleDialog: false,
      selectArr: [],
      formData: {
        username: '',
        password: ''
      },
      rules: {
        username: [{ validator: checkName, trigger: 'blur' }],
        password: [{ validator: checkPwd, trigger: 'blur' }]
      }
    }
  },
  created () {
    this._loadData()
  },
  methods: {
    _loadData () {
      // ControlService.getUsers()
      //   .then((res) => {
      //     if (res.errcode == 0) {
      //       this.tableData = res.users          
      //     }
      //   })
    },
    del () {
      if (!this.selectArr.length) {
        this.$message({
          message: '请选择要删除的用户',
          type: 'warning',
          duration: '2000'
        })
        return
      }
      if (this.tableData.length - this.selectArr.length < 1) {
        this.$message({
          message: '至少保留一个用户',
          type: 'warning',
          duration: '2000'
        })
        return
      }
      this.$confirm('确定删除该用户?', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }).then(() => {
        let idStr = this.selectArr.map(item => {
          return item.u_id
        }).join(',')
        // ControlService.delUsers(idStr)
        //   .then((res) => {
        //     if (res.errcode === 0) {
        //       this.$message({
        //         message: '删除成功！',
        //         type: 'success',
        //         duration: '1500'
        //       })
        //       this._loadData()
        //     }
        //   })
      })
    },
    reset () {
      this.formData = {
        server_ip: '',
        username: '',
        password: ''
      }
      this.$refs['myForm'].clearValidate()// 清除校验结果
    },
    selectItem (item) {
      this.selectArr = item
    },
    submitForm (formName) {
      this.$refs[formName].validate((valid) => {
        if (valid) {
          this.addBtn.status = true
          this.addBtn.text = '正在加载...'

          let name = this.formData.username
          let password = this.formData.password
          // ControlService.addUsers(name, password)
          //   .then((res) => {
          //     this.$message({
          //       message: '添加成功',
          //       type: 'success',
          //       duration: '1500'
          //     })
          //     this.visibleDialog = false
          //     this._loadData()
          //   })
          //   .always(() => {
          //     this.addBtn.status = false
          //     this.addBtn.text = '确 定'
          //   })
        }
      })
    }
  }
}
</script>
<style scoped>
.content-inner {
  margin-top: 10px;
}
.butn{
  width: 300px;
  margin-top: 30px;
  margin-left: 83px;
}
.formtitle {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 15px;
}
.el-row{
  margin-bottom: 10px;
}
.el-input{
  width: 100%;
}
.green{
  color: #5daf34;
}
.red{
  color: #ff3600;
}
</style>