<template>
  <div>
    <!-- {{msg}} -->
    <el-button v-for='aa in msg' @click="ddd(aa)">{{aa}}</el-button>

    <el-button @click="switch_dialog">显示隐藏对话框</el-button>

    <el-dialog :visible="dia_visiable" @close="dia_visiable ? dia_visiable = false : dia_visiable = true">
      <el-row>
        提示信息：aaa bbb ccc 
      </el-row>

      <el-form :model="form_data">
        <el-form-item label="输入框aaa" prop="aaa">
          <el-input v-model="form_data.aaa" placeholder="请输入"></el-input>
        </el-form-item>
        <el-form-item label="输入框bbb" prop="bbb">
          <el-input v-model="form_data.bbb" placeholder="请输入"></el-input>
        </el-form-item>
        <el-form-item label="输入框ccc" prop="ccc">
          <el-input v-model="form_data.ccc" placeholder="请输入"></el-input>
        </el-form-item>

      </el-form>

      {{form_data}}

    </el-dialog>

  </div>

</template>
<script>

import controlService from 'services/controlService'
// "dfsdf".spl
export default {
  data(){
    let aaaa = function (a,b,c) {
      if (b != "aaa") {
        return c(new Error('aaa'))
      }
      return c()
    }
    let bbbb = (a,b,c) => {
      if (b != "bbb") {
        return c(new Error('bbb'))
      }
      return c()
    }
    function cccc(a,b,c) {
      if (b != "ccc") {
        return c(new Error('ccc'))
      }
      return c()
    }
    return {
      msg:[],
      dia_visiable:false,
      form_data:{
        aaa:"",
        bbb:'',
        ccc:''
      },
      rules:{
        aaa:[{validator: aaaa, trigger: 'blur'}],
        bbb:[{validator: bbbb, trigger: 'blur'}],
        ccc:[{validator: cccc, trigger: 'blur'}]
      }
    }
  },
  mounted(){
      controlService.getNetAddr().then(
        (res) => {
          this.msg = res['1'].split("|")
          console.log(res)
        }
      ) 
  },
  methods:{
    ddd(nnn){
      console.log(nnn)
      // this.$alert('点击了' + nnn, nnn, {
      //   callback: action => {
      //     alert(action)
      //   }
      // });
      this.$message({
        message:nnn,
        type:"success"
      })
    },
    switch_dialog(){
      this.dia_visiable ? this.dia_visiable = false : this.dia_visiable = true
    }
  }
}
</script>

