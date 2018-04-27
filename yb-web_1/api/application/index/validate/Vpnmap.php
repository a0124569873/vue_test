<?php

namespace app\index\validate;
use think\Validate;

class Vpnmap extends Validate{

    protected $rule = [
        'page'      => 'integer|gt:0',
        'row'       => 'integer|gt:0',
        'vpn'       => 'require|checkVpnmap',
        'ids'       => 'require|multiInteger',
        
    ];

    protected $message  =   [
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'v_vpn.require'     =>  '22001',
        'r_vpn.require'     =>  '22001',
        'v_vpn.ip'          =>  '11006',
        'r_vpn.ip'          =>  '11006',
        'ids.require'       =>  '22001',
        'ids.multiInteger'  =>  '11010',

    ];

    protected $scene = [
        'get' => ['page', 'row'],
        'add' => ['vpn'],
        'del' => ['ids'],
        
    ];

    // 验证整数组
    protected function multiInteger($value,$rule,$data){
        $integers = explode(',', $value);
        $legals = array_filter($integers, function ($item) {
            return floor($item) == $item && is_numeric($item);
        });
        return count($integers) == count($legals) ? true : false;
    }

    //验证添加vpn格式 ip ip范围 ip掩码
    protected function checkVpnmap($value,$rule,$data){
        if(!is_array($value)){
            return "11019";
        }
        foreach($value as $conf){
            $conf_arr = explode("|", $conf);
            if( !Validate::is($conf_arr[0],'ip') && !CheckIpRange($conf_arr[0]) && !CheckIpMask($conf_arr[0]) ){
                return "11016";
            }
            if(!Validate::is($conf_arr[1],'ip')){
                return "11006";
            }
        }
        return true;
    }

}

