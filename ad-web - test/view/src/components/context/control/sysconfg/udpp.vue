<template>
  <div class="content">
    <!-- {{msg}} -->
    <!-- <el-button v-for='aa in msg' @click="ddd(aa)">{{aa}}</el-button> -->

    <el-button @click="switch_dialog" >显示隐藏对话框</el-button>

    <h2 class="config-title"> sdasdasdasd</h2>

    <el-dialog :visible="dia_visiable" @close="dia_visiable = false">
      <el-row>
        提示信息：aaa bbb ccc 
      </el-row>

      <el-form :model="formData" :rules="rules" ref="myForm222">
        <el-form-item label="输入框aaa" prop="aaa">
          <el-input v-model="formData.aaa"></el-input>
        </el-form-item>
        <el-form-item label="输入框bbb" prop="bbb">
          <el-input v-model.trim="formData.bbb" placeholder="请输入"></el-input>
        </el-form-item>
        <el-form-item label="输入框ccc" prop="ccc">
          <el-input v-model="formData.ccc" placeholder="请输入"></el-input>
        </el-form-item>
        <el-col style="text-align: right">
          <el-button @click="addItem('myForm222')">提交</el-button>
          <el-button @click.prevent.stop="dia_visiable = false">取消</el-button>
        </el-col>
      </el-form>

      {{formData}}

    </el-dialog>
    <el-row>
    <el-col>
        <h2 class="config-title" style="padding-bottom: 10px">
          日志服务器地址
        </h2>
        <el-form :model="tf" :rules="rules" ref="myForm333">
          <el-form-item label="IP" style="margin-left: 5px" prop="ta">
            <el-input style="width:200px;margin-right: 20px;" v-model="tf.ta"></el-input>
          </el-form-item>
        </el-form>
        <el-col style="text-align: right">
          <el-button type="primary" @click="s_tf('myForm333')">保存</el-button>
        </el-col>

    </el-col>
    </el-row>
    <el-row>
      <h2 class="config-title" style="padding-bottom: 10px">
            日志服务器地址
      </h2>

      <el-col>

        <div class="formtitle">
          <div>
            <el-form :model="fd" :inline="true">
              <el-form-item label="查询ip:">
                <el-input v-model="fd.fi" size="mini"></el-input>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="alert(fd.fi)">查询</el-button>
              </el-form-item>
            </el-form>
          </div>
          <div>
            <el-tooltip content="清空所有数据">
              <el-button type="primary" icon="el-icon-delete" @click="clear">清空</el-button>
            </el-tooltip>
            <el-tooltip content="删除一条数据">
              <el-button type="primary" icon="el-icon-remove-outline" >删除</el-button>
            </el-tooltip>
            <el-tooltip content="添加数据">
              <el-button type="primary" icon="el-icon-circle-plus-outline" @click="switch_dialog">添加</el-button>
            </el-tooltip>
          </div>
        </div>


        <el-table :data="ddd" border
          @selection-change='sc'
        >
          <el-table-column type="selection"></el-table-column>
          <el-table-column type="index" label="序号"></el-table-column>
          <el-table-column label="aaa" prop="aaa">
            <!-- <template slot-scope="scop">
              {{scop.row.aaa}}
            </template> -->
          </el-table-column>
          <el-table-column label="bbb">
            <template slot-scope="scop">
              {{scop.row.bbb + '11111'}}
            </template>
          </el-table-column>
          <el-table-column label="ccc">
            <template slot-scope="scop">
              {{scop.row.ccc}}
            </template>
          </el-table-column>
        </el-table>

        <el-pagination
          @size-change="changesize"
          @current-change="changePage"
          :page-sizes="[10, 20, 30, 40]"
          :page-size="10"
          layout="sizes, prev, pager, next"
          :total="total">
        </el-pagination>

      </el-col>
    </el-row>


  </div>

</template>
<script>

