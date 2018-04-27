<?php

namespace app\index\controller;
use app\index\model\UserInfo;
use app\index\model\MaskPoolModel;
use app\index\model\SysConf;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Uconnect extends Controller{

    protected $m_connect;
    protected $v_connect;
    protected $m_sys_conf;

    public function _initialize(){
        $this->m_connect = new UserInfo;
        $this->m_sys_conf = new SysConf;
        $this->v_connect = Loader::validate('Uconnect'); 

    }

    protected $beforeActionList = [
        'check_get'   => ['only' => 'get,getpip'],
        'check_post'  => ['only' => 'add,update,del'],
        'check_login' 
    ];

    //【接口】获取用户接入
    public function get(){
        if(!$this->v_connect->scene('get')->check(input()))
            return Finalfail($this->v_connect->getError());

        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row = is_null(input('get.row')) ? 10 : input('get.row');

        $count = $this->m_connect->countAllUserInfo();
        $data = $count == 0 ?  [] : $this->m_connect->selectUserInfo($page, $row);

        foreach ($data as $key => $value) {
            unset($data[$key]["password"]);
        }

        $result = ["connect" => $data, "count" => $count];
        return Finalsuccess($result);
    }

    //【接口】获取所有私有ip
    public function getpip(){
        $ips = [];
        $data = $this->m_connect->selectAllUserInfoPip();
        if(empty($data))
            return Finalsuccess(["ips" => $ips]);

        $ips = array_map(function($ip){
            return $ip["p_ip"];
        },$data);
        return Finalsuccess(["ips" => $ips]);
    }

    //【接口】添加用户接入
    public function add(){
        if(!$this->v_connect->scene('add')->check(input()))
            return Finalfail($this->v_connect->getError());

        if (strlen(input('post.username')) > 20 ){
            Error("11028");
        }

        $result = $this->m_connect->selectUserInfoByNameIp(input('post.username'), input('post.p_ip'));
        if(count($result)>0)
            return Finalfail("10007");

        $conf_arr = [
            'username' => input('post.username'),
            // 'password' => input('post.password'),
            'vpn_server' => input('post.vpn_server'),
            'p_ip' => input('post.p_ip'),
            'interval' => input('post.interval')
        ];

        if (in_array(strtolower($conf_arr["username"]), ["ca","ta","server","index","serial","dh1024","dh2048","dh4096"])) {
            Error("11025","用户名不能为CA,TA,SERVER,INDEX,SERIAL,DH1024,DH2048,DH4096!");
        }

        if (empty(input('post.uid')) || empty(input('post.hide_ip'))) {
            Error("11027","线路id或伪装原型ip为空");
        }

        if( (empty(input('post.uid')) && input('post.uid') != 0) || empty(input('post.uid')) || empty(input('post.hide_ip')) ){
            $conf_arr['uid'] = '';
            $conf_arr['hide_ip'] = '';
        }else{
            $conf_arr['uid'] = input('post.uid');
            $conf_arr['hide_ip'] = input('post.hide_ip');
        }

        if (empty($conf_arr['uid']) || $conf_arr['uid'] == "RANDOM"){

        }else{
           $stat = $this->checkUid($conf_arr['uid']); // 检查uid是否有效
            if(!$stat){
                return Finalfail("15010");
            } 
        }


        // if ($conf_arr["uid"] == "RANDOM"){
        //     $conf_arr["uid"] = "0";
        // }

        if($conf_arr['p_ip'] != ''){
            $stat = $this->checkPIp($conf_arr['p_ip'],$conf_arr["vpn_server"]); // 检查hide_ip是否有效

            if(!$stat){
                return Finalfail("11026");
            }
        }


        if($conf_arr['hide_ip'] != ''){
            $stat = $this->checkHideIp($conf_arr['hide_ip']); // 检查hide_ip是否有效
            if(!$stat){
                return Finalfail("15011");
            }
        }
        if($conf_arr['vpn_server'] != ''){
            $stat = $this->checkVpnServerIp($conf_arr['vpn_server']); // 检查vpn_server_ip是否有效
            if(!$stat){
                return Finalfail("15011");
            }
        }


        $result = $this->m_connect->addUserInfo($conf_arr);
        if($result <= 0)
            return Finalfail("20001");

        $this->reloadUserInfo();

        #update Gserver
        $ress = rewriteGConf();
        if ($ress != "success") {
            #if failed delete record reload vpn_server
            $result = $this->m_connect->selectUserInfoByNameIp(input('post.username'), input('post.p_ip'));
            if (count($result) > 0) {
                $this->m_connect->deleteUserInfoById([$result[0]["id"]]);
            }
            // delcert($conf_arr['vpn_server'],[$conf_arr['username']]);
            $this->reloadUserInfo();
            return Finalfail("11024",$ress);
        }

        #update vpnserver 
        if(!empty($conf_arr['vpn_server'])){
            $ress = gencert($conf_arr['vpn_server'],$conf_arr['username'],$conf_arr['p_ip']);
            if ($ress != "success") {
                $result = $this->m_connect->selectUserInfoByNameIp(input('post.username'), input('post.p_ip'));
                if (count($result) > 0) {
                    $this->m_connect->deleteUserInfoById([$result[0]["id"]]);
                }
                $this->reloadUserInfo();
                rewriteGConf();
                return Finalfail("11024",$ress);
            }
        }

        return Finalsuccess();
    }

    //【接口】修改用户接入
    public function update(){
        if(!$this->v_connect->scene('update')->check(input()))
            return Finalfail($this->v_connect->getError());

        $tmp_res = $this->m_connect->selectUser_infoByIds([input('post.id')]);

        if(count($tmp_res)!=1)
            return Finalfail("10009","record not exists!");
        
        $conf_arr = [
            'id'       => input('post.id'),
            'interval'       => input('post.interval')
        ];


        // if (empty(input('post.uid'))) {
        //     $conf_arr['uid'] = $tmp_res[0]["uid"];
        // }else{
            // $conf_arr['uid'] = input('post.uid');
        // }

        // if (empty(input('post.hide_ip'))) {
        //     $conf_arr['hide_ip'] = $tmp_res[0]["hide_ip"];
        // }else{
            // $conf_arr['hide_ip'] = input('post.hide_ip');
        // }

        if (empty(input('post.uid')) || empty(input('post.hide_ip'))) {
            Error("11027","线路id或伪装原型ip为空");
        }

        if (empty(input('post.hide_ip')) || empty(input('post.uid'))) {
            $conf_arr['uid'] = '';
            $conf_arr['hide_ip'] = '';
        }else{
            $conf_arr['uid'] = input('post.uid');
            $conf_arr['hide_ip'] = input('post.hide_ip');
        }

        if (empty($conf_arr['uid']) || $conf_arr['uid'] == "RANDOM" || $conf_arr['uid'] == $tmp_res[0]["uid"]){

        }else{
           $stat = $this->checkUid($conf_arr['uid']); // 检查uid是否有效
            if(!$stat){
                return Finalfail("15010");
            } 
        }


        if(empty(input('post.hide_ip')) || $conf_arr['hide_ip'] == $tmp_res[0]["hide_ip"]){

        }else{
            $stat = $this->checkHideIp(input('post.hide_ip')); // 检查hide_ip是否有效
            if(!$stat){
                return Finalfail("15011");
            }
        }


        $result = $this->m_connect->updateUserInfoById($conf_arr);
        if($result < 0)
            return Finalfail("20001");

        $this->reloadUserInfo();

        #update Gserver
        $ress = rewriteGConf();
        if ($ress != "success") {

            if (count($tmp_res) > 0)
                $result = $this->m_connect->updateUserInfoById($tmp_res[0]);
            $this->reloadUserInfo();
            return Finalfail("11024",$ress);
        }

        return Finalsuccess();
    }

    //【接口】删除用户接入
    public function del(){
        $clear = input('get.clear');
        if($clear === "true"){
            ExcuteExec("uroot fpcmd user_info -f");//清空
            $this->m_connect->deleteUserInfoAll();
            return Finalsuccess();
        }

        if(!$this->v_connect->scene('del')->check(input()))
            return Finalfail($this->v_connect->getError());

        $id_arr = explode(",", input('post.ids'));

        $tmp_res = $this->m_connect->selectUser_infoByIds($id_arr);

        $this->m_connect->deleteUserInfoById($id_arr);

        $this->reloadUserInfo();

        #update all vpn server 

        $res_vpn_server_tmp = array();
        
        foreach ($tmp_res as $key => $value) {
            $res_vpn_server_tmp[$value["vpn_server"]][] = $value["username"];
        }


        #update Gserver
        $ress = rewriteGConf();
        if ($ress != "success") {
            
            foreach ($tmp_res as $key => $value) {
                unset($value["id"]);
                $result = $this->m_connect->addUserInfo($value);
                // gencert($value["vpn_server"],$value["username"],$value["p_ip"]);
            }
            $this->reloadUserInfo();
            return Finalfail("11024",$ress);
        }

        #update vpn_server
        $ress = array();
        $ress["flag"] = true;
        $ress["res"] = "";

        foreach ($res_vpn_server_tmp as $key => $value) {
            $resss = delcert($key,$res_vpn_server_tmp[$key]);
            if ($resss != "success") {
                $ress["flag"] = false;
                $ress["res"].=$resss;
                $ress["ips"][] = $key;
            }
        }

        if (!$ress["flag"]) {
            foreach ($tmp_res as $key => $value) {
                if(in_array($value["vpn_server"], $ress["ips"])){
                    unset($value["id"]);
                    $result = $this->m_connect->addUserInfo($value);
                }
            }
            rewriteGConf();
            $this->reloadUserInfo();
            return Finalfail("11024",$ress["res"]);
        }

        return Finalsuccess();
    }


    public function getfile(){

            $name = input('get.filename');
            $path = "/hard_disk/openvpn_cert/".$name;
            $this->Download($path,$name);

        }

    private function Download($path,$name = null){
        if($name === null)
            $name=$path;
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Expires: 0");
        header("Pragma: public");
        header("Content-Type: application/octet-stream");
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".filesize($path));
        header("Content-Disposition: attachment; filename=$name");
        readfile($path);
    }

    //【方法】清空所有，并读取完整用户接入信息配置下去
    private function reloadUserInfo(){
        ExcuteExec("uroot fpcmd user_info -f");//清空
        $confs = $this->m_connect->selectCompleteUserInfo();

        if (count($confs) == 0)
            return;

        $a = 0;
        while (true) {

            foreach($confs as $key => $conf){

                if ($conf["uid"] == "RANDOM") {
                    $conf["uid"] = "0";
                }

                // $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",".$conf['hide_ip'];


                if (empty($conf["interval"])) {
                    $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",0,0,".$conf['hide_ip'];
                }else{
                    $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",-2147483648,".$conf['interval'].",".$conf['hide_ip'];
                }

                $a = $a +1;
                unset($confs[$key]);
                if ($a == 5 || count($confs) == 0)
                    break;
            }

            $conf_param = json_encode($conf_arr);
            $conf_param_p = str_replace('"', '\\"', $conf_param);
            $order = "uroot 'fpcmd user_info -i \"".$conf_param_p."\"'";
            ExcuteExec($order);
            if (count($confs) == 0)
                break;
        }
    }

    public function getAllVpnServerIp(){
        $res = $this ->m_sys_conf-> getAllVpnServerIp();
        return Finalsuccess(["ips" => $res]);
    }

    private function checkVpnServerIp($ip){
        $res = $this ->m_sys_conf-> getAllVpnServerIp();

        $res_ip = array();
        foreach ($res as $key => $value) {
            $res_ip[] = explode("|", $value)[0];
        }
        if (!in_array($ip, $res_ip)){
            return false;
        }
        return true;
    }

    private function checkPIp($p_ip,$server_ip){

        $res = $this ->m_sys_conf-> getVpnServerConfByIp($server_ip);
        if (empty($res)) {
            return false;
        }

        $server_p_ip = explode("|",$res[0]["conf_value"])[4];
        $server_p_ip_arr = explode("/",$server_p_ip);

        if (!($this->netMatch($p_ip,$server_p_ip_arr[0],$server_p_ip_arr[1]))) {
            return false;
        }
        
        return true;
    }

    private function netMatch($client_ip, $server_ip, $mask){
        $mask1 = 32 - $mask;
        return ((ip2long($client_ip) >> $mask1) == (ip2long($server_ip) >> $mask1));
    }



    //【方法】检查redis是否存在uid
    private function checkUid($uid){
        $redis = new \Redis();
        $redis->pconnect('127.0.0.1');
        $stats = $redis->hGet('link_stat', $uid);
        return $stats;
    }



    public function getalluid(){
        $redis = new \Redis();
        $redis->pconnect('127.0.0.1');
        $res = $redis->hgetall('link_stat');
        if(array_key_exists("link_count", $res)){
            unset($res["link_count"]);
        }
        $uids1 = array_keys($res);
        array_push($uids1,"RANDOM");
        $uids = array();
        foreach ($uids1 as $key => $value) {
            array_push($uids, strval($value));
        }

        return Finalsuccess(["uids" => $uids]);
    }

    //【方法】检查伪装原型池是否存在ip
    private function checkHideIp($ip_str){

        $pool = new MaskPoolModel;

        $count = $pool->countMaskPool();

        $data = $pool->selectMaskPool(1,$count);

        $ip_arr = array_map(function($ip){
                return $ip['ip'];
            },$data);

        $ip_str_arr = explode("|", $ip_str);
        foreach ($ip_str_arr as $key => $value) {
            if (!in_array($value, $ip_arr)) {
                Error("11025","error ip range not in maskpool!");
            }
        }

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

    public function _empty(){
        $this->redirect('/errorpage');
    }
}
