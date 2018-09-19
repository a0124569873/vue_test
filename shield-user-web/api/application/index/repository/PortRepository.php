<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/28
 * Time: 17:29
 */

namespace app\index\repository;


use app\common\exception\PermissionDenyException;
use app\common\exception\ZookeeperException;
use app\index\model\BaseModel;
use app\index\model\DnsConfModel;
use app\index\model\HdConfigModel;
use app\index\model\PortModel;
use app\index\model\ProxyConfModel;
use app\index\model\UserInstanceModel;
use app\index\service\Auth;
use think\Log;
use app\common\exception\ElasticSearchException;

class PortRepository extends BaseRepository
{

    protected $hdConfModel = null;

    protected $instanceModel = null;

    protected $proxyConfModel = null;

    protected $dnsConfModel = null;

    public function __construct()
    {
        $this->model = new PortModel();

        $this->hdConfModel = new HdConfigModel();

        $this->instanceModel = new UserInstanceModel();

        $this->proxyConfModel = new ProxyConfModel();

        $this->dnsConfModel = new DnsConfModel();
    }

    /**
     * 生成应用型ID
     *
     * @return string
     * @throws \Exception
     */
    public function generatePortId()
    {
        do {
            $id = PortModel::PORT_ID_PREFIX . str_rand(7);
            $userApp = $this->model->esGetById($id);
        } while ($userApp);

        return $id;
    }

