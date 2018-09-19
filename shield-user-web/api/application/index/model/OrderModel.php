<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 18:03
 */

namespace app\index\model;


class OrderModel extends BaseModel
{

    protected $esIndex = 'user_order';

    protected $esType = 'type';

    //------------------------- 订单状态 -------------------------------

    const ORDER_STATUS_CREATED = 0; // 已创建，待支付

    const ORDER_STATUS_PAID = 1;    // 已支付

    const ORDER_STATUS_TRASHED = 2; //已删除

    //------------------------- 订单状态 -------------------------------


    const ORDER_TYPE_RECHARGE = 1; //充值
    const ORDER_TYPE_PAID = 2; // 消费

    //------------------------- 订单类型标识符 -------------------------------

    const ORDER_HD = 1;

    //------------------------- 订单类型标识符 -------------------------------
}