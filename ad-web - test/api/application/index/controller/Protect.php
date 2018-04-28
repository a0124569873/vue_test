<?php
namespace app\index\controller;
use think\Controller;
use think\Loader;

/**
 * 防御配置 主控制器
 */
class Protect extends Controller {

    protected $V_protect;// 校验器
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get'],
        'checkPost'  => ['only' => 'add,update,del'],
        'checkLogin',
        'checkValid',
    ];
    
    public function _initialize(){
        $this->V_protect = Loader::validate('Protect');
    }

    //【接口】获取
    public function get(){
        if(!$this->V_protect->scene('get')->check(input()))
            return Finalfail($this->V_protect->getError());

        $result = [];
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 系统防御模式
                    $result['1'] = $this->getSysProModal();
                    break;
                case "2":
                    // 系统防御参数
                    $result['2'] = $this->getSysProParam();
                    break;
                case "3":
                    // 主机防护参数
                    $result['3'] = $this->getHostProParam();
                    break;
                case "4":
                    // TCP端口保护
                    $result['4'] = $this->getTcpPro();
                    break;
                case "5":
                    // UDP端口保护
                    $result['5'] = $this->getUdpPro();
                    break;
                case "6":
                    // 黑白名单
                    $result['6'] = $this->getBlackWhiteList();
                    break;
                case "7":
                    // 防护范围
                    $result['7'] = $this->getHostProtect();
                    break;
                case "8":
                    // DNS 白名单
                    $result['8'] = $this->getDnsWhiteList();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】添加
    public function add(){
        if(!$this->V_protect->scene('add')->check(input()))
            return Finalfail($this->V_protect->getError());

        $result = NULL;
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "4":
                    // TCP端口保护
                    $this->addTcpPro();
                    break;
                case "5":
                    // UDP端口保护
                    $this->addUdpPro();
                    break;
                case "6":
                    // 黑白名单
                    $this->addBlackWhiteList();
                    break;
                case "7":
                    // 防护范围
                    $this->addHostProtect();
                    break;
                case "8":
                    // NDS 白名单
                    $this->addDnsWhiteList();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】更新
    public function update(){
        if(!$this->V_protect->scene('update')->check(input()))
            return Finalfail($this->V_protect->getError());

        $result = NULL;
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 系统防御模式
                    $this->updateSysProModal();
                    break;
                case "2":
                    // 系统防御参数
                    $this->updateSysProParam();
                    break;
                case "3":
                    // 主机防护参数
                    $this->updateHostProParam();
                    break;
                case "4":
                    // TCP端口保护
                    $this->updateTcpPro();
                    break;
                case "5":
                    // UDP端口保护
                    $this->updateUdpPro();
                    break;
                case "7":
                    // 防护范围
                    $this->updateHostProtect();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】删除
    public function del(){
        if(!$this->V_protect->scene('del')->check(input()))
            return Finalfail($this->V_protect->getError());

        $result = NULL;
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "4":
                    // TCP端口保护
                    $this->delTcpPro();
                    break;
                case "5":
                    // UDP端口保护
                    $this->delUdpPro();
                    break;
                case "6":
                    // 黑白名单
                    $this->delBlackWhiteList();
                    break;
                case "7":
                    // 防护范围
                    $this->delHostProtect();
                    break;
                case "8":
                    // NDS 白名单
                    $this->delDnsWhiteList();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】删除
    public function clear(){
        if(!$this->V_protect->scene('clear')->check(input()))
            return Finalfail($this->V_protect->getError());

        $result = NULL;
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "6":
                    // 黑白名单
                    $this->clearBlackWhiteList();
                    break;
                case "8":
                    // NDS 白名单
                    $this->clearDnsWhiteList();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //---------------------------------------------------------------------------

    //【方法】获取系统防御模式
    private function getSysProModal(){
        $result = NULL;
        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->get(1); 
        return $result;
    }
    //【方法】获取系统防御参数
    private function getSysProParam(){
        $result = NULL;
        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->get(2);
        return $result;
    }
    //【方法】获取主机防护参数
    private function getHostProParam(){
        if(!$this->V_protect->scene('get_hostproparam')->check(input()))
            Error($this->V_protect->getError());

        $result = NULL;
        $C_hostconf = controller('Hostconf', 'event');
        $result = $C_hostconf->get(); 
        return $result;
    }
    //【方法】获取TCP端口保护
    private function getTcpPro(){
        if(!$this->V_protect->scene('get_tcppro')->check(input()))
            Error($this->V_protect->getError());
        
        $result = NULL;
        $C_tcpconf = controller('Tcpconf', 'event');
        $result = $C_tcpconf->get(); 
        return $result;
    }
    //【方法】获取UDP端口保护
    private function getUdpPro(){
        if(!$this->V_protect->scene('get_udppro')->check(input()))
            Error($this->V_protect->getError());
        
        $result = NULL;
        $C_udpconf = controller('Udpconf', 'event');
        $result = $C_udpconf->get(); 
        return $result;
    }
    //【方法】获取黑白名单
    private function getBlackWhiteList(){
        if(!$this->V_protect->scene('get_blackwhitelist')->check(input()))
            Error($this->V_protect->getError());
        
        $result = NULL;
        $C_bwlist = controller('Bwlist', 'event');
        $result = $C_bwlist->get();
        return $result;
    }
    //【方法】获取防护范围
    private function getHostProtect(){
        if(!$this->V_protect->scene('get_hostprotect')->check(input()))
            Error($this->V_protect->getError());

        $result = NULL;
        $C_protecting = controller('Protecting', 'event');
        $result = $C_protecting->get();
        return $result;
    }
    //【方法】获取DNS白名单
    private function getDnsWhiteList(){
        if(!$this->V_protect->scene('get_dnswhitelist')->check(input()))
            Error($this->V_protect->getError());
        
        $result = NULL;
        $C_dnswlist = controller('Dnswlist', 'event');
        $result = $C_dnswlist->get();
        return $result;
    }

    //【方法】添加TCP端口保护
    private function addTcpPro(){
        $param = ['add4'=>input('post.4')];
        if(!$this->V_protect->scene('add_tcppro')->check($param))
            Error($this->V_protect->getError());
            
        $C_tcpconf = controller('Tcpconf', 'event');
        $C_tcpconf->add(); 
    }
    //【方法】添加UDP端口保护
    private function addUdpPro(){
        $param = ['add5'=>input('post.5')];
        if(!$this->V_protect->scene('add_udppro')->check($param))
            Error($this->V_protect->getError());
            
        $C_udpconf = controller('Udpconf', 'event');
        $C_udpconf->add(); 
    }
    //【方法】添加黑白名单
    private function addBlackWhiteList(){
        $param = ['add6'=>input('post.6')];
        if(!$this->V_protect->scene('add_blackwhitelist')->check($param))
            Error($this->V_protect->getError());
            
        $C_bwlist = controller('Bwlist', 'event');
        $C_bwlist->add();
    }
    //【方法】添加防护范围
    private function addHostProtect(){
        $param = ['add7'=>input('post.7')];
        if(!$this->V_protect->scene('add_hostprotect')->check($param))
            Error($this->V_protect->getError());

        $C_protecting = controller('Protecting', 'event');
        $C_protecting->add();
    }
    //【方法】添加DNS白名单
    private function addDnsWhiteList(){
        $param = ['add8'=>input('post.8')];
        if(!$this->V_protect->scene('add_dnswhitelist')->check($param))
            Error($this->V_protect->getError());
            
        $C_bwlist = controller('Dnswlist', 'event');
        $C_bwlist->add();
    }

    //【方法】更新系统防御模式
    private function updateSysProModal(){
        $param = ['update1'=>input('post.1')];
        if(!$this->V_protect->scene('update_syspromodel')->check($param))
            Error($this->V_protect->getError());

        $C_sysconf = controller('Sysconf', 'event');
        $C_sysconf->set(1);
    }
    //【方法】更新系统防御参数
    private function updateSysProParam(){
        $param = ['update2'=>input('post.2')];
        if(!$this->V_protect->scene('update_sysproparam')->check($param))
            Error($this->V_protect->getError());

        $C_sysconf = controller('Sysconf', 'event');
        $C_sysconf->set(2);
    }
    //【方法】更新主机防护参数
    private function updateHostProParam(){
        $param = ['update3'=>input('post.3')];
        if(!$this->V_protect->scene('update_hostproparam')->check($param))
            Error($this->V_protect->getError());

        $C_hostconf = controller('Hostconf', 'event');
        $C_hostconf->set();
    }
    //【方法】更新TCP端口保护
    private function updateTcpPro(){
        $param = ['update4'=>input('post.4')];
        if(!$this->V_protect->scene('update_tcppro')->check($param))
            Error($this->V_protect->getError());
            
        $C_tcpconf = controller('Tcpconf', 'event');
        $C_tcpconf->update();
    }
    //【方法】更新UDP端口保护
    private function updateUdpPro(){
        $param = ['update5'=>input('post.5')];
        if(!$this->V_protect->scene('update_udppro')->check($param))
            Error($this->V_protect->getError());
            
        $C_udpconf = controller('Udpconf', 'event');
        $C_udpconf->update(); 
    }
    //【方法】更新防护范围
    private function updateHostProtect(){
        $param = ['update7'=>input('post.7')];
        if(!$this->V_protect->scene('update_hostprotect')->check($param))
            Error($this->V_protect->getError());

        $C_protecting = controller('Protecting', 'event');
        $C_protecting->update();
    }

    //【方法】删除TCP端口保护
    private function delTcpPro(){
        $param = ['del4'=>input('post.4')];
        if(!$this->V_protect->scene('del_tcppro')->check($param))
            Error($this->V_protect->getError());
        
        $C_tcpconf = controller('Tcpconf', 'event');
        $C_tcpconf->del(); 
    }
    //【方法】删除UDP端口保护
    private function delUdpPro(){
        $param = ['del5'=>input('post.5')];
        if(!$this->V_protect->scene('del_udppro')->check($param))
            Error($this->V_protect->getError());
        
        $C_udpconf = controller('Udpconf', 'event');
        $C_udpconf->del(); 
    }
    //【方法】删除黑白名单
    private function delBlackWhiteList(){
        $param = ['del6'=>input('post.6')];
        if(!$this->V_protect->scene('del_blackwhitelist')->check($param))
            Error($this->V_protect->getError());
        
        $C_bwlist = controller('Bwlist', 'event');
        $C_bwlist->del();
    }
    //【方法】删除防护范围
    private function delHostProtect(){
        $param = ['del7'=>input('post.7')];
        if(!$this->V_protect->scene('del_hostprotect')->check($param))
            Error($this->V_protect->getError());

        $C_protecting = controller('Protecting', 'event');
        $C_protecting->del();
    }
    //【方法】删除黑白名单
    private function delDnsWhiteList(){
        $param = ['del8'=>input('post.8')];
        if(!$this->V_protect->scene('del_dnswhitelist')->check($param))
            Error($this->V_protect->getError());
        
        $C_bwlist = controller('Dnswlist', 'event');
        $C_bwlist->del();
    }

    //【方法】清空黑白名单
    private function clearBlackWhiteList(){
        $param = ['clear6'=>input('post.6')];
        if(!$this->V_protect->scene('clear_blackwhitelist')->check($param))
            Error($this->V_protect->getError());
        
        $C_bwlist = controller('Bwlist', 'event');
        $C_bwlist->clear();
    }
    //【方法】清空黑白名单
    private function clearDnsWhiteList(){
        $param = ['clear8'=>input('post.8')];
        if(!$this->V_protect->scene('clear_dnswhitelist')->check($param))
            Error($this->V_protect->getError());
        
        $C_bwlist = controller('Dnswlist', 'event');
        $C_bwlist->clear();
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