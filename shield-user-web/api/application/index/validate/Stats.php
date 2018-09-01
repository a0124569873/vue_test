<?php

namespace app\index\validate;
use think\Validate;

class Stats extends Validate{

    protected $rule = [
        'server_ip'=> 'require|ip',
        'orderby'  => 'in:flow_in,flow_out,in_pkt,out_pkt,in_conn,out_conn',
        'limit'    => 'integer|egt:0'
    ];

    protected $message  =   [
        'server_ip.require' =>  '22001',
        'server_ip.ip'      =>  '11006',
        'orderby.in'        =>  '15008',
        'limit.integer'     =>  '15006',
        'limit.egt'         =>  '15006'       
        
    ];

    protected $scene = [
        'real_time_flow'   =>  ['server_ip'], //获取某个服务器ip 的实时流量验证
        'flow_rank'        =>  ['orderby','limit'], //获取服务器流量排行
    ];

}

