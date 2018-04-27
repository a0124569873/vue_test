<?php
namespace app\index\controller;
use app\index\model\SysConf;
use think\Controller;
use think\Loader;
use think\Request;

class Setting extends Controller {

    protected $m_sys_conf;

    protected $validate;


    public function _initialize(){
        $this->m_sys_conf  = new SysConf;
        $this->validate = Loader::validate('Setting');

    }

    public function _empty(){
        $this->redirect('/errorpage');
    }

    protected $beforeActionList = [
        'check_login'
    ];

    //【接口】获取vpn配置
    public function get() {
        if(!request()->isGet())
            return Finalfail("21002","need get method");

        if(!$this->validate->scene('get')->check(input()))
            return Finalfail($this->validate->getError());

        $conf_type = input('get.type');

        foreach (explode('|', $conf_type) as $type) {
            switch($type) {
                case '1':

                    $res = $this->m_sys_conf->getGServerConf();
                    $res_arr = explode("|", $res);
                    $res_arr[3] = "";

                    $result['1'] = implode("|", $res_arr);

                    break;
                case '2':

                    $result['2'] = $this->selectVpnServer();
                    break;

                default:
                    $result = [];
                    break;
            }
        }
        return Finalsuccess($result);
    }

    //【接口】配置vpn
    public function set() {
        if(!request()->isPost())
            return Finalfail("21001","need post method");

        if(!$this->validate->scene('set')->check(input()))
            return Finalfail($this->validate->getError());

        $conf_type = input('get.type');
        $update = input('get.update');

        $result = NULL;

        foreach(explode('|', $conf_type) as $type) {
            switch($type) {
                case '1':
                //
                    $this->addGServerConf();
                    break;
                case '2':
                //
                    $this->add_vpn_server($update);
                    break;

            }
        }
        return Finalsuccess($result);
    }
    //【接口】删除vpn配置
    public function del() {
        if(!$this->validate->scene('del')->check(input()))
            return Finalfail($this->validate->getError());

        $conf_type = input('get.type');
        
        foreach(explode('|', $conf_type) as $type) {
            switch ($type) {
                case '2':

                    $this-> del_vpn_server();
                    break;
                default:
                    //ToDo ......
                    break;
            }
        }
        return Finalsuccess();
    }

    private function selectVpnServer(){
        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row  = is_null(input('get.row')) ? 10 : input('get.row');

        $count = $this->m_sys_conf->countVpnServer();
        $data = $count == 0 ?  [] : $this->m_sys_conf->selectVpnServer($page, $row);
        $res_arr = [];
        foreach ($data as $key => $value) {
            $vpnserver_arr = explode("|", $value["conf_value"]);
            unset($vpnserver_arr[3]);
        	$res_arr[] = $value["id"]."|".implode("|", $vpnserver_arr);
        }
        $res["2"] = $res_arr;
        $res["count"] = $count;
        return $res;
    }

    private function addGServerConf(){
        $conf_arr = RawJsonToArr();
        if(!array_key_exists('1',$conf_arr))
            Error("22001");
        $conf_str = $conf_arr['1'];

        $conf_str_arr = explode("|", $conf_str);

        if (intval($conf_str_arr[1]) < 0 || intval($conf_str_arr[1]) > 65535) {
            Error("11009");
        }

        if (empty($conf_str_arr[3])){

            $res = $this->m_sys_conf->getGServerConf();
            $res_arr = explode("|", $res);
            $conf_str_arr[3] = $res_arr[3];
        }

        $conf_str = implode("|", $conf_str_arr);

        // if(!$this->v_vpnmap->scene('add')->check(['vpn' => $conf_arr]))
        //     return Finalfail($this->v_vpnmap->getError());

        $conf_arr1[] = ["conf_type" => "1", "conf_value" => $conf_str];
        $this ->m_sys_conf-> addGServerConf($conf_arr1);

    }

    private function del_vpn_server(){

        if (empty(file_get_contents("php://input"))){
            $this ->m_sys_conf-> delAllVpnServerConf();
        }else{
            $conf_arr = RawJsonToArr();
            if(array_key_exists('2',$conf_arr)){
                $conf_str = $conf_arr['2'];
                $id_arr = explode(",", $conf_str);
                $this ->m_sys_conf-> delVpnServerConf($id_arr);
            }
        }

    }

    

    private function add_vpn_server($update = false){

        $conf_arr = RawJsonToArr();

        if(!array_key_exists('2',$conf_arr))
                Error("22001");
        $conf_arr_tmp = $conf_arr['2'];

        if(!$this->validate->scene('check_add_vpn')->check(['type1' => $conf_arr_tmp]))
            return Finalfail($this->v_vpnmap->getError());

        if ($update) {

            $conf_arr = [];
            $idippasss = [];
            foreach ($conf_arr_tmp as $key => $value) {
                // $conf_arr[] = ["conf_type"=> "2", "conf_value"=> $value];
                $idippasss[] = ["id" => explode("|", $value)[0],"ip" => explode("|", $value)[1],"pass" => explode("|", $value)[4]];
            }

            foreach ($idippasss as $key => $value) {
                $result1 = $this->m_sys_conf->getVpnServerConfById($value["id"]);
                // var_dump($result);
                if (empty($result1)){
                    Error("10007","更新vpnserver失败，记录不存在");
                }

                $result = $this->m_sys_conf->getVpnServerConfByIpId($value["ip"],$value["id"]);
                // print_r($result);
                if (!empty($result)){
                    Error("10008","更新vpnserver失败，ip已存在");
                }

                if (empty($value["pass"])) {
                    $conf_arr_tmp_arr = explode("|", $conf_arr_tmp[$key]);
                    $conf_arr_tmp_arr[4] = explode("|", $result1[0]["conf_value"])[3];
                    $conf_arr_tmp[$key] = implode("|", $conf_arr_tmp_arr);
                }

            }

            foreach ($conf_arr_tmp as $key => $value) {
                $conf_arr = explode("|", $value);
                unset($conf_arr[0]);
                $conf_val = implode("|", $conf_arr);
                $result = $this->m_sys_conf->updateVpnServerConf(explode("|", $value)[0],$conf_val);
            }

        }else{

            $conf_arr = [];
            $ips = [];


            foreach ($conf_arr_tmp as $key => $value) {

                $conf_arr[] = ["conf_type"=> "2", "conf_value"=> $value];
                $ips[] = explode("|", $value)[0];
            }


            foreach ($ips as $key => $value) {
                $result = $this->m_sys_conf->getVpnServerConfByIp($value);
                if (!empty($result)){
                    Error("10007","重复添加");
                }
            }

            $result = $this->m_sys_conf->addVpnServerConf($conf_arr);
            if($result <= 0)
                return Finalfail("20001");
        }
    }

    






    //【方法】检查配置的私有ip是否有效
    private function checkPip($ip){
        $userinfo = new UserInfo;
        $data = $userinfo->selectPipByIp($ip);
        return empty($data) ? false : true;
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

    // public function _empty(){
    //     $this->redirect('/errorpage');
    // }

}