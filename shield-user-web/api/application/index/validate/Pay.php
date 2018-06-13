<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 17:14
 */

namespace app\index\validate;


use app\index\model\OrderModel;
use think\Validate;

class Pay extends Validate
{

    protected $rule = [
        'order_id' => 'require|min:10'
    ];

    protected $message = [];

    protected $field = [
        'order_id' => '订单编号'
    ];

    protected $scene = [
        'pay' => ['order_id']
    ];

    public function orderExist($value, $rule, $data)
    {
        try {
            $order = (new OrderModel)->esGetById($value);

            return $order ? true : '订单' . $value . '不存在';
        } catch (\Exception $e) {
            return '订单' . $value . '不存在';
        }

    }

}