<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 18:06
 */

namespace app\index\validate;


use think\Validate;

class Order extends Validate
{
    protected $message = [];

    protected $field = [
        'type' => '订单类型',
        'fee' => '订单金额',
        'detail' => '订单详情',
        'detail.instance_line' => '实例线路',
        'detail.base_bandwidth' => '保底防护带宽',
        'detail.bandwidth' => '弹性防护贷款',
        'detail.ord_time' => '实例时长',
        'detail.port_count' => '防护端口数',
        'detail.site_count' => '防护域名数',
        'detail.sp_num' => '购买数量',
        'detail.normal_bandwidth' => '业务带宽',
        'id' => '订单编号',
    ];

    protected $rule = [
        'type' => 'require|in:1,2',
        'fee' => 'require|min:0',
        'detail' => 'require',
        'detail.product_id' => 'require|in:0,1,2,3',
        'detail.instance_line' => 'require|min:1',
        'detail.base_bandwidth' => 'require|integer|>=:0',
        'detail.bandwidth' => 'require|integer|>=:0',
        'detail.ord_time' => 'require|min:1',
        'detail.port_count' => 'require|>=:50',
        'detail.site_count' => 'require|>=:50',
        'detail.sp_num' => 'require|integer|min:1',
        'detail.normal_bandwidth' => 'require|integer|min:1',
        'id' => 'require',
    ];

    protected $scene = [
        'site_paid_order' => [
            'type', 'fee', 'detail', 'detail.product_id', 'detail.instance_line', 'detail.base_bandwidth',
            'detail.bandwidth', 'detail.ord_time', 'detail.site_count', 'detail.sp_num','detail.normal_bandwidth'
        ],

        'port_paid_order' => [
            'type', 'fee', 'detail', 'detail.product_id', 'detail.instance_line', 'detail.base_bandwidth',
            'detail.bandwidth', 'detail.ord_time', 'detail.port_count', 'detail.sp_num','detail.normal_bandwidth'
        ],

        'recharge_order' => ['type', 'fee', 'detail', 'detail.product_id'],
        'delete_order' => ['id'],
        'show_order' => ['id'],
    ];
}
