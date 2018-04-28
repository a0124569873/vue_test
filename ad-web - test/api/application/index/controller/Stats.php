<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;

/**
 * 状态监控 主控制器
 */
class Stats extends Controller {

    protected $V_stats;// 校验器-状态监控
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get'],
        'checkLogin',
        'checkValid'
    ];
    
    public function _initialize(){
        $this->redis = new \Redis();
        $this->V_stats = Loader::validate('Stats');
    }

    //【接口】获取状态监控
    public function get(){
        if(!$this->V_stats->scene('get')->check(input()))
            return Finalfail($this->V_stats->getError());

        $result = [];
        $type_arr = input('get.t');
        $ip = input('get.ip');
        $C_system = controller('System', 'event');
        $C_network = controller('Network', 'event');
        $C_tmplist = controller('Tempbwlist', 'event');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 实时流量状态
                    $recent = is_null(input('get.r')) ? 1 : input('get.r');
                    $ip = is_null(input('get.ip')) ? "" : input('get.ip');

                    $result['1'] = $C_network->RealFlux($recent,$ip);
                    break;
                case "2":
                    // CPU实时状态
                    $recent = is_null(input('get.r')) ? 1 : input('get.r');
                    $result['2'] = $C_system->H_RealCpuStats($recent);
                    break;
                case "3": 
                    // disk实时状态
                    $recent = is_null(input('get.r')) ? 1 : input('get.r');
                    $result['3'] = $C_system->H_RealDiskStats($recent);
                    break;
                case "4": 
                    // memory实时状态
                    $recent = is_null(input('get.r')) ? 1 : input('get.r');
                    $result['4'] = $C_system->H_RealMemoryStats($recent);
                    break;
                case "17":
                    // 网卡状态
                    $result['17'] = $C_system->RealNetworkStats();
                    break;
                case "18": // 主机状态
                    if (!$this->V_stats->scene('get_hoststats')->check(input()))
                        Error($this->V_stats->getError());

                    $orderby = is_null(input('get.orderby')) ? "in_bps" : input('get.orderby');
                    $limit = is_null(input('get.limit')) ? 10 : input('get.limit');
                    $desc = is_null(input('get.desc')) ? "true" : input('get.desc');
                    $result['18'] = $C_network->HostFluxTop($orderby,$limit,$desc);

                    break;
                case "19": // 临时黑白名单
                    if (!$this->V_stats->scene('get_tmp_bw_list')->check(input()))
                        Error($this->V_stats->getError());

                    $list_type = input("get.list_type");
                    $result['19'] = $C_tmplist->get($list_type);
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】获取状态监控
    public function del(){
        if(!$this->V_stats->scene('del')->check(input()))
            return Finalfail($this->V_stats->getError());

        $type = input('get.t');
        $C_tmplist = controller('Tempbwlist', 'event');
        switch($type){
            case "19": // 临时黑白名单
                $param = ['list_type' => input("get.list_type"),'del_tmp_bw' => input("post.19")];
                if (!$this->V_stats->scene('del_tmp_bw_list')->check($param))
                    Error($this->V_stats->getError());

                $list_type = input("get.list_type");
                $C_tmplist->del($list_type);
                break;
            default:
                #code...
                break;
        }
        return Finalsuccess();
    }
    
    //---------------------------------------------------------------------------

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