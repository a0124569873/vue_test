<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 16:08
 */

namespace app\index\repository;


use app\index\model\OrderModel;
use app\index\model\ProxyNodeInfoModel;
use app\index\model\ServerModel;
use app\index\model\SiteModel;
use app\index\model\UserInstanceModel;
use app\index\service\Auth;
use app\index\service\MockData;

class ServerRepository extends BaseRepository
{
    protected $orderModel;

    protected $instanceModel;

    protected $proxyNodeInfoModel;

    public function __construct()
    {
        $this->model = new ServerModel();

        $this->orderModel = new OrderModel();

        $this->proxyNodeInfoModel = new ProxyNodeInfoModel();

        $this->instanceModel = new UserInstanceModel();
    }

    public function calcSiteServerPrice($serverInfo)
    {
        $amount = $monthUnitPrice = 100;  // 一个网站实例一个端口一个月的价格

        // 计算周期价格
        list($period, $priceUnit) = explode(':', $serverInfo['period']);
        if ($priceUnit === 'year') {
            $amount = $period * ($monthUnitPrice * 12);
        }

        if ($priceUnit === 'month') {
            $amount = $period * $monthUnitPrice;
        }

        // 计算相应的端口数价格
        $amount = $amount * $serverInfo['site_count'];

        // 计算相应购买数量的价格
        $amount = $amount * $serverInfo['count'];

        return $amount;
    }

    public function calcNonSiteServerPrice($serverInfo)
    {
        $amount = $monthUnitPrice = 50; // 一个非网站实例一个端口一个月的价格

        // 计算周期价格
        list($period, $priceUnit) = explode(':', $serverInfo['period']);
        if ($priceUnit === 'year') {
            $amount = $period * ($monthUnitPrice * 12);
        }

        if ($priceUnit === 'month') {
            $amount = $period * $monthUnitPrice;
        }

        //计算相应端口数的价格
        $amount = $amount * $serverInfo['port_count'];

        // 计算相应购买数量的价格
        $amount = $amount * $serverInfo['count'];

        return $amount;
    }

    /**
     * 获取用户实例
     * @param null $line
     * @param null $type
     * @return array
     * @throws \Exception
     */
    public function getUserServer($line = null, $type = null)
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
                            'term' => [ // 实例的有效期需要大于当前时间
                                'status' => OrderModel::ORDER_STATUS_PAID,
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

        if ($line != null && $line != '') {
            $filter['query']['bool']['must'][] = ['term' => ['detail.line' => $line]];
        }

        if ($type != null && $type != '') {
            $filter['query']['bool']['must'][] = ['term' => ['detail.product_id' => $type]];
        }

        $_result = $this->orderModel->esSearch($filter);

        $result = [];
        foreach ($_result as $key => $order) {
            if (empty($order['detail'])) {
                continue;
            }

            $result[] = [
                'type' => $order['detail']['product_id'],
                'hd_ip' => $order['detail']['hd_ip'],
                'line' => $order['detail']['line']
            ];
        }

        return $result;
    }

    /**
     * 获取系统可分配实例
     * @param $type 1 独享型，2 共享型，3 非站点型
     * @param $line
     * @param null $count
     * @return array
     * @throws \Exception
     */
    public function getAvailableServerList($type = null, $line = null, $count = null)
    {

        if ($type == SiteModel::DOMAIN_TYPE_SINGLE) {
            // 独享型
            $list = $this->getSignalServerList($line, $count);
        } else if ($type == SiteModel::DOMAIN_TYPE_SHARED) {
            // 共享型
            $list = $this->getSharedServerList($line, $count);
        } else if ($type == SiteModel::DOMAIN_TYPE_PORT) {
            // 非网站类型
            $list = $this->getPortServerList($line, $count);
        } else {
            // 所有类型
            $list = $this->getServerList($line);
        }

        return $list;
    }

    /**
     * 获取所有类型的可分配实例
     *
     * @param null $line
     * @param $count
     * @return array
     * @throws \Exception
     */
    public function getServerList($line = null, $count)
    {
        $filter = [
            'query' => [
                'bool' => [
                    'must' => []
                ]
            ]
        ];


        if ($line) {
            $filter['query']['bool']['must'][] = ['term' => ['line' => $line]];
        }

        return $this->model->esSearch($filter, 0, $count);
    }

