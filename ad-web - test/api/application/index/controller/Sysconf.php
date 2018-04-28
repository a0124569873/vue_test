<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 系统配置 主控制器
 */
class Sysconf extends Controller {

    protected $V_sysconf;// 校验器-系统配置
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get'],
        'checkPost'  => ['only' => 'update'],
        'checkLogin',
        'checkValid'
    ];
    
    public function _initialize(){
        $this->V_sysconf = Loader::validate('Sysconf'); 
    }

    //【接口】获取
    public function get(){
        if(!$this->V_sysconf->scene('get')->check(input()))
            return Finalfail($this->V_sysconf->getError());

        $result = [];
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 网络地址管理
                    $result['1'] = $this->getNetWork();
                    break;
                case "2":
                    // 网卡汇聚
                    $result['2'] = $this->getEthgrp();
                    break;
                case "3":
                    // 管理口与广播ip
                    $result['3'] = $this->getMgtip();
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
        if(!$this->V_sysconf->scene('update')->check(input()))
            return Finalfail($this->V_sysconf->getError());

        $result = NULL;
        $type_arr = input('get.t');
        foreach (explode('|', $type_arr) as $type) {
            switch($type){
                case "1":
                    // 网络地址管理
                    $this->updateNetWork();
                    break;
                case "2":
                    // 网卡汇聚
                    $this->updateEthgrp();
                    break;
                case "3":
                    // 管理口与广播ip
                    $this->updateMgtip();
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【方法】获取网络地址管理
    private function getNetWork(){
        $result = NULL;
        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->get(3); 
        return $result;
    }
    //【方法】获取网络地址管理
    private function getEthgrp(){
        $result = NULL;
        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->getEthgrp();
        return $result;
    }
    //【方法】获取管理口与广播ip
    private function getMgtip(){
        $result = NULL;
        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->getMgtip();
        return $result;
    }

    //【方法】更新网络地址管理
    private function updateNetWork(){
        $param = ['update1'=>input('post.1')];
        if(!$this->V_sysconf->scene('update_network')->check($param))
            Error($this->V_sysconf->getError());

        $C_sysconf = controller('Sysconf', 'event');
        $result = $C_sysconf->setNetWork();
    }
    //【方法】更新网络地址管理
    private function updateEthgrp(){
        $C_sysconf = controller('Sysconf', 'event');
        $C_sysconf->setEthgrp();
    }
    //【方法】更新管理口与广播ip
    private function updateMgtip(){
        $type = input('get.type');
        $C_sysconf = controller('Sysconf', 'event');
        $C_sysconf->setMgtip($type);
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