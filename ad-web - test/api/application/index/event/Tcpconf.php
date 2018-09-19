<?php
namespace app\index\event;
use app\index\model\TcpSetConf;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * TCP端口保护 分层控制器
 */
class Tcpconf extends Controller {

    //【方法】获取查询
    public function get(){
        $gether = input('get.gether');
        $result = [];

        $tcp_set_conf = new TcpSetConf;
        $data = $tcp_set_conf->selectTcpByGether($gether);

        $result['data'] = array_map(function($item){
            return $item['id']."|".$item['conf_value'];
        },$data);

        return $result;
    }

    //【方法】添加配置
    public function add(){
        $conf_arr = TrimParams(input('post.4'));
        $conf_num = $conf_arr[0];
        unset($conf_arr[0]);
        if($conf_arr[1] > $conf_arr[2]){
            $tmp = $conf_arr[1];
            $conf_arr[1] = $conf_arr[2];
            $conf_arr[2] = $tmp;
        }
        $conf_value = implode("|", $conf_arr);

        $tcp_set_conf = new TcpSetConf;
        $count = $tcp_set_conf->countTcpByGether($conf_num);
        if($count >= 16)
            Error("15013");

        //取出已经配置的端口范围的集合
        $exist_conf = $tcp_set_conf->selectTcpByGether($conf_num);
        $port_range_arr = array_map(function($item){
            $tmp_arr = explode("|",$item["conf_value"]);
            return $tmp_arr[0]."|".$tmp_arr[1];
        },$exist_conf);
        if(!empty($port_range_arr)){
            $this->validPortRange($port_range_arr,$conf_arr[1],$conf_arr[2]); //验证端口范围是否已经配置
        }

        $result = $tcp_set_conf->insertTcp($conf_num, $conf_value);
        if($result <= 0)
            Error("20001");

        $data = $tcp_set_conf->selectIpAndConfValueByNum($conf_num);
        //构建配置json并写入
        $this->buildAndWrite($data);
    }

    //【方法】更新配置
    public function update(){
        $conf_arr = TrimParams(input('post.4'));
        $id = $conf_arr[0];
        unset($conf_arr[0]);
        if($conf_arr[1] > $conf_arr[2]){
            $tmp = $conf_arr[1];
            $conf_arr[1] = $conf_arr[2];
            $conf_arr[2] = $tmp;
        }
        $conf_value = implode("|", $conf_arr);

        $tcp_set_conf = new TcpSetConf;
        $data = $tcp_set_conf->selectTcpById($id);
        if(empty($data))
            Error("10027");

        $num_arr = $tcp_set_conf->selectNumById($id);//查询修改的id对应的ip集
        $num = array_map(function($item){
            return $item['set_num'];
        },$num_arr);

        //取出已经配置的端口范围的集合
        $exist_conf = $tcp_set_conf->selectUdpByGetherNoThis($num, $id);
        $port_range_arr = array_map(function($item){
            $tmp_arr = explode("|",$item["conf_value"]);
            return $tmp_arr[0]."|".$tmp_arr[1];
        },$exist_conf);
        if(!empty($port_range_arr)){
            $this->validPortRange($port_range_arr,$conf_arr[1],$conf_arr[2]); //验证端口范围是否已经配置
        }

        $result = $tcp_set_conf->updateTcpById($id, $conf_value);
        if($result < 0)
            Error("20001");

        $data = $tcp_set_conf->selectIpAndConfValueByNum($num);
        //构建配置json并写入
        $this->buildAndWrite($data);
    }

    //【方法】删除配置
    public function del(){
        $ids_arr = TrimParams(input('post.4'));

        $tcp_set_conf = new TcpSetConf;
        $data = $tcp_set_conf->selectTcpById($ids_arr);
        if(empty($data))
            Error("10027");

        $num_arr = $tcp_set_conf->selectNumById($ids_arr);//查询修改的id对应的ip集
        $num = array_map(function($item){
            return $item['set_num'];
        },$num_arr);

        $result = $tcp_set_conf->delectById($ids_arr);
        if($result < 0)
            Error("20001");

        $data = $tcp_set_conf->selectIpAndConfValueByNum($num);
        //构建配置json并写入
        $this->buildAndWrite($data);
    }

    //【方法】验证端口范围是否重复
    private function validPortRange($port_range,$port1,$port2){
        $min_port = $port2 >= $port1 ? $port1 : $port2;
        $max_port = $port2 >= $port1 ? $port2 : $port1;
        foreach($port_range as $range){
            $range_arr = explode("|",$range);
            $min_range = $range_arr[1] > $range_arr[0] ? $range_arr[0] : $range_arr[1] ;
            $max_range = $range_arr[1] > $range_arr[0] ? $range_arr[1] : $range_arr[0] ;
            if(($min_port >= $min_range && $min_port <= $max_range) || ($max_port >= $min_range && $max_port <= $max_range)){
                Error("10025");
            }
        }
    }

    //【方法】将配置写入共享内存并执行命令调取
    private function buildAndWrite($conf){
        if(empty($conf)){
            return;
        }
        //构建配置json
        $C_builder = controller('Confbuilder', 'event');
        $json_conf = $C_builder->forTcpPort($conf);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd server_config");
    }

}