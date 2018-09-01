<?php

namespace app\index\validate;
use think\Validate;

class Logs extends Validate{

    protected $rule = [
        'page'    =>  'integer|egt:0',
        'row'     =>  'integer|egt:0'
    ];

    protected $message  =   [
        'page.integer'      =>  '11011',
        'page.egt'          =>  '15004',
        'row.integer'       =>  '11011',
        'row.egt'           =>  '15006',      
        
    ];

    protected $scene = [
        'attack'   =>  ['page', 'row'], //获取牵引日志
        
    ];

   
    
}

