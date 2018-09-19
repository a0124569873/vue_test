<?php
namespace app\index\event;
use app\index\model\HostSetConf;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 主机防护参数 分层控制器
 */
class Hostconf extends Controller {

    //【接口】获取主机防护参数
    public function get(){
        $gether = input('get.gether');

        $host_set_conf = new HostSetConf;
        $data = $host_set_conf->selectProParamByGether($gether);
        if(empty($data) || empty($data[0]['conf_value'])){
            $host_set_conf->data([
                'set_num'=>$gether,
                'conf_value'=>"0|0|0"
            ]);
            $host_set_conf->save();
            return "0|0|0";
        }
        return $data[0]['conf_value'];
    }

    //【接口】设置主机防护参数
    public function set(){
        $conf_arr = TrimParams(input('post.3'));
        $conf_num = $conf_arr[0];
        unset($conf_arr[0]);
        $conf_value = implode("|", $conf_arr);

        $host_set_conf = new HostSetConf;
        $result = $host_set_conf->updateProParam($conf_num, $conf_value);
        if($result < 0)
            Error("20001");

        $data = $host_set_conf->selectIpAndConfValueByNum($conf_num); // 需要更新的记录信息
        if(empty($data))
            return;
        
        //构建配置json
        $C_builder = controller('Confbuilder', 'event');
        $json_conf = $C_builder->forHostParam("update", $data);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd server_config");
        
    }

}