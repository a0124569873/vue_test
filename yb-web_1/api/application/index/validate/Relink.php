<?php

namespace app\index\validate;
use think\Validate;

class Relink extends Validate{

    protected $rule = [
        'page'      => 'integer|gt:0',
        'row'       => 'integer|gt:0',
        'link'      => 'require|checkLinkConf',
        'uids'      => 'require|multiInteger',
        
    ];

    protected $message  =   [
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'link.require'      =>  '22001',
        'uids.require'      =>  '22001',
        'uids.multiInteger' =>  '11010',
    ];

    protected $scene = [
        'get'   => ['page', 'row'],
        'set'   => ['link'],
        'del'   => ['uids'],
        
    ];

    // 验证主动回连配置信息
    protected function checkLinkConf($value,$rule,$data){
        if(!is_array($value)){
            return "11019";
        }
        foreach($value as $conf){
            $conf_arr = explode("|",$conf);
            if(count($conf_arr)!=7){
                return "11019";
            }
            #todo

            if(strstr( $conf_arr[0] , "/")){
                if(!($this->CheckIpMask($conf_arr[0])))
                    return "11018";
            }elseif(!empty($conf_arr[0])){
                if( !Validate::is($conf_arr[0], "ip")){
                    return "11006";
                }
            }

            if(strstr( $conf_arr[1] , "/")){
                if(!($this->CheckIpMask($conf_arr[1])))
                    return "11018";
            }elseif(!empty($conf_arr[1])){
                if( !Validate::is($conf_arr[1], "ip")){
                    return "11006";
                }
            }

            if(!Validate::is($conf_arr[5], "ip") ){
                return "11006";
            }

            
            if(!$this->protocol($conf_arr[6])){
                return "11023";
            }

            if($conf_arr[6] == "6" || $conf_arr[6] == "17"){
                if(!empty($conf_arr[2])){
                    if (!$this->port($conf_arr[2])) {
                        return "11009";
                    }
                    if ($conf_arr[2] < 32768) {
                        return "11029";
                    }

                }

                if(strstr($conf_arr[3], ":")){

                    if ( !$this->port(explode(":", $conf_arr[3])[0]) || !$this->port(explode(":", $conf_arr[3])[1]) || !$this->port($conf_arr[4])) {
                        return "11009";
                    }

                    $dst_port_arr = explode(":", $conf_arr[3]);

                    if ($dst_port_arr[0] > $dst_port_arr[1] || $dst_port_arr[0] < 32768 || $dst_port_arr[1] < 32768) {
                        return "11029";
                    }

                }else{

                    if ( !$this->port($conf_arr[3]) || !$this->port($conf_arr[4])) {
                        return "11009";
                    }

                    if ($conf_arr[3] < 32768) {
                        return "11029";
                    }

                }
            }else{
                return "11023";
            }
        }
        return true;
    }

    // 验证IP/MASK
    function CheckIpMask($ip){
        $ip_mask = explode('/', $ip);
        return count($ip_mask)==2 && ip2long($ip_mask[0]) && is_numeric($ip_mask[1]) && is_int((int)$ip_mask[1]) && $ip_mask[1] >= 1 && $ip_mask[1] <= 32;
    }

    //端口验证
    protected function port($port){
        $result = is_numeric($port) && floor($port) == $port && $port > 0 && $port <= 65535;
        return $result;
    }
    //协议号验证
    protected function protocol($value){
        $result = $value == 'all' || (is_numeric($value) && $value >= 0 && $value <= 137);
        return $result;
    }

    // 验证整数组
    protected function multiInteger($value,$rule,$data){
        $integers = explode(',', $value);
        $legals = array_filter($integers, function ($item) {
            return floor($item) == $item && is_numeric($item);
        });
        return count($integers) == count($legals) ? true : false;
    }
}

