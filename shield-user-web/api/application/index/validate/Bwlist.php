<?php

namespace app\index\validate;
use think\Validate;

class Bwlist extends Validate{

    protected $rule = [
        'domain'    =>  'require|domain',
        'blackip'   =>  'ips',
        'whiteip'   =>  'ips',
        'port'      =>  'require|port'
    ];

    protected $message  =   [
        'domain.require'      =>  '22001',
        'port.require'        =>  '22001',
        
    ];

    protected $scene = [
        'get_bwlist'     =>  ['domain'],
        'add_bwlist'     =>  ['domain','blackip','whiteip'],
        'get_proxy_bwlist'  =>  ['domain','port'],
        'set_proxy_bwlist'  =>  ['domain','port','blackip','whiteip']
    ];

    // 验证端口
    protected function port($value,$rule,$data){
        return is_numeric($value) && floor($value) == $value && $value >= 0 && $value <= 65535 ? true : '11009';
    }

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

    // 验证多个ip的字符串 用逗号间隔
    protected function ips($value,$rule,$data){
        $flag = true;
        $ip_arr = explode(',', $value);
        foreach($ip_arr as $ip){
            if(Validate::is($ip,'ip')){
                continue;
            }else{
                $flag = false;
                break;
            }
        }
        return $flag ? true : "11006";
    }
}

