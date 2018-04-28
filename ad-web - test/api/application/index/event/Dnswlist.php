<?php
namespace app\index\event;
use app\index\model\DnsWhiteList;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * DNS白名单 分层控制器
 */
class Dnswlist extends Controller {

    protected $M_dns_wlist;
    public function _initialize(){
        $this->M_dns_wlist = new DnsWhiteList;
    }

    //【接口】获取查询
    public function get(){

        $gether = input('get.gether');
        $page = empty(input('get.page')) ? 1 : input('get.page');
        $row = empty(input('get.row')) ? 10 : input('get.row');
        $filter_ip = is_null(input('get.filter_ip')) || input('get.filter_ip') == '' ? NULL : input('get.filter_ip');
        $result = [];$datas = [];$counts = NULL;

        if(!is_null($filter_ip)){
            $counts = $this->M_dns_wlist->countListByNumFilter($gether, $filter_ip);
            $datas = $counts == 0 ? [] : $this->M_dns_wlist->selectListByNumFilter($gether, $filter_ip, $page, $row);
        }else{
            $counts = $this->M_dns_wlist->countListByNum($gether);
            $datas = $counts == 0 ? [] : $this->M_dns_wlist->selectListByNum($gether, $page, $row);
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
        $conf_arr = TrimParams(input('post.8'));
        $save_list[] = [
            "set_num" => $conf_arr[0],
            "ip_range"=> $conf_arr[1],
            "note"    => $conf_arr[2]
        ];
        $result = $this->M_dns_wlist->insertBwList($save_list);
        if(!$result)
            Error('20001');

        $this->_buildAndWrite('add', $save_list);
    }

    //【方法】删除
    public function del(){
        $conf_arr = TrimParams(input('post.8'));
        $set_num = $conf_arr[0];
        $ip_range = $conf_arr[1];

        $datas = $this->M_dns_wlist->selectByNumIpRange($set_num, $ip_range); // 获取要删除的DNS白名单
        if(empty($datas))
            return ;
        
        $result = $this->M_dns_wlist->delectByNumIpRange($set_num, $ip_range);
        if($result < 0)
            Error("20001");

        $this->_buildAndWrite('del', $datas);
    }

    //【方法】清空
    public function clear(){
        $conf_arr = TrimParams(input('post.8'));
        $set_num = $conf_arr[0];

        $datas = $this->M_dns_wlist->selectAllListByNum($set_num); // 获取要删除的DNS白名单配置集
        if(empty($datas))
            return ;
        
        $result = $this->M_dns_wlist->delectAllListByNum($set_num);
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
        $json_conf = $C_builder->forDnswList($oper, $conf_arr);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd b_w_list_config");
    }

}