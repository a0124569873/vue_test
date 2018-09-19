<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;

/**
 * 数据报表 主控制器
 */
class Logs extends Controller {

    protected $V_logs;
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get'],
        'checkLogin',
        'checkValid'
    ];
    
    public function _initialize(){
        $this->V_logs = Loader::validate('Logs');
    }

    //【接口】获取
    public function get(){
        if (!$this->V_logs->check(input()))
            return Finalfail($this->V_logs->getError());
            
        $type_arr = input('get.t');
        $start_time = input('get.start_time');
        $end_time = input('get.end_time');
        $target_ip = input('get.target_ip');
        $host_ip = input('get.host_ip');
        $range = input('get.range');
        $target_port = input('get.target_port');
        $attack_type = input('get.attack_type');
        $page = input('get.page');
        $row = input('get.row');
        $C_logs = controller('Logs', 'event');
        $result = [];
        foreach (explode('|', $type_arr) as $type) {
            switch ($type) {
                case '1':
                    // 攻击日志
                    $result['1'] = $C_logs->attackLogs($start_time, $end_time, $target_ip, $target_port, $attack_type, $page, $row);
                    break;
                case '2':
                    // 流量日志
                    $result['2'] = $C_logs->fluxLogs($start_time, $end_time, $host_ip, $range, $page, $row);
                    break;
                case '3':
                    // 连接分析
                    $conn = true;
                    $result['3'] = $C_logs->fluxLogs($start_time, $end_time, $host_ip, $range, $page, $row, $conn);
                    break;
                case '4':
                    // 清洗日志
                    $result['4'] = $C_logs->cleanLogs($start_time, $end_time, $target_ip, $attack_type, $page, $row);
                    break;
                default:
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