<?php

namespace app\index\validate;
use think\Validate;

class Logs extends Validate {
    protected $rule = [
        't'           =>  'require|checkType:1,2,3,4',
        'start_time'  =>  'integer|egt:0|elt:end_time',
        'end_time'    =>  'integer|egt:0',
        'target_ip'   =>  'ip',
        'host_ip'     =>  'ip',
        'target_port' =>  'integer|port',
        'attack_type' =>  'in:1,2,3',
        'range'       =>  'in:1,2,3',
        'page'        =>  'integer|egt:1',
        'row'         =>  'integer|egt:1'
    ];

    protected $message = [
        't.require'         =>  '22001',
        't.checkType'       =>  '15009',
        'start_time.integet'=>  '11023',
        'start_time.egt'    =>  '11023',
        'start_time.elt'    =>  '11023',
        'end_time.integer'  =>  '11023',
        'end_time.egt'      =>  '11023',
        'target_ip.ip'      =>  '11006',
        'host_ip.ip'        =>  '11006',
        'target_port.integer'=>  '11009',
        'target_port.port'  =>  '11009',
        'attack_type.in'    =>  '15009',
        'page.integer'      =>  '15004',
        'page.egt'          =>  '15004',
        'row.integer'       =>  '15006',
        'row.egt'           =>  '15006'
    ];

    protected $scene = [
        'get_attack'        =>  [
            't',
            'start_time', 
            'end_time', 
            'target_ip', 
            'target_port', 
            'attack_type'
        ]
    ];

    protected function checkType($value, $rule) {
        $valueArr = explode('|', $value);
        $ruleArr = explode(',', $rule);
        
        $resultArr = array_filter($valueArr, function ($item) use ($ruleArr) {
            return in_array($item, $ruleArr);
        });

        return count($resultArr) == count($valueArr);
    }

    protected function port($value, $rule) {
        return $value > 0 && $value < 65536;
    }
}