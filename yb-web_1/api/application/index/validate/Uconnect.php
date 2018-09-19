<?php

namespace app\index\validate;
use think\Validate;

class Uconnect extends Validate{

    protected $rule = [
        'page'      => 'integer|gt:0',
        'row'       => 'integer|gt:0',
        'username'  => 'require',
        'vpn_server'  => 'require',
        'interval'  => 'require',
        'p_ip'      => 'require|ip',
        'hide_ip'   => 'ip',
        'id'        => 'integer',
        'ids'       => 'require|multiInteger',
        
    ];

    protected $message  =   [
        'page.integer'      =>  '15004',
        'page.gt'           =>  '15004',
        'row.integer'       =>  '15006',
        'row.gt'            =>  '15006',
        'username.require'  =>  '22001',
        'interval.require'  =>  '22001',
        'vpn_server.require'  =>  '22001',
        'p_ip.require'      =>  '22001',
        'p_ip.ip'           =>  '11006',
        'hide_ip.ip'        =>  '11006',
        'id.integer'        =>  '11021',
        'ids.require'       =>  '22001',
        'ids.multiInteger'  =>  '11010',

    ];

    protected $scene = [
        'get'   => ['page', 'row'],
        'add'   => ['username', 'p_ip', 'vpn_server', 'interval'],
        'update'=> ['id','interval'],
        'del'   => ['ids'],
        
    ];

    // 验证整数组
    protected function multiInteger($value,$rule,$data){
        $integers = explode(',', $value);
        $legals = array_filter($integers, function ($item) {
            return floor($item) == $item && is_numeric($item);
        });
        return count($integers) == count($legals) ? true : false;
    }

}

