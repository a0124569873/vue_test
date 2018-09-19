<?php
namespace app\index\event;
use app\index\model\SysConfModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 系统防护参数 分层控制器
 */
class Sysconf extends Controller {

    //【方法】获取系统防护参数
    public function get($conf_type){
        $sys_conf = new SysConfModel;
        $data = $sys_conf->selectConfValue($conf_type);
        if(empty($data) || empty($data[0]['conf_value'])){
            return '';
        }
        return $data[0]['conf_value'];
    }

    //【方法】设置系统防护参数
    public function set($conf_type){
        $conf_value = input('post.'.$conf_type);
        $sys_conf = new SysConfModel;
        $result = $sys_conf->updateConfValue($conf_type, $conf_value);
        if($result < 0)
            Error("20001");

        $conf_arr = $sys_conf->selectAllConfValue();        
        //构建配置json
        $C_builder = controller('Confbuilder', 'event');
        $json_conf = $C_builder->forSysParam($conf_arr);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd system_config");
    }

    //【方法】设置网络地址
    public function setNetWork(){
        $conf_value = input('post.1');
        $sys_conf = new SysConfModel;
        $result = $sys_conf->updateConfValue(3, $conf_value);
        if($result < 0)
            Error("20001");
    }

    //【方法】获取管理口与广播ip
    public function getMgtip(){
        $sys_conf = new SysConfModel;
        $result = $sys_conf->selectConfValue(4);
        if(empty($result)){
            return "";
        }
        if(empty($result[0]['conf_value'])){
            return "";
        }
        return $result[0]['conf_value'];
    }
    //【方法】更新管理口与广播ip
    public function setMgtip($type){ // $type = "mgt" or "bro" or empty
        $conf_value = input('post.3');
        if(empty($type) && count(explode("|", $conf_value)) !== 2){
            Error("22001");
        }
        if(empty($type) && !CheckIpMask(explode("|", $conf_value)[1])){
            Error("11006");
        }
        if(empty($type) && !in_array(explode("|", $conf_value)[0], GetEthnets())){
            Error("11024");
        }
        if($type == "bro" && !CheckIpMask($conf_value)){
            Error("11006");
        }
        if($type == "mgt" && !in_array($conf_value, GetEthnets())){
            Error("11024");
        }

        $sys_conf = new SysConfModel;
        $result = $sys_conf->selectConfValue(4);
        if(empty($result)){
           $mgt_broip = "";
        }elseif(empty($result[0]['conf_value'])){
           $mgt_broip = "";
        }else{
            $mgt_broip = $result[0]['conf_value'];
        }
        
        if($mgt_broip === ""){
            if($type == "mgt"){
                $mgt_broip = $conf_value."|";
            }elseif($type == "bro"){
                $mgt_broip = "|".$conf_value;
            }else{
                $mgt_broip = $conf_value;
            }
        }else{
            $tmp_arr = explode("|", $mgt_broip);
            if($type == "mgt"){
                $tmp_arr[0] = $conf_value;
                $mgt_broip = implode("|", $tmp_arr);
            }elseif($type == "bro"){
                $tmp_arr[1] = $conf_value;
                $mgt_broip = implode("|", $tmp_arr);
            }else{
                $mgt_broip = $conf_value;
            }
        }

        $result = $sys_conf->updateConfValue(4, $mgt_broip);
        if($result < 0)
            Error("20001");
        
        $mgt_arr = explode("|", $mgt_broip);
        if(count($mgt_arr) === 2 && ($mgt_arr[0] !== "") && ($mgt_arr[1] !== "") ){
            ExcuteExec("fpcmd set_local_bcast -i ".$mgt_arr[0]." -ip ".$mgt_arr[1]);
        }
    }

