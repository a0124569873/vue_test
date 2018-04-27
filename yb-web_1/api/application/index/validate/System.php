<?php

namespace app\index\validate;
use think\Validate;

class System extends Validate{

    protected $rule = [
        'oper'      => 'require|in:get,set',
        'net'       => 'require|checkNet',
    ];

    protected $message  =   [
        'oper.require'   => '22001',
        'oper.in'        => '15005',
        'net.require'    => '22001',
    ];

    protected $scene = [
        'net'   => ['oper'],
        'set'   => ['net'],
    ];

    // 验证多个IP
    protected function checkNet($value,$rule,$data){
        $ips = explode('|', $value);
        if(count($ips) != 5){
            return "22001";
        }
        $legals = array_filter($ips, function ($item) {
            return Validate::is($item,'ip');
        });
        return 5 == count($legals) ? true : "11006";
    }
}

