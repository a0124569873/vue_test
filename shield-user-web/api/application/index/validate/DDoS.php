<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 15:06
 */

namespace app\index\validate;


use think\Validate;

class DDoS extends Validate
{
    protected $message = [];

    protected $rule = [
        'id' => 'require|min:1',
        'area' => 'require|min:1'
    ];

    protected $scene = [
        'get_ddos' => ['id'],
        'active_ddos' => ['area', 'id']
    ];

    protected $field = [
        'id' => '高防实例ID',
        'area' => '实例接入区域'
    ];
}