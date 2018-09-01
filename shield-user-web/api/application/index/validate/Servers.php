<?php

namespace app\index\validate;
use think\Validate;

class Servers extends Validate{

    protected $field = [
        'id' => '用户实例ID'
    ];

    protected $rule = [
        'page'    =>  'integer|egt:0',
        'row'     =>  'integer|egt:0',
        'server_ip'=> 'require|multiIp',
        'thr_in'  =>  'integer|egt:0',
        'thr_out' =>  'integer|egt:0',

        // Site Price
        'type' => 'require|in:1,2,3', // 实例类型 1 独享网站类型, 2 共享
        'line' => 'require|min:1',
        'base_bandwidth' => 'require|integer',
        'bandwidth' => 'require|integer',
        'count' => 'require|integer|>=:1',     // 实例数
        'period' => 'require',  // 购买时长
        'site_count' => 'require|integer|>=:50',    //防护域名数
        'port_count' => 'require|integer|>=:50',    //端口数

        // 获取实例的可接入地域
        'id' => 'require|min:1' // 用户实例的ID
    ];

    protected $message  =   [
        'page.integer'      =>  '11011',
        'page.egt'          =>  '15004',
        'row.integer'       =>  '11011',
        'row.egt'           =>  '15006',
        'server_ip.require' =>  '22001',
        'thr_in.integer'    =>  '11011',
        'thr_out.integer'   =>  '11011',
        'thr_in.egt'        =>  '15007',
        'thr_out.egt'       =>  '15007',
    ];

    protected $scene = [
        'get_servers'   =>  ['page', 'row'], //获取用户的服务器列表的验证场景
        'add_servers'   =>  ['server_ip'], //用户添加服务器的验证场景
        'setthr'        =>  ['server_ip', 'thr_in', 'thr_out'], //用户设置服务器阈值的验证场景

        'base_price' => ['type'],
        'site_price' => ['type', 'line', 'base_bandwidth', 'bandwidth', 'site_count', 'count', 'period'], // 网站类型
        'port_price' => ['type', 'line', 'base_bandwidth', 'bandwidth', 'port_count', 'count', 'period'], //非网站类型
        'server_area' => ['id'], //获取实例的可接入地域
    ];

    // 验证多个ip 以|隔开
    protected function multiIp($value,$rule,$data){
        $flag = true;
        $ip_arr = explode('|', $value);
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

