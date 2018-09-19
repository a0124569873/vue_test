<?php

namespace app\index\controller;
use app\index\model\SiteModel;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Speed extends Controller {

    protected $site;
    protected $validate;
    protected $beforeActionList = [
        'checkPost',
        'checkLogin'
    ];

    public function _initialize(){
        $this->site = new SiteModel;  //构造用户Model
        $this->validate = Loader::validate('Speed');  //构造用户验证器        
    }

    /**
     * 获取或设置域名加速状态
     * @param   string    $oper    操作 set 或 get
     * @param   string    $domain  用户域名
     * @param   string    $status   加速状态
     * @return string    json 返回结果
     */
    public function speed_status(){
        if(!$this->validate->scene('speed_status')->check(input()))
            return Finalfail($this->validate->getError());

        $oper = input('post.oper');  
        $domain = input('post.domain');
        
        if( $oper == 'get'){
            $result = $this->site->get_speed_status($domain);
            if(count($result) < 1)
                return Finalfail('10019');
            
            if($result[0]['speed_status'] > 0){
                $result_arr = Sendpost(API_SERVER_URL."/hd_node/index.php?action=get_cache_list",json_encode(['domain' => $domain]),"json");
                $count = 0;
                if(array_key_exists('static_resource',$result_arr['data']) && $result_arr['data']['static_resource'] !== ''){
                    $count ++;
                }
                if(array_key_exists('static_html',$result_arr['data']) && $result_arr['data']['static_html'] !== ''){
                    $count ++;
                }
                if(array_key_exists('directory',$result_arr['data']) && $result_arr['data']['directory'] !== ''){
                    $count ++;
                }
                if(array_key_exists('index',$result_arr['data']) && $result_arr['data']['index'] !== ''){
                    $count ++;
                }
                $status = [
                    'speed_status' => $result[0]['speed_status'],
                    'count' =>  $count
                ];
            }else{
                $status = [
                    'speed_status' => $result[0]['speed_status'],
                    'count' =>  0
                ];
            }
            return Finalsuccess($status);
        }

        if($oper == 'set'){
            if(!$this->validate->scene('set_speed_status')->check(input()))
                return Finalfail($this->validate->getError());
            
            $status = input('post.status');
            $result = $this->site->set_speed_status($domain,$status);
            if(!$result >= 0){
                return Finalfail('20001','mysql error'); 
            }
            return Finalsuccess();
        }
    }

    /**
     * 获取域名加速配置信息
     * @param   string    $domain  用户域名
     * @return string    json 返回结果
     */
    public function get_speed_conf(){
        if(!$this->validate->scene('get_speed_conf')->check(input()))
            return Finalfail($this->validate->getError());

        $domain = input('post.domain');
        $result_arr = Sendpost(API_SERVER_URL."/hd_node/index.php?action=get_cache_list",json_encode(['domain' => $domain]),"json");
        
        if(!$result_arr['success'])
            return Finalfail('20002','API msg:'.$result_arr['errorMessage']);
        
        if(array_key_exists('static_resource',$result_arr['data']))
            $result_arr['data']['static_resource'] = String2time($result_arr['data']['static_resource']);

        if(array_key_exists('static_html',$result_arr['data']))
            $result_arr['data']['static_html'] = String2time($result_arr['data']['static_html']);

        if(array_key_exists('directory',$result_arr['data']))
            $result_arr['data']['directory'] = String2time($result_arr['data']['directory']);

        if(array_key_exists('index',$result_arr['data']))
            $result_arr['data']['index'] = String2time($result_arr['data']['index']);

        if(array_key_exists('white_list',$result_arr['data'])){
            foreach($result_arr['data']['white_list'] as &$w){
                $w['time'] = String2time($w['time']);
            }
        }
        return Finalsuccess(["data"=>$result_arr['data']]);
        
    }

    /**
     * 设置域名加速配置信息
     * @param   string    $domain  用户域名
     * @param   string    $oper    操作
     * @return string    json 返回结果
     */
    public function set_speed_conf(){
        if(!$this->validate->scene('set_speed_conf')->check(input()))
            return Finalfail($this->validate->getError());
    
        $oper = input('post.oper');
        $domain = input('post.domain');

        if($oper == 'quick'){
            $static_resource = is_null(input('post.static_resource')) ? "" : input('post.static_resource');
            $static_html = is_null(input('post.static_html')) ? "" : input('post.static_html');
            $directory  = is_null(input('post.directory'))  ? "" : input('post.directory');
            $index  = is_null(input('post.index'))  ? "" : input('post.index');
            $config = [
                'domain'  =>  $domain,
                'data'    =>  [
                    'static_resource'=>    Time2string($static_resource),
                    'static_html'    =>    Time2string($static_html),
                    'directory'      =>    Time2string($directory),
                    'index'          =>    Time2string($index)
                ]
            ];

        }elseif($oper == 'w_list'){
            if(!$this->validate->scene('w_list')->check(input()))
                return Finalfail($this->validate->getError());

            eval('$white_list ='.input('post.white_list').';');
            $list_str = '';
            foreach($white_list as $w){
                $list_str .= '"'.$w[0].'":"'.Time2string($w[1]).'",';
            }
            $list_str = substr($list_str,0,strlen($list_str)-1);
            $config = '{"domain":"'.$domain.'","data":{"white_list":{'.$list_str.'}}}';

        }elseif($oper == 'b_list'){
            if(!$this->validate->scene('b_list')->check(input()))
                return Finalfail($this->validate->getError());

            input('post.black_list') == '' ? $black_list = [] : $black_list = explode(',',input('post.black_list'));
            $config = [
                'domain'  =>  $domain,
                'data'    =>  [
                    'black_list'    =>    $black_list          
                ]
            ];

        }

        if($oper == 'w_list'){
            $result_arr = Sendpost(API_SERVER_URL."/hd_node/index.php?action=set_cache",$config,"json"); //设置白名单不再需要json_encode
        }else{
            $result_arr = Sendpost(API_SERVER_URL."/hd_node/index.php?action=set_cache",json_encode($config),"json");            
        }

        if(!$result_arr['success']){
            return Finalfail('20002','API msg:'.$result_arr['errorMessage']);
        }
        return Finalsuccess(["data"=>$result_arr['data']]);
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