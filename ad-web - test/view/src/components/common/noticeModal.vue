<template>
        <div class="modal" :id = "id" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">         
                        <h3 class="modal-title" id="myModalLabel">{{title}}</h3>
                        <span class="close" data-dismiss="modal" aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <!--成功-->
                        <div v-if="status === 'ok'">
                            <p style="line-height:1.6;color:#00cc66;font-size:16px;margin-top:20px;margin-left:10px">
                              <img src="../img/success.png" style="margin-right: 10px"/>
                              <span sytle="font-size:16px" v-html="msg"></span>
                            </p>
                        </div>
                        <!--失败-->
                        <div v-else-if="status === 'error'">        
                            <p style="line-height:1.6;color:#b31313;font-size:16px;margin-left:10px">
                              <img src="../img/cry.png" style="margin-right: 10px"/>
                              <span sytle="font-size:16px" v-html="msg"></span>
                            </p>
                        </div>	         
                    </div>
                    <slot name='modal_footer'>
                        <div class="modal-footer">                       
                            <button class="sure-btn button button--aylen" data-dismiss="modal">确认</button>					
                        </div>
                    </slot>
                </div>
            </div>
        </div>               
</template>
<script>
export default {
  props: {
    id: {
      type: String
    },
    title: {
      type: String
    },
    status: {
      type: String
    },
    msg: {
      type: String
    },
    callback: {
      type: Function
    }
  },

  mounted () {
    //   $('#notice-modal').on('hidden.bs.modal', this.callback)
  },
  beforeDestroy () {
    //   $('#notice-modal').off('hidden.bs.modal')
  }
}
</script>
<style scoped>
.modal-footer{
    display: flex;
    justify-content: flex-end;
}
</style>