    // 获取网卡汇聚信息
    public function getEthgrp(){
        $parse_json = [];
        $all_eth = [];
        $all_eth = GetEthnets();

        exec("python /etc/scripts/xmsd-client/lacp --action=query", $result, $statu);
        $real_status = $statu < 128 ? $statu : $statu-256;
        if(!($statu === 0 && isset($result[0]) && is_array(json_decode($result[0], true))))
            Error("20005", "error: python /etc/scripts/xmsd-client/lacp --action=query");
        
        $json_arr = json_decode($result[0], true);
        $ethnet_arr = []; // 非汇聚网卡
        $grp_arr = []; // 汇聚网卡的分组名
        $ethgrp_arr = []; // 汇聚网卡详情
        
        foreach($json_arr['if'] as $tmp){
            if(preg_match("/^eth\d+_0$/", $tmp['name']) == 1 && !in_array($tmp['name'], $all_eth)){ // 过滤不存在网卡,ethgrp除外
                continue;
            }
            if($tmp['type'] === 'ethernet'){
                $ethnet_arr[] = [
                    "name"=>$tmp['name'],
                    "ips" =>$tmp['ips']
                ];
            }elseif($tmp['type'] === 'ethgrp'){
                $grp_arr[$tmp['name']] = $tmp['ips'];
            }
        }

        foreach($json_arr['ethgrp'] as $tmp){
            $tmp_arr = [];
            $tmp_arr = [
                "name"=>$tmp['name'],
                "mac"=>$tmp['mac-address'],
                "load_mode"=>$tmp['load-balance'],
                "ips"=>array_key_exists($tmp['name'], $grp_arr) ? $grp_arr[$tmp['name']] : []
            ];
            if(empty($tmp['bindings'])){
                $tmp_arr["binds"] = [];
            }else{
                $flag = false;
                foreach($tmp['bindings'] as $eth){
                    if(!in_array($eth['port'], $all_eth)){ // 过滤不存在网卡
                        $flag = true;
                        break;
                    }
                    $tmp_arr["binds"][] = $eth['port']."|".$eth['mode'];
                }
                if($flag){
                    continue;
                }
            }
            $ethgrp_arr[] = $tmp_arr;
        }
        $parse_json = [
            "ethgrp" => $ethgrp_arr,
            "ethnet" => $ethnet_arr,
            // "all_eth"=> $all_eth
        ];

        return $parse_json;
    }

    // 设置网卡汇聚信息
    public function setEthgrp(){
        $json_arr = input('post.2/a');
        if(!$this->_checkEthJson($json_arr))
            Error("10032", "netgrp configuration format error");
        
        $all_eth = GetEthnets();
        $before_eth_net_grp = $this->_getAllEthNetGrp($all_eth); // 修改前所有网卡与汇聚网卡
        $after_eth_net_grp = [];// 修改后所有网卡与汇聚网卡
        $ethgrp_arr = []; // ethgrp内容
        $if_arr = []; // if
        $if_grp_arr = []; // if 中的ethgrp

        foreach($json_arr['ethgrp'] as $tmp){
            $binds_arr = [];
            $tmp_ethgrp_arr[] = [];
            if(empty($tmp['binds'])){
                $binds_arr = [];
            }else{
                foreach($tmp['binds'] as $eth_mode){
                    $eth_mode_arr = explode("|", $eth_mode);
                    $binds_arr[] = [
                        "port"=>$eth_mode_arr[0], 
                        "mode"=>$eth_mode_arr[1]
                    ];
                }
            }
            $if_grp_arr[] = [
                "name" => $tmp['name'],
                "type" => 'ethgrp',
                "ips"  => $tmp['ips']
            ];
            $tmp_ethgrp_arr = [
                "name"  =>  $tmp['name'],
                "bindings"  =>  $binds_arr,
                "load-balance" =>  $tmp['load_mode'],
            ];
            if(!empty($tmp['mac'])){
                $tmp_ethgrp_arr["mac-address"] = $tmp['mac'];
            }
            $ethgrp_arr[] = $tmp_ethgrp_arr;
            $after_eth_net_grp[] = $tmp['name'];
        }

        foreach($json_arr['ethnet'] as $tmp){
            $if_arr[] = [
                "name"  =>  $tmp["name"],
                "type"  =>  "ethernet",
                "ips"   =>  $tmp["ips"]
            ];
            $after_eth_net_grp[] = $tmp['name'];
        }

        $if_arr = array_merge($if_arr, $if_grp_arr);
        $parse_json = [
            "ethgrp" => $ethgrp_arr,
            "if" => $if_arr
        ];

        $fp = fopen("/tmp/lacp_config.json", "w");
        fwrite($fp, json_encode($parse_json, JSON_UNESCAPED_SLASHES));
        fclose($fp);

        exec("python /etc/scripts/xmsd-client/lacp --action=update --conf=/tmp/lacp_config.json", $result, $statu);
        $real_status = $statu < 128 ? $statu : $statu-256;
        if($real_status < 0){
            if($real_status === -1){ // 配置更格式错误
                Error("10032", isset($result[0]) ? $result[0] : "netgrp configuration error");
            }elseif($real_status === -2){ // xmsd五秒后将重启
                Error("20005", "xmsd server will restart at 5 seconds later");
            }
        }
        if($real_status === 0) // 未改变
            return ;

        if($real_status > 0){ // 更改成功后使配置生效
            ExcuteExec("python /etc/scripts/xmsd-client/ddos");

            $del_eth = [];  // 筛选出删除掉的网卡 将其从redis删除
            foreach($before_eth_net_grp as $eth){
                if(!in_array($eth, $after_eth_net_grp)){
                    $del_eth[] = $eth;
                }
            }
            if(!empty($del_eth)){
                $redis = new \Redis();
                $redis->pconnect('127.0.0.1');
                foreach($del_eth as $tmp){
                    $redis->hdel('interface_stat', $tmp);
                }
            }
        }
    }

