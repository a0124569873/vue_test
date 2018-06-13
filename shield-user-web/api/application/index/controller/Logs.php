<?php
namespace app\index\controller;
use app\index\model\ServerModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

class Logs extends Controller {

    private $validate; 
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'attack'],
        'checkLogin'
    ];

    public function _initialize(){
        $this->validate = Loader::validate("Logs");
    }

    /**
     * 获取牵引日志
     * @param   string    $page  页码
     * @param   string    $row   每页行数
     * @return array     $list  返回结果数组
     */
    public function attack(){
        $uid = session("user_auth.uid");

        if (!$this->validate->scene('attack')->check(input())) 
            return Finalfail($this->validate->getError());

        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row = is_null(input('get.row')) ? 10 : input('get.row');

        //===============debug data ================ 
        $logs = [
            [
                'server_ip'=>'192.168.100.101',
                'in_pkt'=>'500',
                'flow_in'=>'100',
                'thr_in'=>'35',
                'attack_time'=>'2017-10-22 10:46:22'
            ],
            [
                'server_ip'=>'192.168.100.102',
                'in_pkt'=>'453',
                'flow_in'=>'4242',
                'thr_in'=>'4',
                'attack_time'=>'2017-10-22 10:47:01'
            ],
            [
                'server_ip'=>'192.168.100.104',
                'in_pkt'=>'575',
                'flow_in'=>'14537',
                'thr_in'=>'73',
                'attack_time'=>'2017-10-22 10:48:28'
            ]
            
        ];

        $pages = 3;
        
        return Finalsuccess(["logs"=>$logs, "total"=>$pages]);
    }

    protected function checkLogin(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }
    protected function checkPost(){
        if(!request()->isPost())
            Error("21001","need post");
    }
    protected function checkGet(){
        if(!request()->isGet())
            Error("21002","need get");
    }
    public function _empty(){
        $this->redirect('/errorpage');
    }
}