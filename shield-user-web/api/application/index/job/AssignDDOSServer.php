<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/8
 * Time: 14:37
 */

namespace app\index\job;


use app\index\repository\OrderRepository;
use app\index\repository\ServerRepository;
use app\index\traits\JobFailed;
use think\Log;
use think\queue\Job;

/**
 * 为已支付购买的用户分配高仿节点
 *
 * Class AssignDDOSServer
 * @package app\index\job
 */
class AssignDDOSServer extends BaseJob
{

    protected $orderRepository;
    protected $serverRepository;

    protected $maxAttempt = 3;

    // 下次重试时间
    protected $delay = 120;

    use JobFailed;

    public function __construct()
    {
        $this->orderRepository = new OrderRepository();

        $this->serverRepository = new ServerRepository();
    }

    /**
     * 执行队列
     * @param Job $job
     * @param $data
     * @return mixed|void
     */
    public function fire(Job $job, $data)
    {
        Log::info('Job handle with data:' . json_encode($data));
        try {
            // 根据订单ID获取订单详情
            $order = $this->orderRepository->getOrderById($data['orderId']);
            if ($order) {
                // 获取可用高防节点的列表
                $ddosList = $this->getDDOSList($order['detail']['product_id'], $order['detail']['line'], $order['detail']['count']);
                // 如果获取不到DDOS信息，两分钟后重试
                if (count($ddosList) != $order['detail']['count']) {
                    Log::info('No enough ddos server for order.' .
                        json_encode(['orderId' => $data['orderId'], 'count' => $order['detail']['count']]));
                    $this->retry($job);
                }

                // 执行DDOS Phy conf
                $phyIps = array_column($ddosList, 'ip');
                foreach ($phyIps as $ip) {
                    // 更细高仿节点的接入信息
                    if (!$this->updatePhyConfUser($ip)) {
                        Log::info('Update ddos conf error.' . json_encode([
                                'orderId' => $data['orderId'], 'ip' => $ip
                            ]));
                        $this->retry($job);
                    }
                }

                // 更新订单的高仿节点接入信息
                if (!$this->updateOrderDDOSIps($data['orderId'], $phyIps)) {
                    $this->retry($job);
                }
            } else {
                Log::info('Order ' . $data['orderId'] . ' not found.');
            }

            // 执行成功后，清楚Job
            $job->delete();
        } catch (\Exception $e) {
            // 如果异常执行Job重试
            $this->retry($job);
        }
    }

    /**
     * 获取指定数量的可用高仿节点
     * @param $productId
     * @param $line
     * @param $count
     * @return array
     * @throws \Exception
     */
    private function getDDOSList($productId, $line, $count)
    {
        return $this->orderRepository->fetchAvailableServers($productId, $line, $count);
    }

    /**
     * 更新订单的DDOS IP
     * @param $orderId
     * @param $ddosIps
     * @return array|bool
     */
    public function updateOrderDDOSIps($orderId, $ddosIps)
    {
        return $this->orderRepository->updateOrderHdIp($orderId, $ddosIps);
    }

    /**
     * 更新DDOS Phy_conf 接入信息
     * @param $ddosIps
     * @return bool
     * @throws \Exception
     */
    private function updatePhyConfUser($ddosIps)
    {
        return $this->serverRepository->updateServerUserCount($ddosIps);
    }

}