import controlService from 'services/controlService'
import index from 'vue';
// "dfsdf".spl
export default {
  data(){
    const aaaa = (rule, value, callback) => {
      if (value == 'aaa') {
        return callback(new Error('aaa'))
      }
      return callback()
    }
    const bbbb = (rule, value, callback) => {
      if (value == "bbb") {
        return callback(new Error('bbb'))
      }
      return callback()
    }
    const cccc = (rule, value, callback) => {
      if (value == "ccc") {
        return callback(new Error('ccc'))
      }
      return callback()
    }

    const ddddd = (rule, value, callback) => {

      let regIp = /^((2[0-4]\d|25[0-5]|[01]?\d\d?)\.){3}(2[0-4]\d|25[0-5]|[01]?\d\d?)$/
      
      if (!regIp.test(value)) {
        callback(new Error('ip error'))
      }
      callback()
    }

    return {
      msg:[],
      dia_visiable:false,
      formData:{
        aaa:"111",
        bbb:"222",
        ccc:"333"
      },
      tf:{
        ta:''
      },
      fd:{
        fi:''
      },
      ccc: [],
      ddd: [],
      total:1000,
      pagee:1,
      pagesize:10,
      rules:{
        aaa:[
          // {required: true, message: 'not empty', trigger: 'change'},
          // {required: true, type:'number', message: 'not number', trigger: 'change'},
          {validator: aaaa, trigger: 'change'}
          ],
        bbb:[{validator: bbbb, trigger: 'blur'}],
        ccc:[{validator: cccc, trigger: 'blur'}],
        ta:[
          // {required: true, message: "ip not empty!", trigger: 'change'},
          {type: 'string', message: 'is not number', trigger: 'change'},
          {validator: ddddd, trigger: 'blur'}

          ]
      }
    }
  },
  // computed:{
  //   td: () => {
  //     this.ccc = Array()
  //     for(let i = 0; i < 1; i++){
  //       this.ccc.push({aaa:'aaa', bbb: 'bbb', ccc: 'ccc'})
  //     }
  //     return this.ccc
  //   }
  // },
  mounted(){
      // controlService.getNetAddr().then(
      //   (res) => {
      //     this.msg = res['1'].split("|")
          // console.log(res)
      //   }
      // ) 

      // console.log($("iii"))

      for (let i = 0; i < 1000; i++) {
        this.ccc.push({aaa: 'aaa' + i, bbb: 'bbb', ccc: 'ccc',id: i})
      }
      this.ccc.sort(function(a,b){ return Math.random()>.5 ? -1 : 1;});

      this.ddd = this.ccc.slice((this.pagee - 1) * this.pagesize,(this.pagee - 1) * this.pagesize + this.pagesize)
      // console.log(this.ddd)

  },
  methods:{

    sc(cccc){
      let ccc = ""
      cccc.forEach(element => {
        ccc += (element.id + " ")
      });
      // alert("选中的id是：" + ccc)
      this.$message({
          message: "选中的id是：" + ccc,
          type: 'success'
      });

    },

    clear(){
      this.ccc = []
      this.total = 0
    },
    changePage(vvv){
      this.pagee = vvv
      this.ddd = this.ccc.slice((this.pagee - 1) * this.pagesize,(this.pagee - 1) * this.pagesize + this.pagesize)
      this.ddd.sort(function (obj1, obj2) {
          var val1 = obj1.id;
          var val2 = obj2.id;
          if (val1 < val2) {
              return -1;
          } else if (val1 > val2) {
              return 1;
          } else {
              return 0;
          }            
      }).reverse()
    },
    changesize(vvv){
      this.pagesize = vvv
      this.ddd = this.ccc.slice((this.pagee - 1) * this.pagesize,(this.pagee - 1) * this.pagesize + this.pagesize)
      this.ddd.sort(function (obj1, obj2) {
          var val1 = obj1.id;
          var val2 = obj2.id;
          if (val1 < val2) {
              return -1;
          } else if (val1 > val2) {
              return 1;
          } else {
              return 0;
          }            
      }).reverse()
    },
    addItem(namne){
      this.$refs[namne].validate((valid) => {
          if (valid) {
              this.$message({
                  message: "ip is: " + this.tf.ta,
                  type: 'success'
              });
              this.ccc.push({aaa: this.formData.aaa, bbb: this.formData.bbb, ccc: this.formData.ccc})
              this.total++
          } else {
              console.log('error submit!!');
              return false;
          }
      });

    },
    cancle(){
      this.dia_visiable = false
      this.formData.aaa = ''
      this.formData.bbb = ''
      this.formData.ccc = ''
    },
    // ddd(nnn){
    //   console.log(nnn)
    //   // this.$alert('点击了' + nnn, nnn, {
    //   //   callback: action => {
    //   //     alert(action)
    //   //   }
    //   // });
    //   this.$message({
    //     message:nnn,
    //     type:"success"
    //   })


    // },


    switch_dialog(){
      this.dia_visiable ? this.dia_visiable = false : this.dia_visiable = true
    },
    s_tf(namne){
      this.$refs[namne].validate((valid) => {
          if (valid) {
              this.$message({
                  message: "ip is: " + this.tf.ta,
                  type: 'success'
              });
          } else {
              console.log('error submit!!');
              return false;
          }
      });


      console.log(this.tf.ta)
    }
  }
}
</script>

<style>
  .config-title{
    text-align: left;
    color: #7a7f8a;
    line-height: 30px;
    font-size: 15px;
    padding: 0 5px;
    margin-bottom: 15px;
    border-bottom: 1px solid #b4bccc; 
  }
  .formtitle{
    display: flex;
    justify-content: space-between;
    padding-left: 20px;
    margin-bottom: 15px;
    text-align: left;
  }
</style>


