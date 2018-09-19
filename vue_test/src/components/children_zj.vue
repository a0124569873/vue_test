<template>
  <div>
      <el-button @click="fff">变量双向绑定{{foo}}</el-button>
      <el-button @click="incrementCounter">传递事件{{counter}}</el-button>
      <el-button @click="incrementCounter1">传递事件{{counter1}}</el-button>
  </div>
</template>

<script>
export default {
    name:'children_components',
    props:[
        "foo"
    ],
    data(){
        return {
            counter:0,
            counter1:0
        }
    },
    methods:{
        fooo:()=>{
            this.$alert('content', 'title', {
                confirmButtonText: 'confirm',
                callback: action => {
                    
                }
            });
            this.foo = aaa + 1
        },
        fff(){
            this.foo+=1
            this.$emit('update:foo', this.foo)
        },
        incrementCounter() {
            this.counter += 1
            this.$emit('increment')
        },
        incrementCounter1() {
            this.counter1 += 1
            this.$emit('increment',1000)
        }
    }

}
</script>

