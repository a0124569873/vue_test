<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 18:52
 */

namespace app\index\repository;


use app\common\exception\ElasticSearchException;
use app\common\exception\PermissionDenyException;
use app\common\exception\ZookeeperException;
use app\index\model\BaseModel;
use app\index\model\DnsConfModel;
use app\index\model\DNSNodeInfoModel;
use app\index\model\DomainModel;
use app\index\model\HdConfigModel;
use app\index\model\PhyConfModel;
use app\index\model\ProxyConfModel;
use app\index\model\ProxyNodeInfoModel;
use app\index\model\SiteModel;
use app\index\model\UserInstanceModel;
use app\index\service\Auth;
use app\index\service\Encrypt;
use think\Log;


class SiteRepository extends BaseRepository
{

    protected $phyConfModel = null;

    protected $dnsConfModel = null;

    protected $hdConfModel = null;

    protected $proxyConfModel = null;

    protected $instanceModel = null;

    protected $dnsNodeInfoModel = null;

    protected $proxyNodeInfoModel = null;

    public function __construct()
    {
        $this->model = new SiteModel();

        $this->phyConfModel = new PhyConfModel();

        $this->dnsConfModel = new DnsConfModel();

        $this->hdConfModel = new HdConfigModel();

        $this->proxyConfModel = new ProxyConfModel();

        $this->instanceModel = new UserInstanceModel();

        $this->dnsNodeInfoModel = new DNSNodeInfoModel();

        $this->proxyNodeInfoModel = new ProxyNodeInfoModel();
    }

    /**
     * 生成幻盾安全域名
     * @param $userDomain
     * @return string
     */
    public static function generateHDDomain($userDomain)
    {
        return $userDomain ? DomainModel::DOMAIN_PREFIX_HD . '.' . $userDomain : '';
    }

    /**
     * 生成域名的CName地址
     * @return string
     */
    public static function generateDomainCName()
    {
        do {
            $cname = strtolower(str_rand(10)) . DomainModel::DOMAIN_CNAME_TAIL;

            // 检查当前CName 是否重复，重复需要重新生成
            $sites = (new static())->getAppByCname($cname);
        } while (!empty($sites));

        return $cname;
    }

    /**
     * 获取HD Dynamic 域名
     * @param $domain
     * @return string
     */
    public static function generateDynamicDomain($domain)
    {
        return $domain . SiteModel::DOMAIN_DYNAMIC_TAIL;
    }

    /**
     * 去掉域名中包含的(HTTP或者HTTPS)Schema信息
     *
     * @param $domain
     * @return mixed
     */
    public static function getDomainWithoutSchema($domain)
    {
        return str_replace(['http://', 'https://'], "", $domain);
    }

    /**
     * 验证域名是否已经配置Txt解析
     * 1.校验失败返回错误代码；2.校验成功返回获取到的TextCode
     *
     * @param $vDomain
     * @return int|string 错误代码|textCode
     */
    public static function domainIsValid($vDomain)
    {
        $command = "nslookup -q=txt $vDomain";
        $retval = array();
        exec($command, $retval, $status);
        if ($status != 0 || count($retval) < 5) {
            return false;
        }

        $arr = explode("\t", $retval[4]);
        if (count($arr) < 2) {
            return false;
        }

        $textCode = trim(explode(" ", $arr[1])[2], '"');

        return $textCode;
    }

    public static function getFormatHDIs($_hd_ip)
    {
        $formatIps = [];
        foreach ($_hd_ip as $value) {
            $formatIps[$value['line']] = $value['ip'];
        }

        return $formatIps;
    }

