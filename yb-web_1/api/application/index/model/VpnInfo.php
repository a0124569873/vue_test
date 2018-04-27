<?php

namespace app\index\model;
use think\Model;
use think\Db;

class VpnInfo extends Model{

    //统计VPN信息个数
    public function countAllVPNInfo(){
        $count = db('vpn_info')->count();
        return $count;
    }
    //分页查询所有VPN信息
    public function selectVPNInfo($page, $row){
        $datas = db('vpn_info')->page($page, $row)->select();
        return $datas;
    }
    //查询所有VPN信息，用于reload
    public function selectAllVPNInfo(){
        $datas = db('vpn_info')->field("v_vpn,r_vpn")->select();
        return $datas;
    }
    //查询现有VPN的ip
    public function selectAllVPNIp(){
        $datas = db('vpn_info')->field("v_vpn")->select();
        return $datas;
    }
    //添加VPN时检查是否重复
    public function insertAllVPNInfo($conf_arr){

        $result = array();

        foreach ($conf_arr as $key => $value) {
            
            db('vpn_info')->insert($value);
            $result[] = db("vpn_info")->getLastInsID();
        }
        
        return $result;

    }
    //删除VPN
    public function deleteVPNInfoById($id_arr){
        $result = db('vpn_info')->delete($id_arr);
        return $result;
    }
    //清除所有VPN
    public function deleteVPNInfoAll(){
        $result = Db::query('DELETE FROM vpn_info');
        return $result;
    }
    //get所有VPN
    public function getVPNInfoAll(){
        $datas = db('vpn_info')->select();
        return $datas;
    }


    //添加vpn接入时检查是否重复
    public function selectVpnInfoByVvpnRvpn($v_vpn){
        $datas = db('vpn_info')->where('v_vpn', $v_vpn)->select();
        return $datas;
    }

    //get vpn by  ids
    public function getVPNInfoByIds($ids){
        $datas = db('vpn_info')->where('id', "IN",$ids)->select();
        return $datas;
    }


}