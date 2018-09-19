<?php

namespace app\index\controller;
use app\index\model\MaskPoolModel;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Maskpool extends Controller{

    protected $m_pool;
    protected $v_pool;

    public function _initialize(){
        $this->m_pool = new MaskPoolModel;  
        $this->v_pool = Loader::validate('Maskpool'); 
    }

    protected $beforeActionList = [
        'check_get'   => ['only' => 'get'],
        'check_post'  => ['only' => 'add,del'],
        'check_login' 
    ];

    //【接口】获取伪装原型IP
    public function get(){
        if(!$this->v_pool->scene('get')->check(input()))
            return Finalfail($this->v_pool->getError());

        $page = empty(input('get.page')) ? 1 : input('get.page');
        $row  = empty(input('get.row')) ? 10 : input('get.row');
        $ip_range = empty(input('get.ip_range')) ? '' : input('get.ip_range');

        if($ip_range != ''){
            $data = $this->m_pool->selectMaskPoolByRange($ip_range);
            $result['pool'] = array_map(function($ip){
                return $ip['ip'];
            },$data);
        }else{
            $count = $this->m_pool->countMaskPool();
            $data = $count == 0 ?  [] : $this->m_pool->selectMaskPool($page, $row);
            $ip_arr = array_map(function($ip){
                return $ip['ip'];
            },$data);

            $result = ["pool" => $ip_arr, "count" => $count];
        }

        return Finalsuccess($result);
    }

    //【接口】获取伪装原型IP
    public function getall(){
        if(!$this->v_pool->scene('get')->check(input()))
            return Finalfail($this->v_pool->getError());

        
        $count = $this->m_pool->countMaskPool();
        $data = $count == 0 ?  [] : $this->m_pool->selectMaskPool(1, $count);
        $ip_arr = array_map(function($ip){
            return $ip['ip'];
        },$data);

        $result = ["pool" => $ip_arr];

        return Finalsuccess($result);
    }

    //【接口】添加伪装原型IP
    public function add(){
        $conf_arr = RawJsonToArr();
        if(!array_key_exists('pool',$conf_arr))
            Error("22001");
        
        $ip_arr = $conf_arr['pool'];
        if(!$this->v_pool->scene('add')->check(['pool'=>$ip_arr]))
            return Finalfail($this->v_pool->getError());

        $result = $this->m_pool->selectWholeMaskPoolIp();
        $had_ip = empty($result) ? [] : array_map(function ($item){ //获取现有ip
            return $item['ip'];
        },$result);

        $save_conf = array();
        foreach ($ip_arr as $key => $value) {
            if (in_array($value, $had_ip)){
                Error("10007","");
            }
            $save_conf[] = ["ip" => $value];
        }

        $result = $this->m_pool->insertAllMaskPool($save_conf);
        if($result < 0)
            return Finalfail("20001");

        return Finalsuccess();
    }

    //【接口】删除伪装原型IP
    public function del(){
        $clear = input('get.clear');
        if($clear === "true"){
            //To do...
            $this->m_pool->deleteMaskPoolAll();
            return Finalsuccess();
        }

        if(!$this->v_pool->scene('del')->check(input()))
            return Finalfail($this->v_pool->getError());

        $ip_arr = explode(",", input('post.ips'));

        $this->m_pool->deleteMaskPoolByIp($ip_arr);

        return Finalsuccess();
    }

    //import conf
    public function upload(){

        if(!request()->isPost())
            return Error("21001","need post method");

        $read_str = fread(fopen($_FILES["file"]["tmp_name"], "r"), 1024*1024);
        $read_str = str_replace("\r", "", $read_str);
        $read_arr = explode("\n", $read_str);


        $result = $this->m_pool->selectWholeMaskPoolIp();
        $had_ip = empty($result) ? [] : array_map(function ($item){ //获取现有ip
            return $item['ip'];
        },$result);

        $conf_list = array();
        foreach ($read_arr as $key => $value) {
            if (in_array($value, $had_ip)) {
                continue;
            }
            if (strstr($value, "/") && !CheckIpMask($value)) {
                continue;
            }
            if (!strstr($value, "/") && !filter_var($value, FILTER_VALIDATE_IP)) {
                continue;
            }

            $conf_list[] = ["ip" => $value];
        }

        $result = $this->m_pool->insertAllMaskPool($conf_list);
        if($result < 0)
            return Finalfail("20001");

        return Finalsuccess();

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
