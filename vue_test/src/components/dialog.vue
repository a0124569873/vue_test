<template>
  <div id="zxn">
      <el-dialog :visible="d_vis" @close='d_vis = false'>

        <el-form :model="formdata" :rules="rules" ref="myFrom111" >
            <el-form-item label="输入aaa" prop="aaa">
                <el-input v-model.number="formdata.aaa"></el-input>
            </el-form-item>
            <el-form-item label="输入bbb" prop="bbb">
                <el-input v-model="formdata.bbb"></el-input>
            </el-form-item>
            <el-form-item label="输入ccc" prop="ccc">
                <el-input v-model="formdata.ccc"></el-input>
            </el-form-item>
        </el-form>

        <el-button @click="d_vis = false">
            hide
        </el-button>
        <el-button @click="s_m(formdata.aaa+' '+formdata.bbb + ' ' + formdata.ccc)">
            show_content
        </el-button>

        <el-button @click="s_v('myForm')">
            submit
        </el-button>
        

    </el-dialog>

    <el-button @click="d_v()">
        show
    </el-button>
    <el-button @click="toZxn('/')">
        home
    </el-button>
  </div>
</template>
<script>
export default {
    name:"dialog_components",
    data(){
        const aaaa = (rule, value, callback) => {
            console.log(value);
            if (value == 111) {
                callback();
            }else{
                callback(new Error('aaa'));
            }
        }
        const bbbb = (rule, value, callback) => {
            console.log(value);
            if (value == "bbb") {
                callback();
            }else{
                callback(new Error('bbb'));
            }
        }
        const cccc = (rule, value, callback) => {
            console.log(value);
            if (value == "ccc") {
                callback();
            }else{
                callback(new Error('ccc'));
            }
        }

        return {
            msg: "ddsfdsf",
            d_vis: false,
            formdata: {
                aaa: "",
                bbb: "",
                ccc: ""
            },
            rules:{
                aaa: [
                    // {required: true, type:'number',max:5,min:1,message: 'length error!',trigger: 'change'},
                    { validator: aaaa, trigger: 'change' }
                    ],
                bbb: [{ validator: bbbb, trigger: 'blur' }],
                ccc: [{ validator: cccc, trigger: 'blur' }]
            }
        }
    },
    watch:{
        d_vis : function (){
            let cccc = "";

            if (this.d_vis) {
                cccc = "显示对话框"
            }else{
                cccc = "隐藏对话框"
            }

            this.$notify({
                title: 'It works!',
                type: 'success',
                message: cccc,
                duration: 5000
            })
            console.log(this.d_vis)
        }
    },
    methods:{
        d_v(){
            
            let cccc = "";

            if (this.d_vis) {
                this.d_vis = false;
                cccc = "隐藏对话框"
            }else{
                this.d_vis = true;
                cccc = "显示对话框"
            }

            this.$notify({
                title: 'It works!',
                type: 'success',
                message: cccc,
                duration: 5000
            })

        },
        s_v(name_a){
                // console.log(this.$refs[name_a]);
                // console.log(this.$refs["myFrom111"]);
                // console.log(this.$refs[name_a]);
                // console.log(name_a);
                this.$refs["myFrom111"].validate((valid) => {
                    if (valid) {
                        this.$message({
                            message: "dfsdsfdsfdsf",
                            type: 'success'
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });

        },
        toZxn(patth){
            this.$router.push(patth);
        },

        s_m(mes){
            this.$message({
                message: mes,
                type: 'success',
                showClose:true,
                duration:0

            });
        }

    }
}
</script>

