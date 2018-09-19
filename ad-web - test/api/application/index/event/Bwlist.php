<?php
namespace app\index\event;
use app\index\model\BlackWhiteList;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 黑白名单 分层控制器
 */
class Bwlist extends Controller {

    protected $M_bw_list;
    public function _initialize(){
        $this->M_bw_list = new BlackWhiteList;
    }

    //【接口】获取查询
    public function get(){

        $gether = input('get.gether');
        $list_type = input('get.list_type');
        $page = empty(input('get.page')) ? 1 : input('get.page');
        $row = empty(input('get.row')) ? 10 : input('get.row');
        $filter_ip = is_null(input('get.filter_ip')) || input('get.filter_ip') == '' ? NULL : input('get.filter_ip');
        $result = [];$datas = [];$counts = NULL;

        if(!is_null($filter_ip)){
            $counts = $this->M_bw_list->countListByNumTypeIpFilter($gether, $list_type, $filter_ip);
            $datas = $counts == 0 ? [] : $this->M_bw_list->selectListByNumTypeIpFilter($gether, $list_type, $filter_ip, $page, $row);
        }else{
            $counts = $this->M_bw_list->countListByNumType($gether, $list_type);
            $datas = $counts == 0 ? [] : $this->M_bw_list->selectListByNumType($gether, $list_type, $page, $row);
        }
        $tmp_datas = array_map(function($item){
            return $item['ip_range']."|".$item['note']."|".$item['last_update'];
        },$datas);

        $result['data'] = $tmp_datas;
        $result['count'] = $counts;
        return $result;
    }

    //【接口】添加
    public function add(){
        $conf_arr = TrimParams(input('post.6'));
        $save_list[] = [
            "set_num" => $conf_arr[0],
            "type"    => $conf_arr[1],
            "ip_range"=> $conf_arr[2],
            "note"    => $conf_arr[3]
        ];
        $result = $this->M_bw_list->insertBwListByNumType($save_list);
        if(!$result)
            Error('20001');

        $this->_buildAndWrite('add', $save_list);
    }

    //【方法】删除
    public function del(){
        $conf_arr = TrimParams(input('post.6'));
        $set_num = $conf_arr[0];
        $list_type = $conf_arr[1];
        $ip_range = $conf_arr[2];

        $datas = $this->M_bw_list->selectByNumTypeIpRange($set_num, $list_type, $ip_range); // 获取要删除的黑/白名单
        if(empty($datas))
            return ;
        
        $result = $this->M_bw_list->delectByNumTypeIpRange($set_num, $list_type, $ip_range);
        if($result < 0)
            Error("20001");

        $this->_buildAndWrite('del', $datas);
    }

    //【方法】清空
    public function clear(){
        $conf_arr = TrimParams(input('post.6'));
        $set_num = $conf_arr[0];
        $list_type = $conf_arr[1];

        $datas = $this->M_bw_list->selectAllListByNumType($set_num, $list_type); // 获取要删除的黑/白名单
        if(empty($datas))
            return ;
        
        $result = $this->M_bw_list->delectAllListByNumType($set_num, $list_type);
        if($result < 0)
            Error("20001");

        $this->_buildAndWrite('del', $datas);
    }

    /** 构建配置json并写入
     * @param  Array    $conf_arr  修改后的黑白名单配置
    */
    private function _buildAndWrite($oper, $conf_arr){
        //构建配置json
        $C_builder = controller('Confbuilder', 'event');
        $json_conf = $C_builder->forBwList($oper, $conf_arr);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd b_w_list_config");
    }

}