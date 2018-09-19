<?php

namespace app\index\controller;
use app\index\model\SysConf;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class System extends Controller{

    protected $m_system;
    protected $v_system;

    public function _initialize(){
        $this->m_system = new SysConf;
        $this->v_system = Loader::validate('System'); 
    }

    protected $beforeActionList = [
        'check_get'   => ['only' => ''],
        'check_post'  => ['only' => ''],
        'check_login' 
    ];

    //【接口】网络地址配置
    public function net(){
        if(!$this->v_system->scene('net')->check(input()))
            return Finalfail($this->v_system->getError());

        $oper = input('get.oper');
        $result = NULL;
        switch($oper){
            case "get":
                $this->check_get();
                $result = $this->getNetConf();
            break;
            case "set":
                $this->check_post();
                $this->setNetConf();
            break;
        }
        return Finalsuccess($result);
        
    }

    //【方法】获取网络地址
    private function getNetConf(){
        $result = GetExecRes("uroot fpcmd show_yb_conf");
        $network = $this->parseNetconf($result['result']);

        $net_conf = [
            "wan_ip"=>isset($network['in_ip']) ? $network['in_ip'] : '',
            "lan_ip"=>isset($network['out_ip']) ? $network['out_ip'] : '',
            "wan_gateway_ip"=>isset($network['in_gateway_ip']) ? $network['in_gateway_ip'] : '',
            "lan_gateway_ip"=>isset($network['out_gateway_ip']) ? $network['out_gateway_ip'] : '',
            "log_ip"=>isset($network['log_ip']) ? $network['log_ip'] : '',
        ];
        return $net_conf;
    }

    //【方法】设置网络地址配置
    public function setNetConf(){
        $conf_arr = RawJsonToArr();
        if(!array_key_exists('net',$conf_arr))
            Error("22001");
        
        $ips= $conf_arr['net'];
        if(!$this->v_system->scene('set')->check(['net'=>$ips]))
            return Finalfail($this->v_system->getError());

        $ip_arr = explode("|", $ips);
        ExcuteExec("uroot fpcmd yb_conf_ip ".$ip_arr[0]." ".$ip_arr[1]." ".$ip_arr[2]." ".$ip_arr[3]);
        ExcuteExec("uroot fpcmd log_ip ".$ip_arr[4]);
        $this->m_system->updateNetConf($ips);
    }

    //【方法】解析执行exec网络地址为数组
    private function parseNetconf($conf_arr){
        $net_conf = [];
        if(!is_array($conf_arr) || empty($conf_arr)){
            return $net_conf;
        }
        foreach($conf_arr as $conf){
            $conf = explode('=', $conf);
            $net_conf[trim($conf[0])] = trim($conf[1]);
        }
        return $net_conf;
    }

    protected function check_login(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }

    protected function check_post(){
        if(!request()->isPost())
            Error("21001","need post method");
    }

    protected function check_get(){
        if(!request()->isGet())
            Error("21002","need get method");
    }

    public function _empty(){
        $this->redirect('/errorpage');
    }
}
