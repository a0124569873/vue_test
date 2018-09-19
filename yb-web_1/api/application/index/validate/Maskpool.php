<?php

namespace app\index\validate;
use think\Validate;

class Maskpool extends Validate{

    protected $rule = [
        'page'      => 'integer|gt:0',
        'row'       => 'integer|gt:0',
        'ips'       => 'require|multiIp',
        'pool'    => 'require|checkIpPool',
        
    ];

    protected $message  =   [
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'ids.require'       =>  '22001',
        'ids.multiInteger'  =>  '11010',
        'ips.require'       =>  '22001',
        'ips.multiIp'       =>  '11006',
        'pool.require'      =>  '22001',
    ];

    protected $scene = [
        'get'   => ['page', 'row'],
        'add'   => ['pool'],
        'del'   => ['ips'],
        
    ];

    // 验证整数组
    protected function multiInteger($value,$rule,$data){
        $integers = explode(',', $value);
        $legals = array_filter($integers, function ($item) {
            return floor($item) == $item && is_numeric($item);
        });
        return count($integers) == count($legals) ? true : false;
    }

    // 验证多个IP
    protected function multiIp($value,$rule,$data){
        $ips = explode(',', $value);

        foreach ($ips as $key => $value) {
            if(strstr($value, "/")){
                if (!CheckIpMask($value))
                    Error("11016","error ip_range mask error");
            }else{
                if (!filter_var($value, FILTER_VALIDATE_IP))
                    Error("11016","error ip_range ip error");
            }

        }
        return true;

    }

    //验证添加伪装原型池 ip ip范围 ip掩码
    protected function checkIpPool($value,$rule,$data){
        if(!is_array($value)){
            return "11019";
        }
        foreach($value as $ip){
            if( !Validate::is($ip,'ip') && !CheckIpMask($ip) ){
                return "11016";
            }
        }
        return true;
    }

}

