<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 15:07
 */

namespace app\index\repository;


use app\index\model\DDoSModel;
use app\index\model\OrderModel;
use app\index\model\PhyConfModel;
use app\index\model\PortModel;
use app\index\model\ProxyNodeInfoModel;
use app\index\model\SiteModel;
use app\index\model\UserInstanceModel;
use app\index\service\Auth;
use app\index\service\MockData;

class DDoSRepository extends BaseRepository
{

    protected $orderModel;

    protected $phyConfModel;

    protected $instanceModel;

    protected $proxyNodeInfoModel;

    public function __construct()
    {
        $this->model = new DDoSModel();

        $this->orderModel = new OrderModel();

        $this->phyConfModel = new PhyConfModel();

        $this->instanceModel = new UserInstanceModel();

        $this->proxyNodeInfoModel = new ProxyNodeInfoModel();
    }

    public function generateInstanceId()
    {
        do {
            $id = 'ddos-' . strtolower(str_rand(7));
            $result = $this->instanceModel->esGetById($id);
        } while ($result);

        return $id;
    }

    /**
     * 构造高防IP查询的基础查询
     * @return array
     */
    public static function DDoSFilter()
    {
        $filter = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            "term" => [ //需要是当前用户
                                'uid.keyword' => Auth::id()
                            ]
                        ],
                        [
                            "term" => [ // 订单需要是消费订单
                                'type' => OrderModel::ORDER_TYPE_PAID,
                            ],
                        ],
                        [
                            'term' => [ // 订单需要是已支付状态
                                'status' => OrderModel::ORDER_STATUS_PAID,
                            ]
                        ],
                        [
                            'range' => [ // 实例的有效期需要大于当前时间
                                'detail.end_date' => [
                                    'gt' => gmt_withTZ()
                                ],
                            ]
                        ]
                    ],
                    'must_not' => [
                        'term' => [ // 已分配高防IP
                            'detail.hd_ip.keyword' => ''
                        ]
                    ]
                ]
            ]
        ];

        return $filter;
    }

    /**
     * 获取高仿IP列表
     * @param array $filter
     * @param int $from
     * @param null $size
     * @return array
     * @throws \Exception
     */
    public function getDDoSList($filter = [], $from = 0, $size = null)
    {
        $ddosList = $this->instanceModel->esSearch($filter, $from, $size);
        // 过滤所有项的Start_date 和 end_date
        foreach ($ddosList as $key => $item) {
            $ddosList[$key]['start_date'] = strtotime($item['start_date']);
            $ddosList[$key]['end_date'] = strtotime($item['end_date']);
            $ddosList[$key]['last_update'] = strtotime($item['last_update']);
        }

        return $ddosList;
    }

    /**
     * 获取用户实例信息
     * @param $ip
     * @return array
     * @throws \Exception
     */
    public function getIPPhyConf($ip)
    {
        $phyConf = $this->phyConfModel->esGetById($ip);

        return $phyConf ?: [];
    }

    /**
     * 为用户分发订单实例
     * @param $orderId
     * @return array|bool
     * @throws \Exception
     */
    public function dispatchOrderInstance($orderId)
    {
        $order = (new OrderRepository())->getOrderById($orderId);
        if (!$order && !empty($order['detail'])) {
            return false;
        }

        $count = $order['detail']['sp_num'] ?? 0;
        $orderInstanceIds = [];
        while ($count > 0) {
            // 生成用户实例ID
            $instanceId = $this->generateInstanceId();
            $data = [
                'instance_id' => $instanceId,
                'uid' => Auth::id(),
                'type' => $order['detail']['product_id'],   // 实例类型：1-WEB共享型，2-WEB独享型，3-应用型
                'status' => UserInstanceModel::STATUS_CREATED,  // 实例状态，已创建未接入
                'instance_line' => $order['detail']['instance_line'],   // 实力线路
                'normal_bandwidth' => $order['detail']['normal_bandwidth'], // 业务带宽
                'bandwidth' => $order['detail']['bandwidth'],   // 弹性防护带宽
                'base_bandwidth' => $order['detail']['base_bandwidth'], // 保底防护带宽
                'start_date' => $order['detail']['start_date'], // 开始时间
                'end_date' => $order['detail']['end_date'], // 结束时间
                'area' => null,   // 地域
                'node_id' => null,  // 高防节点的ID
                'hd_ip' => [],  // 高防IP列表
                'last_update' => gmt_withTZ()
            ];

            // 网站类型
            if (in_array($order['detail']['product_id'], [UserInstanceModel::INSTANCE_TYPE_SHARED, UserInstanceModel::INSTANCE_TYPE_SINGLE])) {
                $data['site_count'] = $order['detail']['site_count'];
            }

            // 应用类型
            if ($order['detail']['product_id'] == UserInstanceModel::INSTANCE_TYPE_PORT) {
                $data['port_count'] = $order['detail']['port_count'];
            }

            if ($this->instanceModel->esAdd($data, $instanceId)) {
                // 未分配实例数减少1
                $count--;
                array_push($orderInstanceIds, $instanceId);
            } else {
                throw new \Exception();
            }
        }

        return $orderInstanceIds;
    }

    /**
     * 获取用户的高防实例
     * @param $filter
     * @return array
     */
    public function getUserDDoSList($filter)
    {
        try {
            $filter['query']['bool']['must'][] = ['term' => ['uid.keyword' => Auth::id()]];
            $ddosList = $this->instanceModel->esSearch($filter);
            // 过滤所有项的Start_date 和 end_date
            foreach ($ddosList as $key => $item) {
                $ddosList[$key]['start_date'] = strtotime($item['start_date']);
                $ddosList[$key]['end_date'] = strtotime($item['end_date']);
            }

            return $ddosList;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 根据ID获取高防实例 信息
     *
     * @param $id
     * @return null|string
     * @throws \Exception
     */
    public function getDDoSById($id)
    {
        try {
            $ddos = $this->instanceModel->esGetById($id);
            if ($ddos) {
                $ddos['start_date'] = strtotime($ddos['start_date']);
                $ddos['end_date'] = strtotime($ddos['end_date']);
            }

            return $ddos;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取可用的高仿节点信息
     *
     * @param $ddosLine
     * @param $ddosType
     * @param $ddosArea
     * @return mixed|null
     */
    public function getAvailableProxyNodeInfo($ddosLine, $ddosType, $ddosArea)
    {
        try {
            $filter = [
                'query' => ['bool' => ['must' => [
                    ["term" => ['line_type' => $ddosLine]],
                    ["term" => ['area' => $ddosArea]],
                    ["term" => ['node_type' => $ddosType]]
                ]]]
            ];

            // 独享型实例
            if ($ddosType == UserInstanceModel::INSTANCE_TYPE_SINGLE) {
                $filter['query']['bool']['must'][] = ['term' => ['user_count' => 0]];
            }

            // 获取一个可用的高仿节点信息
            $proxyNodeInfo = $this->proxyNodeInfoModel->esSearch($filter, 0, 1);
            if (!empty($proxyNodeInfo)) {
                return array_shift($proxyNodeInfo);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 根据实例ID，更新实例状态为已更新
     * 1.根据实例的线路、类型、接入区域获取
     * @param $ddosInstanceId
     * @param $proxyNodeInfo
     * @return array|bool
     */
    public function ddosActive($ddosInstanceId, $proxyNodeInfo)
    {
        try {

            $hdIps = $proxyNodeInfo['node_ip'];
            if (in_array($proxyNodeInfo['node_type'], [UserInstanceModel::INSTANCE_TYPE_SINGLE, UserInstanceModel::INSTANCE_TYPE_SHARED])) {
                array_walk($hdIps, [$this, 'processSiteInstanceHdIps']);
            } else {
                array_walk($hdIps, [$this, 'processPortInstanceHdIps']);
            }

            $instanceData = [
                'hd_ip' => $hdIps,
                'node_id' => $proxyNodeInfo['node_id'],
                'area' => $proxyNodeInfo['area'],
                'status' => UserInstanceModel::STATUS_ACTIVATED
            ];

            return $this->instanceModel->esUpdateById($instanceData, $ddosInstanceId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新当前高防节点的用户接入数
     *
     * @param $proxyNodeInfo
     * @return bool
     */
    public function updateProxyNodeUserCount($proxyNodeInfo)
    {
        try {
            $newCount = (int)(($proxyNodeInfo['user_count'] ?? 0) + 1);

            return $this->proxyNodeInfoModel->esUpdateById(['user_count' => $newCount], $proxyNodeInfo['node_id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取用户的高防IP
     *
     * @param null $line
     * @param null $type
     * @param int $from
     * @param null $size
     * @return array
     * @throws \Exception
     */
    public function getUserDDoSIps($line = null, $type = null, $from = 0, $size = null)
    {

        $ips = [];
        $filter = ['query' => ['bool' => ['must' => [
            ['term' => ['uid.keyword' => Auth::id()]],
            ['term' => ['status' => UserInstanceModel::STATUS_ACTIVATED]],
        ]]]];

        $line !== null && $filter['query']['bool']['must'][] = ['term' => ['hd_ip.line' => $line]];
        $type && $filter['query']['bool']['must'][] = ['term' => ['type' => $type]];

        // 获取列表
        $instances = $this->instanceModel->esSearch($filter, $from, $size);
        $total = $this->instanceModel->esCount($filter);

        foreach ($instances as $instance) {
            array_walk($instance['hd_ip'], function (&$item) {
                $item['line_text'] = UserInstanceModel::$instanceLines[$item['line']];
                unset($item['site_count']);
            });

            $ips[$instance['id']] = [
                'type' => $instance['type'],
                'area' => $instance['area'],
                'area_text' => UserInstanceModel::$instanceAreas[$instance['area']],
                'ddos_id' => $instance['id'],
                'ips' => $instance['hd_ip']
            ];
        }

        return ['list' => array_values($ips), 'total' => $total];
    }

    /**
     * 获取实例可接入
     * @param $line
     * @param $type
     * @return array
     */
    public function getAvailableArea($line, $type)
    {
        try {
            // 获得当前实例可接入的所有高防节点
            $must = [
                ['term' => ['line_type' => $line]],
                ['term' => ['node_type' => $type]],
            ];

            // 如果是独享型实例，只能获取尚未有用户接入的高仿节点
            $type == UserInstanceModel::INSTANCE_TYPE_SINGLE && $must[]= ['term' => ['user_count' => 0]];

            $filter = ['query' => ['bool' => ['must' => $must]]];
            $nodes = $this->proxyNodeInfoModel->esSearch($filter);
            $availableAreas = array_column($nodes, 'area');

            // 获取所有地域列表
            $areas = MockData::value('AreaList');
            foreach ($areas as $key => &$area) {
                if (isset($area['children'])) {
                    // 如果有子地域，根据自地域判断当前地域是否可用，并移除不可用的地域
                    foreach ($area['children'] as $_k => $_area) {
                        if (!in_array($_area['value'], $availableAreas)) {
                            unset($areas[$key]['children'][$_k]);
                        }
                    }
                    // 如果该节点下所有子地域都被移除，说明当前地域无可接入的地域，移除地域
                    if (empty($areas[$key]['children'])) {
                        unset($areas[$key]);
                    }else{
                        $areas[$key]['children'] = array_values($areas[$key]['children']);
                    }
                } else {
                    // 如果没有子地域，直接判断当前地域是否可用，移除不可用的
                    if (!in_array($area['value'], $availableAreas)) {
                        unset($areas[$key]);
                    }
                }
            }

            return array_values($areas);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getRelationAreaIds($areaId)
    {
        $relationIds = [];
        $areas = MockData::value('AreaList');
        foreach ($areas as $key => $area) {
            // 如果是一级地域
            if ($areaId = $area['value']) {
                $relationIds = array_column($area['children'], 'value');
                break;
            }
        }
        // 二级地域
        empty($relationIds) && $relationIds = [$areaId];

        return $relationIds;
    }

    private function processSiteInstanceHdIps(&$item)
    {
        $item['site_count'] = 0;

        return $item;
    }

    private function processPortInstanceHdIps(&$item)
    {
        $item['port_count'] = 0;

        return $item;
    }

}