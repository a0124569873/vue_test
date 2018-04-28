<?php

namespace app\index\validate;
use think\Validate;

class Stats extends Validate{

    protected $rule = [
        't'        => 'require|checkConfType:1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19',
        'r'        => 'in:1,2,3,4,5,6,7,8,9',
        'ip'       => 'ip',
        'orderby'  => 'in:in_bps,out_bps,in_pps,out_pps,tcp_conn,udp_conn',
        'limit'    => 'integer|egt:0',
        'desc'     => 'in:true,false',
        'list_type'=> 'require|in:0,1,2',
        'del_tmp_bw'=>'require|checkTempList'
    ];

    protected $message  =   [
        't.require'         =>  '22001',
        't.checkConfType'   =>  '15009',
        'r.in'              =>  '15009',
        'ip.ip'             =>  '11006',
        'orderby.in'        =>  '15008',
        'limit.integer'     =>  '15006',
        'limit.egt'         =>  '15006',
        'desc.in'           =>  '15009',
        'list_type.require' =>  '22001',
        'list_type.in'      =>  '15009',
        'del_tmp_bw.require'      =>  '22001',
        'del_tmp_bw.checkTempList'=>  '11006'        
    ];

    protected $scene = [
        'get'               =>  ['t'],
        'get_colligate'     =>  ['r' => 'in:1,2,3,4', 'ip'],
        'get_device'        =>  ['r' => 'in:1,2,3,4,5,6,7,8,9'],
        'get_network'       =>  [''],
        'get_hoststats'     =>  ['orderby', 'limit', 'desc'],
        'get_tmp_bw_list'   =>  ['list_type'],
        'del'               =>  ['t'=>'require|checkConfType:19'],
        'del_tmp_bw_list'   =>  ['list_type', 'del_tmp_bw']
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

    //验证临时黑白名单
    protected function checkTempList($value){
        $valueArr = explode(',', $value);
        $resultArr = array_filter($valueArr, function ($item) {
            return Validate::is(explode('|', $item)[0], "ip") && Validate::is(explode('|', $item)[1], "ip");
        });

        return count($resultArr) == count($valueArr);
    }

}

