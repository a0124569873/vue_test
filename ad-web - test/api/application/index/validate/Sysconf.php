<?php

namespace app\index\validate;
use think\Validate;

class Sysconf extends Validate{

    protected $rule = [
        't'        => 'require|checkConfType:1,2,3',
        'update1'  => 'require|checkCount:3|checkupdate1',
    ];

    protected $message  =   [
        't.require'         =>  '22001',
        't.checkConfType'   =>  '15009',
        'update1.require'   =>  '22001',
        'update1.checkCount'=>  '22001',
        'update1.checkupdate1'   =>  '11006',
        
    ];

    protected $scene = [
        'get'               =>  ['t'],
        'update'            =>  ['t'],
        'update_network'    =>  ['update1'],
        
    ];

    //验证参数个数
    protected function checkCount($value, $rule){
        $valueArr = explode('|', $value);
        return $rule == count($valueArr);
    }

    // 验证多个ip（网络地址配置）
    protected function checkupdate1($value){
        $valueArr = explode('|', $value);
        $resultArr = array_filter($valueArr, function ($item) {
            return Validate::is($item,'ip');
        });
        return count($resultArr) == count($valueArr);
    }

    //掩码验证 如255.255.0.0
    protected function check_mask_ok($mask) {
        $m = @ip2long($mask);
        if(empty($m)) {
            return false;
        }
        $m = ~$m & 0xffffffff;
        return ($m&($m+1)) == 0;
    }

    //验证多个类型|隔开
    protected function checkConfType($value, $rule){
        $valueArr = explode('|', $value);
        $ruleArr = explode(',', $rule);
        
        $resultArr = array_filter($valueArr, function ($item) use ($ruleArr) {
            return in_array($item, $ruleArr);
        });

        return count($resultArr) == count($valueArr);
    }

}

