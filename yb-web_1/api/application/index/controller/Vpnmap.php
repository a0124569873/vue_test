<?php

namespace app\index\controller;
use app\index\model\VpnInfo;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Vpnmap extends Controller{

    protected $m_vpnmap;
    protected $v_vpnmap;

    public function _initialize(){
        $this->m_vpnmap = new VpnInfo;  
        $this->v_vpnmap = Loader::validate('Vpnmap'); 
    }

    protected $beforeActionList = [
        'check_get'   => ['only' => 'get'],
        'check_post'  => ['only' => 'add,del'],
        'check_login' 
    ];

    //【接口】获取VPN信息
    public function get(){
        if(!$this->v_vpnmap->scene('get')->check(input()))
            return Finalfail($this->v_vpnmap->getError());

        $page = is_null(input('get.page')) ? 1 : input('get.page');
        $row = is_null(input('get.row')) ? 10 : input('get.row');

        $count = $this->m_vpnmap->countAllVPNInfo();
        $data = $count == 0 ?  [] : $this->m_vpnmap->selectVPNInfo($page, $row);

        $result = ["vpn" => $data, "count" => $count];
        return Finalsuccess($result);
    }

    //【接口】添加VPN
    public function add(){
        $conf_arr = RawJsonToArr();
        if(!array_key_exists('vpn',$conf_arr))
            Error("22001");
        
        $conf_arr = $conf_arr['vpn'];
        if(!$this->v_vpnmap->scene('add')->check(['vpn' => $conf_arr]))
            return Finalfail($this->v_vpnmap->getError());
        
        // $result = $this->m_vpnmap->selectAllVPNIp();
        // $had_conf = empty($result) ? [] : array_map(function ($item){
        //     return $item['v_vpn'];
        // },$result);
        
        // $save_conf = $this->parseIP($conf_arr,$had_conf);

        foreach ($conf_arr as $key => $value) {
            $v_vpn_arr[] = explode("|", $value)[0];
            $r_vpn_arr[] = explode("|", $value)[1];
        }


        foreach ($v_vpn_arr as $key => $value) {
            unset($v_vpn_arr[$key]);
            if (in_array($value, $v_vpn_arr)){
                Error("10007","重复添加");
            }
        }

        foreach ($conf_arr as $key => $value) {
            $rres = $this->m_vpnmap->selectVpnInfoByVvpnRvpn(explode("|", $value)[0]);
            if (!empty($rres)) {
                Error("10007","重复添加");
            }
            $save_conf[] = ["v_vpn"=>explode("|", $value)[0],"r_vpn"=>explode("|", $value)[1]];
        }

        

        $result = $this->m_vpnmap->insertAllVPNInfo($save_conf);
        if(count($result) <= 0)
            return Finalfail("20001");

        $this->reloadVPNMap();

        $ress = rewriteGConf();

        if ($ress != "success") {
            #if failed delete record

            $this->m_vpnmap->deleteVPNInfoById($result);

            $this->reloadVPNMap();
            return Finalfail("11024",$ress);
        }
        
        return Finalsuccess();
    }

    //【接口】删除VPN
    public function del(){
        $clear = input('get.clear');
        if($clear === "true"){
            ExcuteExec("uroot fpcmd vpn_info -f");//清空

            $res_tmp = $this->m_vpnmap->getVPNInfoAll();

            $this->m_vpnmap->deleteVPNInfoAll();
            
            $ress = rewriteGConf();

            if ($ress != "success") {
                foreach ($res_tmp as $key => $value) {
                    unset($res_tmp[$key]["id"]);
                }
                $this->m_vpnmap->insertAllVPNInfo($res_tmp);
                $this->reloadVPNMap();

                return Finalfail("11024",$ress);
            }
            return Finalsuccess();
        }

        if(!$this->v_vpnmap->scene('del')->check(input()))
            return Finalfail($this->v_vpnmap->getError());

        $id_arr = explode(",", input('post.ids'));

        $res_tmp = $this->m_vpnmap->getVPNInfoByIds($id_arr);

        $this->m_vpnmap->deleteVPNInfoById($id_arr);

        $this->reloadVPNMap();

        $ress = rewriteGConf();

        if ($ress != "success") {

            foreach ($res_tmp as $key => $value) {
                unset($res_tmp[$key]["id"]);
            }
            
            $this->m_vpnmap->insertAllVPNInfo($res_tmp);
            $this->reloadVPNMap();
            return Finalfail("11024",$ress);
        }

        return Finalsuccess();
    }

    //【方法】清空所有，并读取现有VPN映射
    private function reloadVPNMap(){
        ExcuteExec("uroot fpcmd vpn_info -f");//清空
        $confs = $this->m_vpnmap->selectAllVPNInfo();
        foreach($confs as $conf){
            $order = "uroot fpcmd vpn_info -v ".$conf['v_vpn']." -r ".$conf['r_vpn'];
            ExcuteExec($order);
        }
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
