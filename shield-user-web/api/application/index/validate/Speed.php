<?php

namespace app\index\validate;
use think\Validate;

class Speed extends Validate{

    protected $rule = [
        'oper'           =>  'require',
        'domain'         =>  'require|domain',
        'status'         =>  'require|integer|egt:0',
        'white_list'     =>  'require',
        'black_list'     =>  'require'
    ];

    protected $message  =   [
        'oper.require'      =>  '22001',
        'oper.in'           =>  '15005',
        'domain.require'    =>  '22001',
        'status.require'    =>  '22001',
        'status.integer'    =>  '11001',
        'status.egt'        =>  '11011', 
        'white_list.require'=>  '22001',
        'black_list.require'=>  '22001'        
    ];

    protected $scene = [
        'speed_status'     =>   ['oper'=>'require|in:set,get','domain'],
        'set_speed_status' =>   ['status'],
        'get_speed_conf'   =>   ['domain'],
        'set_speed_conf'   =>   ['domain','oper'=>'require|in:quick,w_list,b_list,w_list'],
        'w_list'           =>   ['white_list'],
        'b_list'           =>   ['black_list']
    ];

    // 验证域名
    protected function domain($value,$rule,$data){
        $res = Validate::regex($value, '\b([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}\b');
        if(!$res) 
            return "11003";

        $domainArr = explode('.', $value);
        if(count($domainArr) < 2)
            return "11003";
        
        return true;
    }

}

