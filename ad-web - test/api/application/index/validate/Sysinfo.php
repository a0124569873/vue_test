<?php

namespace app\index\validate;
use think\Validate;

class Sysinfo extends Validate{

    protected $rule = [
        't'        => 'require|checkConfType:1,2,3,4,5,6,7,8',
        
    ];

    protected $message  =   [
        't.require'         =>  '22001',
        't.checkConfType'   =>  '15009',
        
    ];

    protected $scene = [
        'get'         =>  ['t'],
    ];

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

