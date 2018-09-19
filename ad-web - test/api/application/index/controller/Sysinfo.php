<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;

/**
 * 设备概况 主控制器
 */
class Sysinfo extends Controller {

    protected $V_sysinfo;// 校验器-设备概况
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get'],        
        'checkLogin',
        'checkValid'
    ];
    
    public function _initialize(){
        $this->V_sysinfo = Loader::validate('Sysinfo'); 
    }

    //【接口】获取设备概况
    public function get(){
        if(!$this->V_sysinfo->scene('get')->check(input()))
            return Finalfail($this->V_sysinfo->getError());

        $result = [];
        $type_arr = input('get.t');
        $C_system = controller('System', 'event');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 防护主机数
                    $result['1'] = $C_system->HostCount();
                    break;
                case "2":
                    // 开机时间
                    $result['2'] = $C_system->startTime();
                    break;
                case "3":
                    // 流量概况
                    $result['3'] = $C_system->GlobalNetStat();
                    break;
                case "4":
                    // CPU
                    $result['4'] = $C_system->RealCPUStats();
                    break;
                case "5":
                    // 证书信息
                    $C_license = controller('License');
                    $result['5'] = $C_license->get();
                    break;
                case "6":
                    // 实时数据监控
                    $ip = is_null(input('get.ip')) ? "" : input('get.ip');
                    $result['6'] = $C_system->GlobalNetLogs($ip);
                    break;
                case "7":
                    // 内存
                    $result["7"] = $C_system->RealRAMStats();
                    break;
                case "8":
                    // 硬盘
                    $result["8"] = $C_system->RealHDStats();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【前置方法】验证登陆
    protected function checkLogin(){
        if(!CheckLoginToken())
            Error('12001','need login or token error');
    }
    //【前置方法】验证设备授权
    protected function checkValid(){
        $status = CheckValid();
        if($status != '0')
            Error($status,'need valid');
    }
    //【前置方法】验证post请求
    protected function checkPost(){
        if(!request()->isPost())
            Error("21001","need post method");
    }
    //【前置方法】验证get请求
    protected function checkGet(){
        if(!request()->isGet())
            Error("21002","need get method");
    }
    //【空方法】
    public function _empty(){
        $this->redirect('/errorpage');
    }

}