<template>
  <div class="pages">
    <Select 
      class="pageSelect" 
      v-model="size" 
      @on-change="changePageSize" 
      style="width:100px;margin-right:5px;" 
      size="small">
      <Option :value="item" v-for='item in pageSize' :key="item">{{item}}条/页</Option>
    </Select>
    <span class="prepage button button--aylen" @click='prePage($event)' onselectstart="return false;">{{$t('common.perpage')}}</span>
    <span id="currentpage" onselectstart="return false;">{{currentpage}}</span>
    <span class="nextpage button button--aylen" @click='nextPage($event)' onselectstart="return false;">{{$t('common.nextpage')}}</span>
    <input type="text" class="toNpage" placeholder="1" >
    <span  class="button button--aylen" @click='whichPage($event)' onselectstart="return false;">{{$t('common.jumppage')}}</span>
    <span onselectstart="return false;">{{$t('common.totalpage')}} {{totalpage==0?1:totalpage}} {{$t('common.pageunit')}}</span>
  </div>
</template>
<script>
  export default {
    props: {
      totalNum: {
        type: Number,
        required: true
      },
      perNum: {
        type: Number
      },
      nowpage: {
        type: Number,
        required: true
      },
      pageSize: {
        type: Array
      }
    },
    computed: {
      totalpage () {
        return Math.ceil(this.totalNum / this.size)
      }
    },
    data () {
      return {
        currentpage: 1,
        size: this.perNum
      }
    },
    watch: {
      nowpage: function () {
        this.currentpage = this.nowpage
      }
    },
    methods: {
      changePageSize () {
        this.$emit('changePageSize', this.size)
      },
      prePage (event) {
        let ele = $(event.currentTarget)
        this.currentpage--
        if (this.currentpage < 1) {
          this.currentpage = 1
          this.popoverShow(ele, this.$t('common.firstPageNotice'))
        } else {
          this.$emit('curPage', this.currentpage)
        }
      },
      nextPage (event) {
        let ele = $(event.currentTarget)
        this.currentpage++
        if (this.currentpage > this.totalpage) {
          this.currentpage = this.totalpage
          if (this.totalpage == 0) {
            this.currentpage = 1
          }
          this.popoverShow(ele, this.$t('common.lastPageNotice'))
        } else {
          this.$emit('curPage', this.currentpage)
        }
      },
      whichPage (event) {
        let ele = $(event.currentTarget)
        let input_ele = $(event.currentTarget).parent().find('.toNpage')
        let topage = Number(input_ele.val())
        if (topage == '') {
          this.popoverShow(input_ele, this.$t('common.jumpPageNotice'))
        } else if (topage <= this.totalpage && topage >= 1) {
          this.currentpage = topage
          this.$emit('curPage', this.currentpage)
        } else {
          this.popoverShow(input_ele, this.$t('common.overallPageNotice'))
        }
      },
      popoverShow: function (ele, content) {
        $(ele).attr({
          'data-toggle': 'popover',
          'data-content': content,
          'data-placement': 'bottom'
        }).popover('show')
        setTimeout(function () {
          $(ele).popover('destroy')
        }, 1000)
      }
    }
  }
</script>
<style scoped>
  .pages {
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 5px 0;
    margin:0 5px;
    border-bottom: 1px dashed #f1f1f1;
  }
  .pages span {
    display: inline-block;
    padding: 2px 8px;
    margin: 0 3px;
    cursor: pointer;
    vertical-align: middle;
    -moz-user-select:none;
  }
  .pages .button {
    color: #444;
    border-radius: 3px;
  }
  
  input {
    width: 40px;
    background: #E8E8E8;
    height: 18px;
    border-radius: 3px;
    margin-left: 10px;
    text-align: center;
  }
  
  input::-webkit-input-placeholder {
      color:#FFFFFF;
  }
  input::-moz-placeholder { 
      color:#FFFFFF;
  }
  input:-ms-input-placeholder{
     color:#FFFFFF;
  }
</style>