    /**
     * 根据站点CName获取站点列表
     *
     * @param $cname
     * @return array
     */
    public function getSitesByCname($cname)
    {
        try {
            $filter = [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => compact('cname')]
                        ]
                    ]
                ]
            ];

            return $this->model->esSearch($filter);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取制定CNAME的所有APP
     * @param $cname
     * @return array
     */
    public function getAppByCname($cname)
    {
        try {
            $filter = [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => compact('cname')]
                        ]
                    ]
                ]
            ];

            return $this->model->esSearch($filter);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 检查 Domain 是否存在
     * @param string $domain
     * @return bool
     * @throws \Exception
     */
    public function domainIsExist(string $domain): bool
    {
        $domain = $this->model->esGetById($domain);

        return $domain ? true : false;
    }

    /**
     * 添加新的Domain
     *
     * @param $attributes
     * @return array|mixed
     * @throws \Exception
     */
    public function addDomain($attributes)
    {
        $data = [
            'app_id'         => $attributes['name'],
            'uid'            => Auth::id() ?: 0,
            'name'           => $attributes['name'],
            'type'           => DomainModel::DOMAIN_TYPE_NONE,
            'http'           => $attributes['http'],
            'https'          => $attributes['https'],
            'https_cert'     => '',     // HTTPS 证书
            'https_cert_key' => '',     // HTTPS 证书私钥
            'upstream'       => explode(',', $attributes['upstream']),    // 源站IP
            'status'         => DomainModel::DOMAIN_STATUS_CREATED,
            'text_code'      => str_rand(16),
            'proxy_ip'       => [],
            'cache'          => null,
            'filter'         => null,
            'last_update'    => gmt_withTZ()
        ];
        $result = $this->model->esAdd($data, $attributes['name']);

        return $result['result'] == 'created' ? true : false;
    }

    /**
     * 获取域名的TextCode值
     * @param $domain
     * @return mixed|null
     * @throws \Exception
     */
    public function getDomainTextCode($domain)
    {
        $domain = $this->model->esGetById($domain);
        if (!empty($domain) && !empty($domain['text_code'])) {
            return $domain['text_code'];
        }

        return null;
    }

    /**
     * 更新域名状态
     * @param $domain
     * @param $status
     * @return bool
     * @throws \Exception
     */
    public function updateSiteStatus($domain, $status)
    {
        $data = ['status' => $status, 'last_update' => gmt_withTZ()];

        return (bool)$this->model->esUpdateById($data, $domain);
    }

    /**
     * @param $id
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \Exception
     */
    public function getSiteById($id)
    {
        $site = $this->model->esGetById($id);
        if ($site['type'] == SiteModel::DOMAIN_TYPE_PORT) { // 如果查询结果为应用型，返回空
            return null;
        }

        return $site;
    }

    /**
     * 更新站点配置信息
     *
     * @param array $attributes
     * @param $site
     * @return array|bool
     */
    public function updateSiteConf($attributes, $siteId): bool
    {
        try {
            // upstream需要数组格式，如果是字符串需要进行转化
            is_string($attributes['upstream']) && $attributes['upstream'] = explode(',', $attributes['upstream']);

            $data = [
                'http'        => $attributes['http'],              // HTTP
                'https'       => $attributes['https'],            // HTTPS
                'upstream'    => $attributes['upstream'],      // 上游服务器IP列表
                'last_update' => gmt_withTZ(),
            ];

            // 需要重新下发站点配置


            return (bool)$this->model->esUpdateById($data, $siteId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新域名的信息
     *
     * @param array $attributes
     * @param $site
     * @return array|bool
     * @throws ElasticSearchException
     */
    public function updateSiteProxyConf($attributes = [], $site)
    {
        is_string($site) && $site = $this->getSiteById($site);
        if (isset($attributes['linked_ips'])) {
            array_walk($attributes['linked_ips'], function (&$item) {
                // 检查用户实例是否存在
                $instance = $this->instanceModel->esGetById($item['ddos_id']);
                if (!$instance) {
                    throw new ElasticSearchException('未找到用户实例' . $item['ddos_id'] . '!');
                }

                // 检查实例是否属于当前用户
                if ($instance['uid'] != Auth::id()) {
                    throw new PermissionDenyException('没有权限接入实例' . $item['ddos_id'] . '！');
                }

                // 更新用户实例高防IP接入数量
                foreach ($instance['hd_ip'] as $key => $v) {
                    if ($v['ip'] == $item['ip'] && $v['line'] == $item['line']) {
                        $instance['hd_ip'][$key]['site_count'] = $v['site_count'] + 1;
                    }
                }
                if (!$this->instanceModel->esUpdateById(['hd_ip' => $instance['hd_ip']], $item['ddos_id'])) {
                    throw new ElasticSearchException('更新用户实例' . $item['ddos_id'] . '接入数量失败！');
                }

                $item['instance_id'] = $item['ddos_id'];
                $item['instance_line'] = $instance['instance_line'];
                $item['area'] = $instance['area'];
                unset($item['ddos_id']);
            });
        }

        $data = [
            'name'        => $site['name'],
            'http'        => $attributes['http'] ?? $site['http'],
            'https'       => $attributes['https'] ?? $site['https'],
            'proxy_ip'    => $attributes['linked_ips'] ?? $site['proxy_ip'],
            'cname'       => $attributes['cname'] ?? $site['cname'],
            'status'      => $attributes['status'] ?? $site['status'],
            'type'        => $attributes['type'] ?? $site['type'],
            'cache'       => $attributes['cache'] ?? ($site['cache'] ?? null),
            'filter'      => $attributes['filter'] ?? ($site['filter'] ?? null),
            'last_update' => gmt_withTZ(),
        ];
        if (!$this->model->esUpdateById($data, $site['name'])) {
            throw new ElasticSearchException('更新域名' . $site['name'] . '接入信息失败！');
        }

        return true;
    }

    /**
     * 将站点DNS 配置信息写入ES
     *
     * @param array $attributes
     * @param $domain
     * @return ElasticSearchException|array|bool
     * @throws \Exception
     */
    public function addESDNSConf($attributes = [], $domain)
    {
        $site = $this->getSiteById($domain);
        $dnsConf = [
            'conf_id'     => self::generateDynamicDomain($domain),    // dns_conf Id为站点动态域名
            'name'        => $site['cname'] ?? '',                                     // 站点CNAME
            'type'        => DnsConfModel::TYPE_DYNAMIC,                                // 配置类型 Dynamic - 动态; Static - 静态
            'response'    => [],                                                    // DNS响应列表
            'last_update' => gmt_withTZ()
        ];

        foreach ($attributes['linked_ips'] as $index => $ip) {
            if (!$ddosInstance = $this->instanceModel->esGetById($ip['ddos_id'])) {
                throw new ElasticSearchException('未找到该用户实例' . $ip['ddos_id'] . '!');
            }

            $dnsConf['response'][$ip['line']] = [
                'line' => $ip['line'],
                'ip'   => $ip['ip'],
                'area' => $ddosInstance['area'],
            ];
            $dnsConf['response'] = array_values($dnsConf['response']);
        }

        return $this->dnsConfModel->esAdd($dnsConf, $dnsConf['conf_id']);
    }

    /**
     *
     * 将DNS配置写入Zookeeper
     * @param $input
     * @param $site
     * @return bool
     * @throws ElasticSearchException
     * @throws ZookeeperException
     */
    public function setZKDnsConf($input, $site)
    {
        is_string($site) && $site = $this->getSiteById($site);
        if (!$site) {
            throw new ZookeeperException('未找到站点' . $site['name']);
        }

        $data = ['action' => BaseModel::HD_ZK_ACTION_CREATE, 'name' => $site['cname'], 'response' => []];
        foreach ($input['linked_ips'] as $_ip) {
            // 获取实例的接入区域
            if ($ddosInstance = $this->instanceModel->esGetById($_ip['ddos_id'])) {
                // 一个线路选择一个IP进行接入
                $data['response'][$_ip['line']] = ['line' => $_ip['line'], 'ip' => $_ip['ip'], 'area' => $ddosInstance['area']];
            }
        }

        // 重建IP的索引
        $data['response'] = array_values($data['response']);

        // 获取所有可用的DNS服务器，并将DNS配置信息写入每台机器的ZK配置上
        $dnsNodeList = $this->getDNSNodeList();
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
                throw new ZookeeperException('写入站点：' . $site['name'] . 'ZK DNS配置失败！');
            }
        }

        return true;
    }

    /**
     * 将Proxy 写入Zookeeper
     *
     * @param $site
     * @param bool $https
     * @param null $timestamp
     * @return bool
     * @throws ZookeeperException
     */
    public function setZKProxyConf($site, $https = false, $timestamp = null)
    {
        is_string($site) && $site = $this->getSiteById($site);
        // 生成秒级时间戳，作为ZK节点名称
        $timestamp = micro_timestamp($timestamp);
        // 如果没有设置http， 直接写Https配置
        $site['http'] != '1' && $https = true;
        // 如果需要写入HTTPS信息，且HTTPS证书不存在。不进行HTTPS写入
        if ($https && (empty($site['https_cert_key']) || empty($site['https_cert']))) {
            return true;
        }
        $schema = $https ? 'https://' : 'http://';
        $httpHost = $schema . self::getDomainWithoutSchema($site['name']);

        // 独享型和共享性Proxy 配置信息
        $data = [
            'type'   => BaseModel::ZK_TYPE_WEB,
            'action' => BaseModel::HD_ZK_ACTION_CREATE,
            'data'   => [
                'id'     => $httpHost,
                'http-host' => $httpHost,
                'servers'   => $site['upstream'],
                'location'  => ['~/' => []]
            ]
        ];

        // 将信息循环写入所有高防IP
        $servers = array_column($site['proxy_ip'], 'ip');
        foreach (array_unique($servers) as $ip) {
            $path = '/hd/proxy/' . $ip . '/' . $timestamp;
            // 日志记录写入的proxy 值
            Log::info('Set site proxy with data:' . json_encode(compact('path', 'data')));
            $result = $this->model->zkSetData($path, $data);
            if (!$result) {
                throw new ZookeeperException('写入站点' . $site['name'] . 'ZK Proxy配置失败！');
            }
        }

        // 如果但当前写入为HTTP配置，且该站点设置了Https转发。进行递归写入HTTPS配置
        if (!$https && $site['https'] == 1) {
            // 递归写入HTTPS设置
            $this->setZKProxyConf($site, true, $timestamp);
        }

        return true;
    }

    /**
     * 获取站点信息列表
     * @param array $filter
     * @param int $from
     * @param null $size
     * @return array
     */
    public function domainList($filter = [], $from = 0, $size = null)
    {
        try {
            $_domains = $this->model->esSearch($filter, $from, $size);

            // 对站点字段进行过滤
            $domains = array_map(function ($domain) {
                // 按照前端需要的格式进行返回
                $domain['upstream'] = !empty($domain['upstream']) ? implode(',', $domain['upstream']) : '';
                $domain['id'] = !empty($domain['upstream']) ? $domain['name'] : '';
                $domain['last_update'] = strtotime($domain['last_update']);

                // 证书敏感信息不进行返回
                $domain['https_cert'] = (int)!empty($domain['https_cert']);
                $domain['https_cert_key'] = (int)!empty($domain['https_cert_key']);

                return $domain;
            }, $_domains);

            return $domains;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 删除站点
     *
     * @param $id
     * @return bool true - 删除生成; false - 删除失败
     */
    public function removeDomain($id): bool
    {
        try {
            return (bool)$this->model->esDeleteById($id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 移除DNS Conf中的配置信息
     *
     * @param $site
     * @return bool
     * @throws ElasticSearchException
     */
    public function rmESDNSConf($site)
    {
        // DNS Conf ID为域名生成的Dynamic域名
        $dnsConfId = self::generateDynamicDomain($site['name']);
        if (!$dnsConf = $this->dnsConfModel->esGetById($dnsConfId)) {
            return true;
        }

        if (!$dnsConf = $this->dnsConfModel->esDeleteById($dnsConfId)) {
            throw  new ElasticSearchException('移除站点:' . $site['name'] . 'ES DNS 配置信息失败！');
        }

        return true;
    }

    /**
     * 移除站点的ProxyConf信息
     *
     * @param $site
     * @return bool
     * @throws ElasticSearchException
     */
    public function rmESProxyConf($site)
    {
        is_string($site) && $site = $this->getSiteById($site);
        if (empty($site['proxy_ip'])) {    // 如果hd_ip 不存在，表示ES尚未写入，相应的ZK也尚未写入。不进行移除
            return true;
        }

        $linkedIPs = $site['proxy_ip'];
        foreach ($linkedIPs as $linkedIP) {
            if (!$hdConf = $this->proxyConfModel->esGetById($linkedIP['ip'])) {
                continue;
            }

            foreach ($hdConf['web'] as $index => $item) {
                // 跟据站点域名和当前用户的ID，匹配需要删除的ProxyConf记录
                if ($item['name'] == $site['name'] && $item['uid'] == Auth::id()) {
                    unset($hdConf['web'][$index]);
                }
            }

            if (!$this->proxyConfModel->esUpdateById(['web' => array_values($hdConf['web'])], $linkedIP['ip'])) {
                throw new ElasticSearchException('移除站点：' . $site['name'] . 'ES Proxy 配置信息失败！');
            }
        }

        return true;
    }

    /**
     * 移除ZK中站点的DNS 配置信息
     * @param $site
     * @return bool
     * @throws ZookeeperException
     */
    public function rmZKDNSConf($site)
    {
        $data = ['action' => BaseModel::HD_ZK_ACTION_DELETE, 'name' => $site['cname']];

        // 获取DNS信息
        $dnsNodeList = $this->getDNSNodeList();
        $dnsIps = [];
        array_map(function ($item) use (&$dnsIps) {
            foreach ($item['node_ip'] as $nodeIp) {
                $dnsIps[] = $nodeIp['ip'];
            }
        }, $dnsNodeList);

        foreach (array_unique($dnsIps) as $ip) {
            $path = '/hd/dns/' . $ip . '/' . micro_timestamp();
            if (!$this->model->zkSetData($path, $data)) {
                throw new ZookeeperException('移除站点：' . $site['name'] . 'ZK DNS配置失败！');
            }
        }

        return true;
    }

    /**
     * 移除ZK代理配置
     * @param $site
     * @param bool $https
     * @param null $timestamp
     * @return bool
     * @throws ZookeeperException
     */
    public function rmZKProxyConf($site, $https = false, $timestamp = null)
    {
        is_string($site) && $site = $this->model->esGetById($site);
        if (empty($site['proxy_ip'])) { // 如果hd_ip 不存在，表示ES尚未写入，相应的ZK也尚未写入。不进行移除
            return true;
        }

        $timestamp = micro_timestamp($timestamp);
        !$site['http'] && $https = true;

        // 如果没有https证书，则该站点Https 代理转发尚未写入ZK，不需要移除
        if ($https && (empty($site['https_cert']) || empty($site['https_cert_key']))) {
            return true;
        }

        $schema = $https ? 'https://' : 'http://';
        $httpHost = $schema . self::getDomainWithoutSchema($site['name']);

        // 独享型和共享性Proxy 配置信息
        $data = [
            'type'   => BaseModel::ZK_TYPE_WEB,
            'action' => BaseModel::HD_ZK_ACTION_DELETE,
            'data'   => [
                'id'       => $httpHost,
                'proxy_ip' => array_column($site['proxy_ip'], 'ip')
            ]
        ];

        $servers = array_column($site['proxy_ip'], 'ip');
        foreach ($servers as $ip) {
            $path = '/hd/proxy/' . $ip . '/' . $timestamp;
            if (!$this->model->zkSetData($path, $data)) {
                throw new ZookeeperException('移除站点' . $site['name'] . 'ZK Proxy配置信息失败！');
            }
        }

        // 如果当前移除HTTP配置，则递归移除站点HTTPS配置
        if (!$https && $site['https'] == 1) {
            $this->rmZKProxyConf($site, true, $timestamp);
        }

        return true;
    }

    /**
     * 写入ES的代理信息
     * @param $site
     * @return bool
     * @throws ElasticSearchException
     */
    public function setESProxyConf($site)
    {
        is_string($site) && $site = $this->model->esGetById($site);
        // 需要写入的配置
        $webData = [
            'uid'            => Auth::id(),                // 用户ID
            'name'           => $site['name'],         // 站点
            'cname'          => $site['cname'],          // 站点CName
            'http'           => $site['http'],            // 是否是HTTP
            'https'          => $site['https'],          // 是否是HTTPS
            'upstream'       => $site['upstream'],    // 源站IP数组
            'https_cert'     => $site['https_cert'],    // HTTPS 证书（已进行RAS私钥加密）
            'https_cert_key' => $site['https_cert_key'],             // HTTPS 证书私钥（已进行RAS私钥加密）
            'cache'          => [                        // 缓存配置
                'static'    => $this->convertCacheExpireTime($site['cache']['static'] ?? null),  // 静态资源（分钟）
                'html'      => $this->convertCacheExpireTime($site['cache']['html'] ?? null),     // 静态页面（分钟）
                'index'     => $this->convertCacheExpireTime($site['cache']['index'] ?? null),   // 首页（分钟）
                'directory' => $this->convertCacheExpireTime($site['cache']['directory'] ?? null), // 目录（分钟）
                'whitelist' => $this->processSiteCacheWhiteList($site['cache']['whitelist'] ?? []),  // 缓存策略列表
                'blacklist' => $site['cache']['blacklist'] ?? [],              // 缓存黑名单列表
            ],
            'filter'         => [                       // 黑白名单配置
                'url_whitelist' => $site['filter']['url_whitelist'] ?? [],          // 网址白名单
                'url_blacklist' => $site['filter']['url_blacklist'] ?? [],          // 网址黑名单
                'ip_whitelist'  => $site['filter']['ip_whitelist'] ?? [],           // IP白名单
                'ip_blacklist'  => $site['filter']['ip_blacklist'] ?? [],           // IP黑名单
            ]
        ];

        foreach ($site['proxy_ip'] as $item) {
            if (!$hdConfig = $this->proxyConfModel->esGetById($item['ip'])) {
                // 当前高防节点记录不存在时，进行创建
                $hdConfig = [
                    'ip'          => $item['ip'],
                    'web'         => [$webData],
                    'forwarding'  => [],
                    'last_update' => gmt_withTZ()
                ];
                $result = $this->proxyConfModel->esAdd($hdConfig, $item['ip']);
            } else {
                // 如果当前高仿节点Proxy Conf记录已经存在，判断当前站点记录是否存在
                $hdConfig['web'] = $hdConfig['web'] ?? [];
                $key = array_search($site['name'], array_column($hdConfig['web'], 'name'));
                if ($key !== false) {
                    $hdConfig['web'][$key] = $webData;
                } else {
                    $hdConfig['web'][] = $webData;
                }
                $result = $this->proxyConfModel->esUpdateById(['web' => $hdConfig['web'], 'last_update' => gmt_withTZ()], $item['ip']);
            }

            if (!$result) {
                throw new ElasticSearchException('配置站点' . $site['name'] . 'ES Proxy配置信息失败！');
            }

        }

        return true;
    }

    /**
     * 获取可用的DNS服务器信息
     * @return array
     */
    public function getDNSNodeList()
    {
        try {
            return $this->dnsNodeInfoModel->esSearch([]);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 更新用户实例中的域名接入数量
     *
     * @param $site
     * @return array|bool
     * @throws ElasticSearchException
     */
    public function resetUserInstanceSiteCount($site)
    {
        /// 获取该域名下接入的所有实例
        foreach ($site['proxy_ip'] as $proxyIp) {
            if (!$ddosInstance = $this->instanceModel->esGetById($proxyIp['instance_id'])) {
                throw new ElasticSearchException('未找到实例' . $proxyIp['instance_id']);
            }

            // 根据该站点接入的IP和线路，更新对应的接入数量
            foreach ($ddosInstance['hd_ip'] as $key => $hdIP) {
                if ($proxyIp['line'] == $hdIP['line'] && $proxyIp['ip'] == $hdIP['ip']) {
                    $ddosInstance['hd_ip'][$key]['site_count'] = ($hdIP['site_count'] ?? 0) > 0 ? $hdIP['site_count'] - 1 : 0;
                }
            }

            // 更新用户实例的接入数量
            if (!$this->instanceModel->esUpdateById(['hd_ip' => $ddosInstance['hd_ip']], $proxyIp['instance_id'])) {
                throw new ElasticSearchException('更新实例' . $proxyIp['instance_id'] . '接入数失败！');
            }
        }

        return true;
    }

    /**
     * 更新站点的HTTPS证书
     *
     * @param $site
     * @param null $certificate
     * @param null $certificateKey
     * @return array|bool
     */
    public function updateSiteCertificate($site, $certificate = null, $certificateKey = null)
    {
        try {
            // 对证书信息进行RSA 私钥加密
            $certificate = $certificate ? Encrypt::instance()->encode($certificate, true) : null;
            $certificateKey = $certificateKey ? Encrypt::instance()->encode($certificateKey, true) : null;

            $data = [
                'https_cert'     => $certificate ?? ($site['https_cert'] ?? ''),
                'https_cert_key' => $certificateKey ?? ($site['https_cert_key'] ?? '')
            ];
            if (!$this->model->esUpdateById($data, $site['name'])) {
                return false;
            }

            // 如果是第一次配置证书，将写入一条Https代理转发配置到高防节点对应的Zookeeper
            if (empty($site['https_cert']) || empty($site['https_cert_key'])) {
                return $this->setZKProxyConf($site['name'], true);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取站点的HTTPS证书
     * @param $site
     * @return array|null|string
     */
    public function getSiteCerts($site)
    {
        try {

            $cert = Encrypt::instance()->decode(base64_decode($site['https_cert']), true);
            $certKey = Encrypt::instance()->decode(base64_decode($site['https_cert_key']), true);
            $cert = [
                'site'            => $site['name'],
                'certificate'     => $cert ?? '',
                'certificate_key' => $certKey ?? ''
            ];

            return $cert;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $ids
     * @return array|bool
     */
    public function bundleDelete($ids)
    {
        try {
            return $this->model->esBulkDelete($ids);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取高仿的列表
     *
     * @param array $filter 筛选条件
     * @return array
     */
    public function getDDoSList($filter = [])
    {
        try {
            return $this->proxyNodeInfoModel->esSearch($filter);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 设置站点缓存有效期
     *
     * @param $_cacheExpire
     * @param $site
     * @return bool
     * @throws \Exception
     */
    public function setSiteCacheExpire($_cacheExpire, $site)
    {
        $cacheExpire = [
            'static'    => $_cacheExpire['static_expire'],
            'html'      => $_cacheExpire['html_expire'],
            'index'     => $_cacheExpire['index_expire'],
            'directory' => $_cacheExpire['directory_expire']
        ];

        return (bool)$this->model->esUpdateById(['cache' => $cacheExpire], $site['name']);
    }

    /**
     * 更新Proxy conf 缓存配置信息
     *
     * @param $_cacheExpire
     * @param $site
     * @return bool
     * @throws \Exception
     */
    public function setSiteProxyCache($_cacheExpire, $site): bool
    {
        $siteProxyIps = $site['proxy_ip'];
        foreach ($siteProxyIps as $siteProxyIp) {
            $proxyConf = $this->proxyConfModel->esGetById($siteProxyIp['ip']);
            if (!$proxyConf) {
                continue;
            }

            foreach ($proxyConf['web'] as &$item) {
                // 站点域名和用户ID确定当前站点的高防节点的配置信息
                if ($item['name'] != $site['name'] || $item['uid'] != Auth::id()) {
                    continue;
                }

                $item['cache'] = [
                    'static'    => $this->convertCacheExpireTime($_cacheExpire['static_expire']),
                    'html'      => $this->convertCacheExpireTime($_cacheExpire['html_expire']),
                    'index'     => $this->convertCacheExpireTime($_cacheExpire['index_expire']),
                    'directory' => $this->convertCacheExpireTime($_cacheExpire['directory_expire'])
                ];
            }

            // 更新 Last_update 字段
            $proxyConf['last_update'] = gmt_withTZ();
            if (!$this->proxyConfModel->esUpdateById($proxyConf, $siteProxyIp['ip'])) {
                return false;
            }
        }

        return true;
    }

    /**
     *
     * 获取站点的缓存设置
     * @param $site
     * @return array
     */
    public function getSiteCacheExpire($site): array
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);

            return [
                'static_expire'    => $site['cache']['static'] ?? "0:minute",
                'html_expire'      => $site['cache']['html'] ?? "0:minute",
                'index_expire'     => $site['cache']['index'] ?? "0:minute",
                'directory_expire' => $site['cache']['directory'] ?? "0:minute"
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 添加站点缓存策略
     * @param $_data
     * @param $site
     * @return bool
     */
    public function addSiteCacheWhiteList($_data, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);

            // 如果未设置过该站点的缓存策略，whitelist有可能不存在，需要添加
            $site['cache']['whitelist'] = $site['cache']['whitelist'] ?? [];
            $site['cache']['whitelist'][] = [
                'keyword' => $_data['keyword'],
                'time'    => $_data['expire'],
            ];

            return (bool)$this->model->esUpdateById($site, $site['name']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取站点缓存白名单
     * @param $site
     * @return array
     */
    public function getSiteCacheWhiteList($site)
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);

            return (array)array_values($site['cache']['whitelist']) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 检查当前缓存白名单关键字是否已经添加过
     *
     * @param $keyword
     * @param $site
     * @return bool
     */
    public function isWhiteCacheKeywordExist($keyword, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);
            // 取得已经添加过的缓存关键字
            $cache = $site['cache'] ?? [];
            $whiteList = $cache['whitelist'] ?? [];
            $keywords = array_column($whiteList, 'keyword');

            return (bool)in_array($keyword, $keywords);
        } catch (\Exception $e) {
            return true;
        }
    }

    /**
     * 移除缓存白名单关键字
     * @param $keywords
     * @param $site
     * @return bool
     */
    public function rmSiteCacheWhiteList($keywords, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);
            // 删除时有可能whitelist 未初始化，未初始化时初始化为空数组
            $site['cache']['whitelist'] = $site['cache']['whitelist'] ?? [];
            foreach ($site['cache']['whitelist'] as $key => $item) {
                if (in_array($item['keyword'], $keywords)) {
                    unset($site['cache']['whitelist'][$key]);
                }
            }
            $site['cache']['whitelist'] = array_values($site['cache']['whitelist']);

            return (bool)$this->model->esUpdateById($site, $site['name']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 转换缓存有效期为分钟
     *
     * @param $_cacheTime
     * @return float|int
     */
    private function convertCacheExpireTime($_cacheTime)
    {
        try {
            list($num, $unit) = explode(':', $_cacheTime);
            switch ($unit) {
                case 'day': // 天
                    $cacheTime = $num * 24 * 60;
                    break;
                case 'hour':    // 小时
                    $cacheTime = $num * 60;
                    break;
                default:    // 分钟
                    $cacheTime = $num;
            }

            return (int)$cacheTime;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 验证站点的CName值是否符合正确解析
     *
     * @param $domain
     * @param $cname
     * @return bool
     */
    public function cNameVerify($domain, $cname): bool
    {
        $command = 'nslookup ' . $domain;
        exec($command, $output, $ret);

        /**
         * $output example:
         * Server:         10.0.2.3
         * Address:        10.0.2.3#53
         *
         * Non-authoritative answer:
         * abc.test.com   canonical name = 7fdb8e0817a54131.vedasec.net.
         * Name:   7fdb8e0817a54131.vedasec.net
         * Address: 52.79.51.109
         */
        if ($ret != 0 || count($output) < 7) {
            return false;
        }
        list($key, $val) = explode("\t", $output[5]);

        return (bool)$val == $cname;
    }

    /**
     * 更新站点信息
     *
     * @param $attributes
     * @param $site
     * @return bool
     * @throws \Exception
     */
    public function updateSiteInfo($attributes, $site)
    {
        is_string($site) && $site = $this->getSiteById($site);
        // 处理高防IP数据
        if (isset($attributes['linked_ips'])) {
            array_walk($attributes['linked_ips'], function (&$item) {
                // 检查用户实例是否存在
                if (!$instance = $this->instanceModel->esGetById($item['ddos_id'])) {
                    throw new ElasticSearchException('未找到要接入的用户实例！', REP_CODE_SOURCE_NOT_FOUND);
                }
                // 检查实例是否属于当前用户
                if ($instance['uid'] != Auth::id()) {
                    throw new PermissionDenyException('没有权限接入此实例！', REP_CODE_ILLEGAL_OPERATION);
                }
                $item['instance_id'] = $item['ddos_id'];
                $item['instance_line'] = $instance['instance_line'];
                $item['area'] = $instance['area'];
                unset($item['ddos_id']);
            });
        }

        $data = [
            'http'        => $attributes['http'] ?? $site['http'],      // 是否http
            'https'       => $attributes['https'] ?? $site['https'],   // 是否https
            'cname'       => $attributes['cname'] ?? ($site['cname'] ?? ''),   // 站点CNAME
            'status'      => $attributes['status'] ?? $site['status'], // 站点状态
            'type'        => $attributes['type'] ?? $site['type'],      // 站点类型
            'upstream'    => $attributes['upstream'] ?? $site['upstream'],      // 源站IP
            'proxy_ip'    => $attributes['linked_ips'] ?? $site['proxy_ip'],      // 代理IP地址
            'last_update' => gmt_withTZ(),
        ];

        // 设置站点的Filter值
        if (isset($attributes['filter'])) {
            $data['filter'] = [   //黑白名单
                'url_whitelist' => $attributes['filter']['url_whitelist'] ?? ($site['filter']['url_whitelist'] ?? []), // 网址白名单
                'url_blacklist' => $attributes['filter']['url_blacklist'] ?? ($site['filter']['url_blacklist'] ?? []),  // 网站黑名单
                'ip_whitelist'  => $attributes['filter']['ip_whitelist'] ?? ($site['filter']['ip_whitelist'] ?? []), //IP白名单
                'ip_blacklist'  => $attributes['filter']['ip_blacklist'] ?? ($site['filter']['ip_blacklist'] ?? []), // IP 黑名单
            ];
        }

        return (bool)$this->model->esUpdateById($data, $site['name']);
    }

    /**
     * @param $attributes
     * @param string $action add 新增| cut 减少
     * @return bool
     */
    public function updateUserInstanceSiteCount($attributes, $action = 'add')
    {
        try {

            foreach ($attributes['linked_ips'] as $linkedIp) {
                if (!$instance = $this->instanceModel->esGetById($linkedIp['ddos_ip'])) {
                    throw  new ElasticSearchException('未找到该用户实例！');
                }

                // 检查实例是否属于当前用户
                if ($instance['uid'] != Auth::id()) {
                    throw new PermissionDenyException('没有权限接入此实例！');
                }

                // 更新用户实例高防站点接入数量
                foreach ($instance['hd_ip'] as $key => &$v) {
                    if ($v['ip'] == $linkedIp['ip'] && $v['line'] == $linkedIp['line']) {
                        if ($action == 'add') { // 增加实例站点接入数
                            $v['site_count'] = $v['site_count'] + 1;
                        } else {    // 减少实例站点接入数
                            $v['site_count'] = ($v['site_count'] ?? 0) > 0 ? $v['site_count'] - 1 : 0;
                        }
                    }
                }

                if (!$this->instanceModel->esUpdateById(['hd_ip' => $instance['hd_ip']], $linkedIp['ddos_id'])) {
                    throw new ElasticSearchException('更新用户实例接入数量失败！');
                }
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取站点总数
     * @param array $filter
     * @return int
     */
    public function getSiteCount($filter = [])
    {
        try {
            return (int)$this->model->esCount($filter);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 获取站点缓存黑名单
     *
     * @param $site
     * @return array
     */
    public function getSiteCacheBlackList($site)
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);

            return (array)array_values($site['cache']['blacklist']) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function isBlackCacheKeywordExist($keyword, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);
            // 取得已经添加过的缓存关键字
            $cache = $site['cache'] ?? [];
            $blackList = $cache['blacklist'] ?? [];

            return (bool)in_array($keyword, $blackList);
        } catch (\Exception $e) {
        }
    }

    /**
     * 移除站点缓存黑名单关键字
     * @param $keywords
     * @param $site
     * @return bool
     */
    public function rmSiteCacheBlackList($keywords, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);
            // 删除时有可能blacklist 未初始化，未初始化时初始化为空数组
            $site['cache']['blacklist'] = $site['cache']['blacklist'] ?? [];
            foreach ($site['cache']['blacklist'] as $key => $item) {
                if (in_array($item, $keywords)) {
                    unset($site['cache']['blacklist'][$key]);
                }
            }
            $site['cache']['blacklist'] = array_values($site['cache']['blacklist']);

            return (bool)$this->model->esUpdateById($site, $site['name']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function addSiteCacheBlackList($_data, $site): bool
    {
        try {
            is_string($site) && $site = $this->getSiteById($site);

            // 如果未设置过该站点的缓存策略，whitelist有可能不存在，需要添加
            $site['cache']['blacklist'] = $site['cache']['blacklist'] ?? [];
            $site['cache']['blacklist'][] = $_data['keyword'];

            return (bool)$this->model->esUpdateById($site, $site['name']);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function processSiteCacheWhiteList($siteCacheWhiteList = [])
    {
        foreach ($siteCacheWhiteList as &$item) {
            $item['time'] = $this->convertCacheExpireTime($item['time']);
        }

        return $siteCacheWhiteList;
    }

}