    // 获取已经配置的所有网口与汇聚网卡
    private function _getAllEthNetGrp($all_eth){
        $eth_net_grp = [];
        exec("python /etc/scripts/xmsd-client/lacp --action=query", $result, $statu);
        $real_status = $statu < 128 ? $statu : $statu-256;
        if(!($statu === 0 && isset($result[0]) && is_array(json_decode($result[0], true))))
            Error("20005", "error: python /etc/scripts/xmsd-client/lacp --action=query");
        
        $json_arr = json_decode($result[0], true);
        foreach($json_arr['if'] as $tmp){
            if($tmp['type'] === 'ethernet' && preg_match("/^eth\d+_0$/", $tmp['name']) == 1 && !in_array($tmp['name'], $all_eth)){
                    continue;
            }
            $eth_net_grp[] = $tmp['name'];
        }
        return $eth_net_grp;
    }

    private function _checkEthJson($json_arr){
        $all_eth = GetEthnets();
        if(!is_array($json_arr) || empty($json_arr)){
            return false;
        }
        if(!(isset($json_arr['ethgrp']) && isset($json_arr['ethnet']))){
            return false;
        }
        foreach($json_arr['ethgrp'] as $tmp){
            if( !(isset($tmp['name']) && isset($tmp['load_mode']) && isset($tmp['ips']) && isset($tmp['binds'])) ){
                return false;
            }
            if(!empty($tmp['mac']) && (filter_var($tmp['mac'], FILTER_VALIDATE_MAC) === false) ){
                return false;
            }
            if(empty($tmp['name'])){
                return false;
            }
            if(!in_array($tmp['load_mode'], ['xor-ip', 'round-robin', 'xor-mac'])){
                return false;
            }

            if(!empty($tmp['ips'])){                
                $tmp_arr = [];
                foreach($tmp['ips'] as $ip){
                    if(!CheckIpMask($ip)){
                        return false;
                    }
                    if(in_array($ip, $tmp_arr)){
                        return false;
                    }
                    $tmp_arr[] = $ip;
                }
            }
            if(!empty($tmp['binds'])){                
                $tmp_arr = [];
                foreach($tmp['binds'] as $binds){
                    if(count(explode("|", $binds)) != 2){
                        return false;
                    }
                    if(!in_array(explode("|", $binds)[0], $all_eth)){
                        return false;
                    }
                    if(!in_array(explode("|", $binds)[1], ["active", "passive", "static"])){
                        return false;
                    }
                    if(in_array($binds, $tmp_arr)){
                        return false;
                    }
                    $tmp_arr[] = $binds;
                }
            }
            
        }

        foreach($json_arr['ethnet'] as $tmp){
            if(!(isset($tmp['name']) && isset($tmp['ips']))){
                return false;
            }
            if(empty($tmp['name'])){
                return false;
            }elseif(!in_array($tmp['name'], $all_eth)){
                return false;
            }
            if(!empty($tmp['ips'])){
                $tmp_arr = [];
                foreach($tmp['ips'] as $ip){
                    if(!CheckIpMask($ip)){
                        return false;
                    }
                    if(in_array($ip, $tmp_arr)){
                        return false;
                    }
                    $tmp_arr[] = $ip;
                }
            }
            $tmp_arr[] = $tmp;
        }
        return true;
    }

}