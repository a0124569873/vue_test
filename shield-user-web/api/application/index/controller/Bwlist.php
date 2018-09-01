<?php

namespace app\index\controller;
use app\index\model\UserModel;
use think\Controller;
use think\Loader;
use think\Request;
use think\Session;

class Bwlist extends Controller {

    protected $validate;
    protected $beforeActionList = [
        'checkGet'   => ['only' => 'get_bwlist,get_proxy_bwlist'],
        'checkPost'  => ['only' => 'add_bwlist,set_proxy_bwlist'],
        'checkLogin'
    ];
    
    public function _initialize(){
        $this->validate = Loader::validate('Bwlist');  //构造Bwlist验证器   
    }   

    /**
     * 获取用户域名黑白名单
     * @param  string    $domain  用户domain
     * @return string    json     返回结果
     */
    public function get_bwlist(){
        if (!$this->validate->scene('get_bwlist')->check(input())) 
            return Finalfail($this->validate->getError());
        
        $config = ['domain' => input('get.domain')];
        $result_arr = Sendpost(API_SERVER_URL . "/hd_node/index.php?action=get_domain_list", json_encode($config),"json");
        if ($result_arr['success']) {
            return Finalsuccess(["data"=>$result_arr['data']]);
        }
        return Finalfail('20002', 'API msg: '.$result_arr['errorMessage']);

    }

    /**
     * 设置用户域名黑白名单
     * @param  string    $domain  用户domain
     * @param  string    $blackurl 网址黑名单
     * @param  string    $whiteurl  网址白名单
     * @param  string    $blackip  ip黑名单
     * @param  string    $whiteip  ip白名单
     * @return string    json     返回结果
     */
    public function add_bwlist(){
        if (!$this->validate->scene('add_bwlist')->check(input())) 
            return Finalfail($this->validate->getError());

        $domain = input('post.domain');
        $blackurl = is_null(input('post.blackurl')) ? "" : input('post.blackurl');
        $whiteurl = is_null(input('post.whiteurl')) ? "" : input('post.whiteurl');
        $blackip  = is_null(input('post.blackip'))  ? "" : input('post.blackip');
        $whiteip  = is_null(input('post.whiteip'))  ? "" : input('post.whiteip');

        $config = [
            "domain" => $domain,
            "type" => "http",
            "data" => [
                "black_url" => $blackurl,
                "white_url" => $whiteurl,
                "black_ip" => $blackip,
                "white_ip" => $whiteip
            ]
        ];

        $result_arr = Sendpost(API_SERVER_URL . "/hd_node/index.php?action=set_domain_list", json_encode($config),"json");        

        if (!$result_arr['success']) {
            return Finalfail('20002', 'API msg: '.$result_arr['errorMessage']);
        }
        return Finalsuccess();

    }

    /**
     * 获取缓存白名单
     * @param  string    $domain  用户domain
     * @param  string    $port    端口
     * @return string    json     返回结果
     */
    public function get_proxy_bwlist(){
        if (!$this->validate->scene('get_proxy_bwlist')->check(input())) 
            return Finalfail($this->validate->getError());

        $domain = input('get.domain');
        $port = input('get.port');

        $result_arr = Sendpost(API_SERVER_URL . "/hd_node/index.php?action=get_domain_list", json_encode(['domain' => $domain, 'port' => $port]),"json");
        if (!$result_arr['success']) {
            return Finalfail('20002', 'API msg: '.$result_arr['errorMessage']);
        }   
        return Finalsuccess(["data"=>$result_arr['data']]);
            
    }

    /**
     * 设置缓存白名单
     * @param  string    $domain  用户domain
     * @param  string    $port    端口
     * @return string    json     返回结果
     */
    public function set_proxy_bwlist(){
        if (!$this->validate->scene('set_proxy_bwlist')->check(input())) 
            return Finalfail($this->validate->getError());
        
        $domain = input('post.domain');
        $port = input('post.port');
        $blackip  = is_null(input('post.blackip'))  ? "" : input('post.blackip');
        $whiteip  = is_null(input('post.whiteip'))  ? "" : input('post.whiteip');

        $config = [
            "domain" => $domain,
            "type" => "port",
            "data" => [
                "port" => $port,
                "content" => [
                    "black" => $blackip,
                    "white" => $whiteip
                ]
            ]
        ];

        $result_arr = Sendpost(API_SERVER_URL . "/hd_node/index.php?action=set_domain_list", json_encode($config),"json");

        if (!$result_arr['success']) {
            return Finalfail('20002', 'API msg: '.$result_arr['errorMessage']);
        }   
        return Finalsuccess();
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