    /**
     * 检查非网站信息是否存在
     *
     * @param $proxyIps
     * @param $port
     * @return bool
     * @throws \Exception
     */
    public function portIsExist($proxyIps, $port)
    {
        try {
            foreach ($proxyIps as $proxyIp) {
                $filter = [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['proxy_ip.ip' => $proxyIp['ip']]],
                                ['term' => ['proxy_port' => $port]],
                            ]
                        ]
                    ]
                ];
                $userApp = $this->getUserApp($filter);
                if (count($userApp) > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getUserApp($filter = [])
    {
        try {
            return $this->model->esSearch($filter);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 根据IP获取非网站信息
     *
     * @param $ip
     * @param null $port
     * @return array
     * @throws \Exception
     */
    public function getPortByIp($ip, $port = null)
    {
        $filter = ['source_ip' => $ip];

        if ($port) {
            $filter['source_port'] = $port;
        }
        $result = $this->model->esSearch($filter);

        return $result;
    }

    /**
     * 添加非网站防护
     *
     * @param $attributes
     * @return bool
     * @throws \Exception
     */
    public function addPort($attributes)
    {
        try {
            $proxyIps = [];
            foreach ($attributes['proxy_ips'] as $proxyIp) {
                if (!$userInstance = $this->instanceModel->esGetById($proxyIp['ddos_id'])) {
                    throw new ElasticSearchException('为找到该用户实例！');
                }

                // 检查该实例是否属于当前用户
                if ($userInstance['uid'] != Auth::id()) {
                    throw new PermissionDenyException('没有权限接入此实例！');
                }

                $proxyIps[] = [
                    'instance_id'   => $proxyIp['ddos_id'],
                    'instance_line' => $userInstance['instance_line'],
                    'area'          => $userInstance['area'],
                    'line'          => $proxyIp['line'],
                    'ip'            => $proxyIp['ip'],
                ];
            }

            $id = $this->generatePortId();
            $data = [
                'status'      => PortModel::PORT_STATUS_LINING,
                'uid'         => Auth::id(),
                'app_id'      => $id,
                'type'        => PortModel::USER_APP_TYPE_PORT,
                'proxy_ip'    => $proxyIps,
                'protocol'    => $attributes['protocol'],
                'proxy_port'  => $attributes['proxy_port'],
                'server_port' => $attributes['server_port'],
                'server_ip'   => explode(',', $attributes['server_ips']),
                'name'        => null,     // CNAME自动调度域名
                'cname'       => null,    // CNAME自动调度CNAME
                'filter'      => null,   // 黑白名单
                'last_update' => gmt_withTZ()
            ];

            return $this->model->esAdd($data, $id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取非网站防护列表
     * @param $filter
     * @param $from
     * @param $size
     * @return array
     * @throws \Exception
     */
    public function portList($filter, $from, $size)
    {
        $ports = $this->model->esSearch($filter, $from, $size);
        foreach ($ports as $key => &$port) {
            $port['last_update'] = strtotime($port['last_update']);
            $port['name'] = $port['name'] ?? null;
            $port['cname'] = $port['cname'] ?? null;
        }

        return $ports;
    }

    public function getPortById($id)
    {
        $port = $this->model->esGetById($id);
        if ($port['type'] != PortModel::USER_APP_TYPE_PORT) {   // 如果查询结果不是应用型，返回空
            return null;
        }

        return $port;
    }

    public function delPortById($id)
    {
        try {
            return (bool)$this->model->esDeleteById($id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 设置Zookeeper Proxy Conf
     *
     * @param $port
     * @return bool
     */
    public function setZKProxyConf($port)
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            $data = [
                'type'   => BaseModel::ZK_TYPE_APP,
                'action' => BaseModel::HD_ZK_ACTION_CREATE,
                'data'   => [
                    'protocol'    => strtolower($port['protocol']),
                    'proxy_port'  => (int)$port['proxy_port'],
                    'server_ip'   => $port['server_ip'],
                    'server_port' => (int)$port['server_port'],
                ]
            ];
            foreach ($port['proxy_ip'] as $proxyIp) {
                $path = '/hd/proxy/' . $proxyIp['ip'] . '/' . micro_timestamp();

                Log::info('Set port proxy with data:' . json_encode(compact('path', 'data')));
                $result = $this->model->zkSetData($path, $data);
                if (!$result) {
                    throw new ZookeeperException('将Proxy信息写入ZK时，为PORT：' . $port['domain'] . '设置Proxy配置失败！');
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updatePort($attributes, $port)
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            $data = [
                'protocol'    => $attributes['protocol'] ?? $port['protocol'],
                'proxy_port'  => $attributes['proxy_port'] ?? $port['proxy_port'],
                'server_ip'   => $attributes['server_ip'] ?? $port['server_ip'],
                'server_port' => $attributes['server_port'] ?? $port['server_port'],
                'name'        => $attributes['domain'] ?? ($port['name'] ?? null),
                'cname'       => $attributes['cname'] ?? ($port['cname'] ?? null),
                'status'      => $attributes['status'] ?? ($port['status'] ?? null),
                'last_update' => gmt_withTZ()
            ];

            // 如果存在proxy_ips，需要对proxy_ips 进行处理
            if (isset($attributes['proxy_ips'])) {
                $proxyIps = [];
                foreach ($attributes['proxy_ips'] as $proxyIp) {
                    if (!$userInstance = $this->instanceModel->esGetById($proxyIp['ddos_id'])) {
                        throw new ElasticSearchException('为找到该用户实例！');
                    }
                    // 检查该实例是否属于当前用户
                    if ($userInstance['uid'] != Auth::id()) {
                        throw new PermissionDenyException('没有权限接入此实例！');
                    }

                    $proxyIps[] = [
                        'instance_id'   => $proxyIp['ddos_id'],
                        'instance_line' => $userInstance['instance_line'],
                        'area'          => $userInstance['area'],
                        'line'          => $proxyIp['line'],
                        'ip'            => $proxyIp['ip'],
                    ];
                }
                $data['proxy_ip'] = $proxyIps;
            }

            if (isset($attributes['filter'])) {
                $data['filter'] = [
                    'ip_blacklist' => $attributes['filter']['ip_blacklist'] ?? ($port['filter']['ip_blacklist'] ?? []),
                    'ip_whitelist' => $attributes['filter']['ip_whitelist'] ?? ($port['filter']['ip_whitelist'] ?? []),
                ];
            }

            return $this->model->esUpdateById($data, $port['app_id']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 将 hd_conf 信息写入ES
     * @param $input
     * @return bool
     * @throws ElasticSearchException
     * @throws \Exception
     */
    public function setESHDConf($input)
    {
        $ip = $input['proxy_ip'];
        if (!$hdConfig = $this->hdConfModel->esGetById($input['proxy_ip'])) {
            // 当前高防节点记录不存在时，进行创建
            $hdConfig = [
                'ip'          => $ip,
                'forwarding'  => [
                    [
                        'uid'         => Auth::id(),
                        'server_port' => input('server_port'),
                        'server_ip'   => input('server_ip'),
                        'proxy_port'  => input('proxy_port'),
                    ]
                ],
                'last_update' => gmt_withTZ(),
            ];
            $result = $this->hdConfModel->esAdd($hdConfig, $ip);
        } else {
            // 已经存在的，更新相关信息
            $hdConfig['forwarding'][] = [
                'uid'         => Auth::id(),
                'server_port' => input('server_port'),
                'server_ip'   => input('server_ip'),
                'proxy_port'  => input('proxy_port'),
            ];

            $result = $this->hdConfModel->esUpdateById($hdConfig, $ip);
        }

        if (!$result) {
            throw new ElasticSearchException("写入PORT：" . $input['domain'] . " hd_config失败");
        }

        return true;
    }

    /**
     * 删除应用型防护的Proxy Conf配置信息
     *
     * @param $port
     * @return bool
     */
    public function rmESProxyConf($port)
    {
        try {
            foreach ($port['proxy_ip'] as $proxyIp) {
                if (!$proxyConfig = $this->proxyConfModel->esGetById($proxyIp['ip'])) {
                    continue; // 未找到改高防节点的Proxy Conf信息时，不进行移除操作
                }

                foreach ($proxyConfig['forwarding'] as $index => $forwarding) {
                    if ($forwarding['uid'] == Auth::id() && $forwarding['proxy_port'] == $port['proxy_port']) {
                        unset($proxyConfig['forwarding'][$index]);
                    }
                }

                if (!$this->proxyConfModel->esUpdateById(['forwarding' => array_values($proxyConfig['forwarding'])], $proxyIp['ip'])) {
                    throw new ElasticSearchException("删除PORT：" . $port['proxy_ip'] . " Proxy Config配置信息失败");
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 移除Zookeeper中Proxy Conf 配置信息
     *
     * @param $port
     * @return bool
     */
    public function rmZKProxyConf($port)
    {
        try {
            $data = [
                'type'   => BaseModel::ZK_TYPE_APP,
                'action' => BaseModel::HD_ZK_ACTION_DELETE,
                'data'   => [
                    'proxy_port' => (int)$port['proxy_port'],
                    'proxy_ip'   => array_column($port['proxy_ip'], 'ip'),
                    'protocol'   => strtolower($port['protocol'])
                ]
            ];
            foreach ($port['proxy_ip'] as $proxyIp) {
                $path = '/hd/proxy/' . $proxyIp['ip'] . '/' . micro_timestamp();
                if (!$this->model->zkSetData($path, $data)) {
                    throw new ZookeeperException('将Proxy：' . $port['proxy_ip'] . '删除信息写入ZK时失败！');
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新Proxy Conf forwarding 转发规则
     *
     * @param $port
     * @return bool
     */
    public function setESProxyConf($port)
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            $data = [
                'uid'         => Auth::id(),
                'server_port' => $port['server_port'],
                'server_ip'   => $port['server_ip'],
                'proxy_port'  => $port['proxy_port'],
                'filter'      => [
                    'ip_blacklist' => $port['filter']['ip_blacklist'] ?? [],
                    'ip_whitelist' => $port['filter']['ip_whitelist'] ?? [],
                ]
            ];

            foreach ($port['proxy_ip'] as $proxyIp) {
                if (!$proxyConf = $this->proxyConfModel->esGetById($proxyIp['ip'])) {
                    // 当前高防节点记录不存在时，进行创建
                    $proxyConf = ['ip' => $proxyIp['ip'], 'forwarding' => [$data], 'last_update' => gmt_withTZ()];
                    if (!$this->proxyConfModel->esAdd($proxyConf, $proxyIp['ip'])) {
                        throw new \Exception('更新Proxy Conf转发规则失败！');
                    }
                } else {
                    // 已经存在的，更新相关信息
                    $proxyConf['forwarding'] = $proxyConf['forwarding'] ?? [];
                    $key = array_search($port['proxy_port'], array_column($proxyConf['forwarding'], 'proxy_port'));
                    if ($key != false) {    // 当前配置已经写入，更新已经写入的配置
                        $proxyConf['forwarding'][$key] = $data;
                    } else {      // 当前配置尚未写入，写入配置
                        $proxyConf['forwarding'][] = $data;
                    }
                    $proxyConf['last_update'] = gmt_withTZ();
                    if (!$this->proxyConfModel->esUpdateById($proxyConf, $proxyIp['ip'])) {
                        throw new \Exception('更新Proxy Conf转发规则失败！');
                    }
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 增加用户实例的接入数量
     * @param $port
     * @return bool
     */
    public function addUserInstancePortCount($port)
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            foreach ($port['proxy_ip'] as $proxyIp) {
                if (!$userInstance = $this->instanceModel->esGetById($proxyIp['instance_id'])) {
                    throw new ElasticSearchException('用户接入实例不存在！');
                }

                // 验证该实例是否属于该用户
                if ($userInstance['uid'] != Auth::id()) {
                    throw new PermissionDenyException('没有权限接入此实例！');
                }

                // 将对应的实例端口接入数增加
                foreach ($userInstance['hd_ip'] as $index => $hdIp) {
                    if ($hdIp['ip'] == $proxyIp['ip'] && $hdIp['line'] == $proxyIp['line']) {
                        $userInstance['hd_ip'][$index]['port_count'] = ($hdIp['port_count'] ?? 0) + 1;
                    }
                }

                if (!$this->instanceModel->esUpdateById(['hd_ip' => $userInstance['hd_ip']], $proxyIp['instance_id'])) {
                    throw new ElasticSearchException('更新实例接入数量失败！');
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 削减用户实例端口接入数
     *
     * @param $port
     * @return bool
     */
    public function cutUserInstancePortCount($port)
    {
        try {
            foreach ($port['proxy_ip'] as $proxyIp) {
                if (!$userInstance = $this->instanceModel->esGetById($proxyIp['instance_id'])) {
                    continue;
                }
                foreach ($userInstance['hd_ip'] as $index => $hdIp) {
                    if ($hdIp['line'] == $proxyIp['line'] && $hdIp['ip'] == $proxyIp['ip']) {
                        $userInstance['hd_ip'][$index]['port_count'] = ($hdIp['port_count'] ?? 0) > 0 ? $hdIp['port_count'] - 1 : 0;
                    }
                }

                if (!$this->instanceModel->esUpdateById([
                    'hd_ip' => $userInstance['hd_ip'], 'last_update' => gmt_withTZ()
                ], $proxyIp['instance_id']
                )) {
                    throw new ElasticSearchException('更新用户实例接入数失败！');
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkIsUpdatePortInfo($data, $port)
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            // 检查源站IP是否更新
            if (isset($data['server_ips']) && $data['server_ips'] != implode(',', $port['server_ip'])) {
                return true;
            }
            // 检查源站端口是否更新
            if (isset($data['server_port']) && $data['server_port'] != $port['server_port']) {
                return true;
            }
            // 检查转发协议是否更新
            if (isset($data['protocol']) && $data['protocol'] != $port['protocol']) {
                return true;
            }
            // 检查代理端口是否更新
            if (isset($data['proxy_port']) && $data['proxy_port'] != $port['proxy_port']) {
                return true;
            }
            // 检查代理IP是否更新
            if (isset($data['proxy_ips'])) {
                // 如果所选高防IP数量与原数量不一致，需要更新当前Port信息
                if (count($data['proxy_ips']) != count($port['proxy_ip'])) {
                    return true;
                }
                // 如果所选高防IP数量和原数量一致，比较所有高仿IP和实例是否一致，不一致时更新当前Port信息
                foreach ($data['proxy_ips'] as $proxyIp) {
                    $exist = false;
                    foreach ($port['proxy_ip'] as $v) {
                        if ($exist) {
                            continue;
                        }
                        // 实例ID，线路和端口完全相同，标识该接入线路已选择（未修改）
                        if ($proxyIp['ddos_id'] == $v['instance_id'] && $proxyIp['ip'] == $v['ip'] && $proxyIp['line'] == $v['line']) {
                            $exist = true;
                        }
                    }

                    if (!$exist) {
                        return true;
                    }
                }
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 设置应用的访问黑白名单
     * @param $data
     * @param $port
     * @return bool
     */
    public function setPortWhiteBlackList($data, $port): bool
    {
        try {
            is_string($port) && $port = $this->getPortById($port);
            $whitelist = explode("\n", trim($data['whitelist'] ?? ''));
            $blacklist = explode("\n", trim($data['blacklist'] ?? ''));

            return (bool)$this->updatePort(compact('whitelist', 'blacklist'), $port);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param $domain
     * @param $port
     * @return string
     * @throws \Exception
     */
    public function generatePortCname($domain, $port)
    {
        is_string($port) && $port = $this->getPortById($port);
        $cname = $domain != ($port['name'] ?? '') ? SiteRepository::generateDomainCName() : $port['cname'];

        return $cname;
    }

    /**
     * 设置CNAME自动调度的DNS配置信息
     *
     * @param $attributes
     * @param $port
     * @return array|bool
     * @throws ElasticSearchException
     */
    public function setESDNSConf($attributes, $port)
    {
        is_string($port) && $port = $this->getPortById($port);
        $dnsConf = [
            'conf_id'     => SiteRepository::generateDynamicDomain($port['name']),    // dns_conf Id为站点动态域名
            'name'        => $port['cname'],                                     // 站点CNAME
            'type'        => DnsConfModel::TYPE_DYNAMIC,                                // 配置类型 Dynamic - 动态; Static - 静态
            'response'    => [],                                                    // DNS响应列表
            'last_update' => gmt_withTZ()
        ];

        foreach ($attributes['proxy_ip'] as $index => $proxyIp) {
            if (!$ddosInstance = $this->instanceModel->esGetById($proxyIp['instance_id'])) {
                throw new ElasticSearchException('未找到该用户实例' . $proxyIp['instance_id'] . '!');
            }

            $dnsConf['response'][$proxyIp['line']] = [
                'line' => $proxyIp['line'],
                'ip'   => $proxyIp['ip'],
                'area' => $ddosInstance['area'],
            ];
            $dnsConf['response'] = array_values($dnsConf['response']);
        }

        return $this->dnsConfModel->esAdd($dnsConf, $dnsConf['conf_id']);
    }

    /**
     * 写入ZK DNS配置信息
     * @param $port
     * @return bool
     * @throws ElasticSearchException
     * @throws ZookeeperException
     */
    public function setZKDNSConf($port)
    {
        is_string($port) && $port = $this->getPortById($port);
        $data = ['action' => BaseModel::HD_ZK_ACTION_CREATE, 'name' => $port['cname'], 'response' => []];
        foreach ($port['proxy_ip'] as $_ip) {
            // 获取实例的接入区域
            if ($ddosInstance = $this->instanceModel->esGetById($_ip['instance_id'])) {
                // 一个线路选择一个IP进行接入
                $data['response'][$_ip['line']] = ['line' => $_ip['line'], 'ip' => $_ip['ip'], 'area' => $ddosInstance['area']];
            }
        }

        // 重建IP的索引
        $data['response'] = array_values($data['response']);

        // 获取所有可用的DNS服务器，并将DNS配置信息写入每台机器的ZK配置上
        $dnsNodeList = (new SiteRepository)->getDNSNodeList();
        if (empty($dnsNodeList)) {
            throw new ElasticSearchException('无可用DNS服务器！');
        }

        $dnsIps = [];
        array_map(function ($item) use (&$dnsIps) {
            foreach ($item['node_ip'] as $nodeIp) {
                $dnsIps[] = $nodeIp['ip'];
            }
        }, $dnsNodeList);

        // 将站点DNS信息写到所有的DNS节点上
        foreach (array_unique($dnsIps) as $ip) {
            $path = '/hd/dns/' . $ip . '/' . micro_timestamp();
            Log::info('Set site DNS with data:' . json_encode(compact('path', 'data')));
            $result = $this->model->zkSetData($path, $data);
            if (!$result) {
                throw new ZookeeperException('写入应用：' . $port['app_id'] . 'ZK DNS配置失败！');
            }
        }

        return true;
    }

    /**
     * 移除ZK DNS配置信息
     * @param $port
     * @return bool
     * @throws ZookeeperException
     */
    public function rmZKDNSConf($port)
    {
        is_string($port) && $port = $this->getPortById($port);
        $data = ['action' => BaseModel::HD_ZK_ACTION_DELETE, 'name' => $port['cname']];

        // 获取DNS信息
        $dnsNodeList = (new SiteRepository)->getDNSNodeList();
        $dnsIps = [];
        array_map(function ($item) use (&$dnsIps) {
            foreach ($item['node_ip'] as $nodeIp) {
                $dnsIps[] = $nodeIp['ip'];
            }
        }, $dnsNodeList);

        foreach (array_unique($dnsIps) as $ip) {
            $path = '/hd/dns/' . $ip . '/' . micro_timestamp();
            if (!$this->model->zkSetData($path, $data)) {
                throw new ZookeeperException('移除应用：' . $port['app_id'] . 'ZK DNS配置失败！');
            }
        }

        return true;
    }

    /**
     * 移除应用ES DNS配置信息
     * @param $port
     * @return bool
     * @throws ElasticSearchException
     */
    public function rmESDNSConf($port)
    {
        is_string($port) && $port = $this->getPortById($port);
        // DNS Conf ID为域名生成的Dynamic域名
        $dnsConfId = SiteRepository::generateDynamicDomain($port['name']);
        if (!$dnsConf = $this->dnsConfModel->esGetById($dnsConfId)) {
            return true;
        }

        if (!$dnsConf = $this->dnsConfModel->esDeleteById($dnsConfId)) {
            throw  new ElasticSearchException('移除应用:' . $port['app_id'] . 'ES DNS 配置信息失败！');
        }

        return true;
    }
}