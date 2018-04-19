<template>
    <div>
        <p>变量双向绑定：{{foo1}}</p>
        <testzj :foo.sync="foo1" @increment="incrementTotal"></testzj>
        <p>事件传递变量：{{ total }}</p>
    </div>
    
</template>
<script>

import testzj from '@/components/children_zj'

export default {
    name:'father_components',
    data() {
        return {
            foo1:100,
            total:200
        }
    },
    components:{
        testzj
    },
    methods:{
        incrementTotal:function(aaa = 10) {
            this.total += 1
            console.log(aaa)
        }
    }
}
</script>