    /**
     * 获取独享型可用实例列表
     * @param null $line
     * @param $count
     * @return array
     * @throws \Exception
     */
    public function getSignalServerList($line = null, $count)
    {
        $filter = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'user_count' => '0',
                            ],
                        ],
                        [
                            'term' => [
                                'type' => SiteModel::DOMAIN_TYPE_SINGLE,
                            ],
                        ],
                    ]
                ]
            ]
        ];

        if ($line) {
            $filter['query']['bool']['must'][] = ['term' => ['line' => $line]];
        }

        return $this->model->esSearch($filter, 0, $count);
    }

    /**
     * 获取共享型实力列表
     * @param null $line
     * @param $count
     * @return array
     * @throws \Exception
     */
    public function getSharedServerList($line = null, $count)
    {
        $filter = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'type' => SiteModel::DOMAIN_TYPE_SHARED,
                            ],
                        ],
                    ]
                ]
            ]
        ];

        if ($line) {
            $filter['query']['bool']['must'][] = ['term' => ['line' => $line]];
        }

        return $this->model->esSearch($filter, 0, $count);
    }

    /**
     * 获取非网站类型实例列表
     *
     * @param null $line
     * @param $count
     * @return array
     * @throws \Exception
     */
    public function getPortServerList($line = null, $count)
    {
        $filter = [
            'query' => [
                'bool' => [
                    'must' => [
                        [
                            'term' => [
                                'type' => SiteModel::DOMAIN_TYPE_PORT,
                            ],
                        ],
                    ]
                ]
            ]
        ];
        if ($line) {
            $filter['query']['bool']['must'][] = ['term' => ['line' => $line]];
        }

        return $this->model->esSearch($filter, 0, $count);
    }

    /**
     * 更新高仿节点的介入信息
     * @param $id
     * @param int $count
     * @return bool
     * @throws \Exception
     */
    public function updateServerUserCount($id, $count = 1)
    {
        $server = $this->model->esGetById($id);
        if (!$server) {
            return false;
        }

        // 高仿节点介入用户数增加
        $userCount = (int)(isset($server['user_count']) ? $server['user_count'] : 0);
        $data = ['user_count' => $userCount + $count];

        // 高仿节点接入站点数增加
        if (in_array($server['type'], [ServerModel::SERVER_TYPE_WEB_ALONE, ServerModel::SERVER_TYPE_WEB_SHARED])) {
            $siteCount = (int)(isset($server['site_count']) ? $server['site_count'] : 0);
            $data['site_count'] = $siteCount + $count;
        }

        // 高仿节点接入端口数增加
        if ($server['type'] == ServerModel::SERVER_TYPE_WEB_PORT) {
            $portCount = (int)(isset($server['port_count']) ? $server['port_count'] : 0);
            $data['port_count'] = $portCount + $count;
        }

        $result = $this->model->esUpdateById($data, $id);

        return $result ? true : false;
    }

    /**
     * 获取实例可接入的地域
     * @param $line
     * @param $type
     * @return array
     */
    public function getServerAvailableAreas($line, $type)
    {
        try {
            $must = [
                ['term' => ['line_type' => $line]],
                ['term' => ['node_type' => $type]],

            ];
            $filter = ['query' => ['bool' => ['must' => $must]]];
            $nodes = $this->proxyNodeInfoModel->esSearch($filter);

            $areas = [];
            $instanceAreas = UserInstanceModel::$instanceAreas;
            foreach ($nodes as $key => $node) {
                $areas[] = ['value' => $node['area'], 'text' => $instanceAreas[$node['area']]];
            }

            return $areas;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取实例接入线路
     * @return array
     */
    public function getServerLines()
    {
        $lines = [];
        $_lines = UserInstanceModel::$instanceLines;
        foreach ($_lines as $key => $line) {
            $lines[] = ['value' => $key, 'text' => $line];
        }

        return $lines;
    }

}