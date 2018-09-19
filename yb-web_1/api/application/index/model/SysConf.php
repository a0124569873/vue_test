<?php

namespace app\index\model;
use think\Model;
use think\Db;

class SysConf extends Model{

    protected $pk = 'id';
    protected $table = 'sys_conf';

    //更新网络地址配置
    public function updateNetConf($ips){
        $result = db('sys_conf')->where('conf_type', 'net')->update(['conf_value' => $ips]);
        return $result;
    }

    public function getNetConf(){
        $result = db('sys_conf')->where('conf_type', 'net')->select();
        return $result;
    }

    //分页查询vpnserver
    public function selectVpnServer($page, $row){
        $datas = db('sys_conf')-> where("conf_type","2")->page($page, $row)->select();
        return $datas;
    }

    //统计vpnserver个数
    public function countVpnServer(){
        return db('sys_conf')-> where("conf_type","2")->count();
    }

    public function getAllVpnServerIp(){
        $result = db('sys_conf')->where('conf_type', '2')->select();
        $res_arr = [];
        foreach ($result as $key => $value) {
        	$res_arr[] = explode("|", $value["conf_value"])[0]."|".explode("|", $value["conf_value"])[4];
        }
        return $res_arr;
    }

    public function addVpnServerConf($conf_arr){
        $result = db('sys_conf')->insertAll($conf_arr);
        return $result;
    }

    public function updateVpnServerConf($id,$conf_val){
        $result = db('sys_conf')->where('id', $id)->update(['conf_value' => $conf_val]);
        return $result;
    }

    public function delVpnServerConf($ids_arr){
        $result = db('sys_conf')->delete($ids_arr);
        return $result;
    }

    public function delAllVpnServerConf(){
        $result = db('sys_conf')->where("conf_type","2")->delete();
        return $result;
    }

    public function getVpnServerConfById($id){
        $result = db('sys_conf')->where("id",$id)->select();
        return $result;
    }

    public function getVpnServerConfByIpId($ip,$id){
        $result = db('sys_conf')->where(["conf_value" => ["like","%".$ip."%"],"conf_type" => "2"])->where(["id" => ["<>",$id] ])->select();
        return $result;
    }

    public function getVpnServerConfByIp($ip){
        $result = db('sys_conf')->where(["conf_value" => ["like","%".$ip."%"],"conf_type" => "2"])->select();
        return $result;
    }

    public function addGServerConf($conf_arr){
    	$result = db('sys_conf')->where("conf_type","1")->select();
    	if (empty($result)){
    		$result = db('sys_conf')->insertAll($conf_arr);
    	}else{
    		// var_dump($conf_arr);
    		$result = db('sys_conf')->where("conf_type","1")->update(["conf_value" => $conf_arr[0]["conf_value"]]);
    	}
    	return $result;

    }

    public function getGServerConf(){
    	$sstr = "";
    	$result = db('sys_conf')->where("conf_type","1")->select();
    	if (!empty($result)){
    		$sstr = $result[0]["conf_value"];
    	}
    	return $sstr;
    }





}