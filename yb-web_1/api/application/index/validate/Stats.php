<?php

namespace app\index\validate;
use think\Validate;

class Stats extends Validate{

    protected $rule = [
        'orderby'  => 'in:flow_in,flow_out,in_pkt,out_pkt',
        'limit'    => 'integer|egt:0',
        't'        => 'require|checkConfType:1,2,3,4,5,6,7',
        'r'        => 'in:1,2,3,4,5,6,7,8,9',
        'ip'       => 'ip'
        
    ];

    protected $message  =   [
        'orderby.in'        =>  '15008',
        'limit.integer'     =>  '15006',
        'limit.egt'         =>  '15006',       
        't.require'         =>  '22001',
        't.checkConfType'   =>  '15009',
        'r.in'              =>  '15009',
        'ip.ip'             =>  '11006',
        
    ];

    protected $scene = [
        'rank'        =>  ['orderby','limit'], 
        'dev'         =>  ['t','r'],
        'flow'        =>  ['r','ip'],
        
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

