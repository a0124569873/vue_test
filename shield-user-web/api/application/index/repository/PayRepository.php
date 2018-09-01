<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 17:11
 */

namespace app\index\repository;


class PayRepository extends BaseRepository
{
    protected $orderRepository;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();
    }

    /**
     * 订单支付
     * @param $data
     * @return bool
     */
    public function orderPay($data)
    {
        $payResult = $this->doPay($data['order_id']);

        if ($payResult) {
            $this->emitPaySuccessEvent($data);
        }

        return $payResult;
    }

    /**
     * 进行支付操作
     *
     * @param $orderId
     * @return bool true 支付成功| false 支付失败
     */
    public function doPay($orderId)
    {
        // 如果是测试环境，直接返回支付成功
        if (config('app_debug')) {
            return true;
        }

        // TODO 生产环境进行支付操作
        return false;
    }

    /**
     * 执行支付成功后的操作
     * @param $data
     */
    public function emitPaySuccessEvent($data)
    {
        // 处理支付成功的订单
        $this->orderRepository->processPaidOrder($data['order_id']);
    }
}