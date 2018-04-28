<?php
namespace app\index\event;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 临时黑白名单 分层控制器
 */
class Tempbwlist extends Controller {

    //【接口】获取查询
    public function get($list_type){
        ExcuteExec("uroot fpcmd fp_config_shm_clear -t_b_w_table");// 清空之前的黑白名单
        ExcuteExec("uroot fpcmd temp_b_w_table_status");
        $shm_data= ReadShm(TMP_BW_LIST_SHM_FILE, "1", TMP_BW_LIST_SHM_SIZE, TMP_BW_LIST_SHM_SIZE);
        if(is_null($shm_data))
            Error("10033");

        $tmp_bw_list_datas = trim($shm_data);
        // $tmp_bw_list_datas = '{"t_b_list":[{"dst_ip":"192.168.2.52","src_ip":"192.168.2.53"},{"dst_ip":"192.168.2.52","src_ip":"192.168.2.53"}],"t_w_list":[{"dst_ip":"192.168.2.52","src_ip":"192.168.2.53"},{"dst_ip":"192.168.2.52","src_ip":"192.168.2.53"}]}';
        if(empty($tmp_bw_list_datas)){
            $list["b_list"] = [];
            $list["w_list"] = [];
            return $list;
        }

        $list_arr = $this->_parseTmplist($tmp_bw_list_datas);
        if($list_type == "1"){
            $list['b_list'] = $list_arr['b_list'];
        }elseif($list_type == '0'){
            $list['w_list'] = $list_arr['w_list'];
        }else{
            $list['b_list'] = $list_arr['b_list'];
            $list['w_list'] = $list_arr['w_list'];
        }
        return $list;
    }

    //【方法】删除
    public function del($list_type){
        $list_arr = explode(",", input("post.19"));
        $order_plus = $list_type == "1" ? "temp_b_table_del" : "temp_w_table_del";
        foreach($list_arr as $tmp){
            $tmp = explode("|", $tmp);
            ExcuteExec("uroot fpcmd $order_plus -dst_ip ".$tmp[0]." -src_ip ".$tmp[1]);
        }
    }

    private function _parseTmplist($tmp_bw_list_datas){
        $tmp_list_arr = json_decode($tmp_bw_list_datas, TRUE);
        $b_list = [];
        $w_list = [];
        
        if(isset($tmp_list_arr['t_b_list'])){
            foreach($tmp_list_arr['t_b_list'] as $tmp){
                $b_list[] = $tmp["dst_ip"]."|".$tmp['src_ip'];
            }
        }

        if(isset($tmp_list_arr['t_w_list'])){
            foreach($tmp_list_arr['t_w_list'] as $tmp){
                $w_list[] = $tmp["dst_ip"]."|".$tmp['src_ip'];
            }
        }

        return ['b_list'=>$b_list, 'w_list'=>$w_list];
    }

   
}