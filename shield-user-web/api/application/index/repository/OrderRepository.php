<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 18:18
 */

namespace app\index\repository;


use app\index\job\AssignDDOSServer;
use app\index\model\OrderModel;
use app\index\service\Auth;
use app\index\service\Time;
use Carbon\Carbon;
use think\Log;

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderModel();
    }

    /**
     * 生成订单号,长度
     *
     */
    public function generateOrderId()
    {
        do {
            // 标识 + （年 + 日 + 月 + 时 + 分） + 随机码
            $orderId = OrderModel::ORDER_HD . date('ymdHi') . rand(100, 999);
            $result = $this->model->esGetById($orderId);
        } while ($result);

        return $orderId;
    }

    /**
     * 创建订单
     * @param $data
     * @return array|bool|string
     * @throws \Exception
     */
    public function createOrder($data)
    {
        // 处理订单主信息
        $orderInfo = $this->processOrderInfo($data);
        // 处理订单详情信息
        $orderInfo = $this->processOrderDetails($orderInfo, $orderInfo['type']);
        $result = $this->model->esAdd($orderInfo, self::generateOrderId());

        if ($result['result'] == 'created') {
            return [
                'id' => $result['_id'],
            ];
        }

        return false;
    }

    /**
     * 处理订单创建时的订单主信息
     * @param array $orderInfo
     * @return array
     */
    public function processOrderInfo(array $orderInfo = [])
    {
        $orderInfo = array_merge($orderInfo, [
            'create_time' => gmt_withTZ(),
            'pay_time' => null, // 支付时间
            'status' => OrderModel::ORDER_STATUS_CREATED,
            'fee' => (int) $orderInfo['fee'],
            'type' => (int) $orderInfo['type'],
            'uid' => Auth::id(),
        ]);

        return $orderInfo;
    }

    /**
     * 处理订单创建时的订单详细信息
     * @param array $orderInfo
     * @param $orderType
     * @return array
     */
    public function processOrderDetails(array $orderInfo = [], $orderType)
    {
        // 充值
        if ($orderType == OrderModel::ORDER_TYPE_RECHARGE) {
            // TODO 检查是否有需要添加的
        }

        // 消费
        if ($orderType == OrderModel::ORDER_TYPE_PAID) {
            // 根据所选时间生成结束时间
            $dt = new Time($orderInfo['create_time']);
            $period = $orderInfo['detail']['ord_time'];
            if (!empty($period)) {
                list($period, $unit) = explode(':', $period);
                if ($unit === 'year') {
                    $dt = $dt->addYears($period);
                    // 年周期改为按月计算的周期
                    $orderInfo['detail']['ord_time'] = $period * 12;
                }

                if ($unit === 'month') {
                    $dt = $dt->addMonths($period);
                    // 年周期改为按月计算的周期
                    $orderInfo['detail']['ord_time'] = $period;
                }
            }

            // End_time 默认与create_time相等
            $processedDetail = array_merge($orderInfo['detail'], [
                'start_date' => $orderInfo['create_time'],
                'end_date' => gmt_withTZ($dt->timestamp),
                'instance_id' => []
            ]);
            $orderInfo['detail'] = $processedDetail;
        }

        return $orderInfo;
    }

    /**
     * 获取订单列表
     * @param array $filter
     * @param int $from
     * @param null $size
     * @return array
     * @throws \Exception
     */
    public function orderList($filter = [], $from = 0, $size = null)
    {
        try {
            $orders = $this->model->esSearch($filter, $from, $size);
            foreach ($orders as $key => &$order) {
                $order['oid'] = $order['id'];
                unset($order['id']);

                // 格式化字段返回值
                $order['create_time'] = strtotime($order['create_time']);
                $order['pay_time'] = strtotime($order['pay_time'] ?? null) ?: null;
                $order['detail']['start_date'] = strtotime($order['detail']['start_date']);
                $order['detail']['end_date'] = strtotime($order['detail']['end_date']);
                $order['detail']['fee'] = $order['fee'];
            }

            return $orders;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 根据订单ID获取订单详情
     * @param $id
     * @return null|string
     * @throws \Exception
     */
    public function getOrderById($id)
    {
        $order = $this->model->esGetById($id);
        $order['oid'] = $id;

        return $order;
    }

    /**
     * 作废订单
     * @param $id
     * @return array|bool
     * @throws \Exception
     */
    public function deleteOrder($id): bool
    {
        return (bool)$this->model->esUpdateById(['status' => OrderModel::ORDER_STATUS_TRASHED], $id);
    }

    public function processPaidOrder($id)
    {
        try {
            $order = $this->model->esGetById($id);
            if ($order) {
                // 更新订单状态为已支付
                $this->model->esUpdateById([
                    'last_update' => gmt_withTZ(),
                    'status' => OrderModel::ORDER_STATUS_PAID,
                    'pay_time' => gmt_withTZ()  // 支付时间
                ], $id);

                if ($order['type'] == OrderModel::ORDER_TYPE_PAID) {
                    // 生成高防实例
                    $orderInstancesIds = (new DDoSRepository())->dispatchOrderInstance($id);

                    // 更新订单中分配的实例ID
                    $this->model->esUpdateById(['detail' => ['instance_id' => $orderInstancesIds]], $id);

                    /*
                    // 添加Job为订单分配高仿IP
                    dispatch(AssignDDOSServer::class, ['orderId' => $id]);
                    */
                }
            }
        } catch (\Exception $e) {
            Log::error('Update Order HD IP Error with id:' . $id . ', message:' . $e->getMessage());
        }
    }

    /**
     * 获取可用DNS服务器列表
     * @param null $type 类型
     * @param null $line 线路
     * @param $count 获取数量
     * @return array
     * @throws \Exception
     */
    public function fetchAvailableServers($type = null, $line = null, $count = null)
    {
        $serverList = (new ServerRepository())->getAvailableServerList($type, $line, $count);

        return $serverList;
    }

    public function updateOrderHdIp($id, $hdIp)
    {
        $data = ['detail' => ['hd_ip' => $hdIp]];

        return $this->model->esUpdateById($data, $id);
    }
}