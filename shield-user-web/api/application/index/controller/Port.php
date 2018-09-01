<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/28
 * Time: 17:27
 */

namespace app\index\controller;


use app\index\model\PortModel;
use app\index\model\SiteModel;
use app\index\repository\PortRepository;
use app\index\repository\SiteRepository;
use app\index\service\Auth;
use app\index\traits\CheckLogin;
use think\Request;

class Port extends BaseController
{
    use CheckLogin;
    protected $repository = null;

    public function __construct(Request $request)
    {
        $this->repository = new PortRepository();
        $this->validator = new \app\index\validate\Port();  //构造Site验证器

        parent::__construct($request);
    }

    protected $beforeActionList = [
        'checkLogin'
    ];

    /**
     * @SWG\Get(
     *      path="/port",
     *      tags={"Port 非网站防护"},
     *      summary="获取用户的非网站防护列表",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="应用ID",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="proxy_ip",
     *          in="query",
     *          description="高防IP",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="proxy_port",
     *          in="query",
     *          description="转发端口",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="server_ip",
     *          in="query",
     *          description="源站IP",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="server_port",
     *          in="query",
     *          description="源站端口",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="_from",
     *          in="query",
     *          description="查询范围开始",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="_size",
     *          in="query",
     *          description="查询数量",
     *          type="integer"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="",
     *          ref="#/definitions/PortList"
     *      )
     * )
     *
     * 域名状态码：
     * 0：待审核 1：待接入 2：正在接入 3：已接入，未修改DNS解析
     * 4：正常 5：接入错误 6：正在删除 7：删除异常
     * @return array     $domainList 返回结果数组
     * @throws \Exception
     */
    public function index()
    {

        $from = input('_from', 0);
        $size = input('_size', null);

        $must = [
            ['term' => ['type' => SiteModel::DOMAIN_TYPE_PORT]],
            ['term' => ['uid.keyword' => Auth::id()]]   // 根据当前用户查询
        ];

        // 根据应用ID进行筛选
        $id = input('id', null);
        if ($id !== null && $id != '') {
            $must[] = ['term' => ['app_id.keyword' => $id]];
        }

        // 根据高仿IP筛选
        $proxyIp = input('proxy_ip', null);
        if ($proxyIp !== null && $proxyIp != '') {
            $must[] = ['term' => ['proxy_ip.ip.keyword' => $proxyIp]];
        }

        // 根据转发端口
        $proxyPort = input('proxy_port', null);
        if ($proxyPort !== null && $proxyPort != '') {
            $must[] = ['term' => ['proxy_port.keyword' => $proxyPort]];
        }

        // 根据源站IP
        $serverIp = input('server_ip', null);
        if ($serverIp !== null && $serverIp != '') {
            $must[] = ['term' => ['server_ip.keyword' => $serverIp]];
        }

        // 源站端口
        $serverPort = input('server_port', null);
        if ($serverPort !== null && $serverPort != '') {
            $must[] = ['term' => ['server_port.keyword' => $serverPort]];
        }

        $filter = [
            'query' => ['bool' => ['must' => $must]],
            "sort"  => [["last_update" => ["order" => "desc"]]],
        ];
        $list = $this->repository->portList($filter, $from, $size);
        $total = $this->repository->getListTotal($filter);

        return Finalsuccess(compact('list', 'total'));
    }

    /**
     *
     * @SWG\Post(
     *      path="/port",
     *      tags={"Port 非网站防护"},
     *      summary="用户添加非网站防护",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="Port 信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"server_ips": "192.168.10.11,192.168.10.11", "server_port": 1232, "proxy_ips": {{"line": "8",
     *     "ip": "192.168.1.8","ddos_id": "ddos-ofanbvo"},{"line": "1", "ip": "192.168.1.1","ddos_id": "ddos-ofanbvo"}},
     *     "proxy_port": 1234, "protocol":"TCP"}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 添加成功| !=0 添加失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     *
     * 用户添加域名
     * @param Request $request
     * @return string    json     返回结果
     * @throws \Exception
     */
    public function create(Request $request)
    {
        $data = input();
        // 进行非网站防护校验
        if (!$this->validator->scene('add_port')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        // 检查当前非网站防护是否存在
        if ($this->repository->portIsExist($data['proxy_ips'], $data['proxy_port'])) {
            return Finalfail(REP_CODE_PARAMS_INVALID, '端口已存在，不能重复添加！');
        }

        // 添加新应用型防护
        $data = $request->only(['server_ips', 'server_port', 'proxy_port', 'proxy_ips', 'protocol']);
        if (!$port = $this->repository->addPort($data)) {
            return Finalfail(REP_CODE_DB_ERROR, '新应用配置保存失败！');
        }

        // 更新用户实例的应用接入数
        if (!$this->repository->addUserInstancePortCount($port['_id'])) {
            return Finalfail(REP_CODE_ES_ERROR, '更新实例接入信息失败！');
        }

        // 在hd_conf 中加入新的转发配置
        if (!$this->repository->setESProxyConf($port['_id'])) {
            return Finalfail(REP_CODE_ES_ERROR, 'HD Conf配置保存失败！');
        }

        // 将新转发配置写入ZK
        if (!$this->repository->setZKProxyConf($port['_id'])) {
            return Finalfail(REP_CODE_DB_ERROR, 'ZK error.');
        }

        if (!$this->repository->updatePort(['status' => PortModel::PORT_STATUS_NORMAL], $port['_id'])) {
            return Finalfail(REP_CODE_ES_ERROR, '更新域名状态失败！');
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Put(
     *      path="/port/{id}",
     *      tags={"Port 非网站防护"},
     *      summary="更新应用信息",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="应该信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"server_ips":"192.9.43.1,192.9.43.2","server_port":"9431","proxy_port":"9432","proxy_ips":
     *     {{"line":"8","ip":"192.168.1.8","ddos_id":"ddos-l9umrps"},{"line":"1","ip":"192.168.1.1","ddos_id":"ddos-l9umrps"}},
     *     "protocol":"TCP"}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 更新成功| !=0 更新失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * @param $id
     * @return string
     */
    public function update($id)
    {
        $data = request()->only(['server_ips', 'server_port', 'proxy_port', 'proxy_ips', 'protocol']);
        if (!$this->validator->scene('update')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }

        $data['server_ip'] = explode(',', $data['server_ips']);
        if (!$this->repository->updatePort($data, $port)) {
            return Finalfail(REP_CODE_ES_ERROR, '应用配置更新失败！');
        }

        // 写入ES代理配置
        if (!$this->repository->setESProxyConf($id)) {
            return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf配置保存失败！');
        }

        // 写入ZK代理配置
        if (!$this->repository->setZKProxyConf($id)) {
            return Finalfail(REP_CODE_DB_ERROR, 'ZK error.');
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Get(
     *      path="/port/{id}",
     *      tags={"Port 非网站防护"},
     *      summary="获取应用防护详情",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="应用防护 Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"status":1,"uid":"test@veda.com","app_id":"app-ddos-kC0c5EP",
     *     "type":3,"proxy_ip":{{"instance_id":"ddos-iary7q9","instance_line":"8","area":13,"line":"8","ip":"192.17.54.1"}},
     *     "protocol":"TCP","proxy_port":"10392","server_port":"10391","server_ip":"127.10.39.1,127.10.39.2","name":null,
     *     "cname":null,"filter":null,"last_update":"2018-04-28T02:40:05.000Z"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function show($id)
    {
        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }
        // 将 Server_ip 数组转换为','分割字符串
        $port['server_ip'] = implode(',', $port['server_ip']);
        $port['name'] = $port['name'] ?? null;
        $port['cname'] = $port['cname'] ?? null;

        return Finalsuccess(['data' => $port]);
    }

    /**
     * @SWG\Delete(
     *      path="/port/{id}",
     *      tags={"Port 非网站防护"},
     *      summary="用户删除非网站防护",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="Port Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 删除成功| !=0 删除失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * 用户删除站点
     * @param $id
     * @return string    json     返回结果
     */
    public function destroy($id)
    {
        if ($this->validator->scene('del_port')->check(input())) {
            return Finalfail(11000, $this->validator->getError());
        }

        // 检查非网站防护是否存在
        $port = $this->repository->getPortById($id);
        if (!$port) {
            return Finalfail(10016, 'Port not exist.');
        }

        if ($port['status'] == PortModel::PORT_STATUS_NORMAL) { // 未正常接入的域名，不进行移除
            // 将新转发配置写入ZK
            if (!$this->repository->rmZKProxyConf($port)) {
                return Finalfail(REP_CODE_DB_ERROR, 'ZK error.');
            }

            // 移除Proxy Conf中的配置信息
            if (!$this->repository->rmESProxyConf($port)) {
                return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf配置更新失败！');
            }

            if (!$this->repository->cutUserInstancePortCount($port)) {
                return Finalfail(REP_CODE_ES_ERROR, '用户实例接入信息更新失败！');
            }
        }

        $delResult = $this->repository->delPortById($id);
        if (!$delResult) {
            $this->repository->updatePort(['status' => PortModel::PORT_STATUS_DELETE_ERR], $id);

            return Finalfail(20001, 'DB error.');
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/linkup-update",
     *      tags={"Port 非网站防护"},
     *      summary="更新应用接入信息",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="接入信息",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"proxy_ips":{{"line":"8","ip":"192.9.2.1","ddos_id":"ddos-kzxrdnp"},{"line":"1",
     *     "ip":"192.168.9.20","ddos_id":"ddos-rbpavgc"}},"protocol":"TCP"}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 更新成功| !=0 更新失败！",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":""}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function updateLinkUp($id)
    {
        $data = request()->only(['proxy_ips', 'protocol']);
        if (!$this->validator->scene('update_linkup')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }
        //------------------------- 移除旧的配置 START -------------------------------
        // 获取应用详情
        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }
        // 移除ZK Proxy Conf信息
        if (!$this->repository->rmZKProxyConf($port)) {
            return Finalfail(REP_CODE_DB_ERROR, 'ZK error.');
        }
        // 移除ES Proxy Conf 配置信息
        if (!$this->repository->rmESProxyConf($port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf配置更新失败！');
        }
        // 削减用户实例的端口接入数量
        if (!$this->repository->cutUserInstancePortCount($port)) {
            return Finalfail(REP_CODE_ES_ERROR, '用户实例接入信息更新失败！');
        }

        //------------------------- 移除旧的配置 END -------------------------------
        // 更新站点的接入信息
        if (!$this->repository->updatePort($data, $id)) {
            return Finalfail(REP_CODE_ES_ERROR, '更新应用接入配置失败！');
        }

        //------------------------- 写入新的配置 START -------------------------------
        // 重新获取Port信息
        $port = $this->repository->getPortById($id);
        // 更新用户实例接入数量
        if (!$this->repository->addUserInstancePortCount($port)) {
            return Finalfail(REP_CODE_ES_ERROR, '更新实例接入信息失败！');
        }
        // 写入ES Proxy Conf配置
        if (!$this->repository->setESProxyConf($port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'HD Conf配置保存失败！');
        }
        // 写入ZK Proxy Conf配置
        if (!$this->repository->setZKProxyConf($port)) {
            return Finalfail(REP_CODE_DB_ERROR, 'ZK Proxy Conf 写入失败！');
        }

        //------------------------- 写入新的配置 END -------------------------------

        return Finalsuccess();
    }

    /**
     * @SWG\Delete(
     *      path="/port/bundle/delete",
     *      tags={"Port 非网站防护"},
     *      summary="应用批量删除",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="应用ID数组",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ids": {"app-ddos-zltXu3G", "app-ddos-f0IxRO8"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 删除成功| !=0 删除失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * 应用批量删除
     * @return string
     */
    public function bundleDelete()
    {
        $data = input();
        if (!$this->validator->scene('bundle_delete')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        foreach ($data['ids'] as $id) {
            // 依次移除对应应用的ES和ZK配置
            $result = ($this->destroy($id))->getData();
            if ($result['errcode'] != 0) {
                return Finalfail($result['errcode'], $result['errmsg']);
            }
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/conf-update",
     *      tags={"Port 非网站防护"},
     *      summary="更新应用信息",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="应用信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"server_ips":"192.9.56.1,192.9.56.1","server_port":"958"}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 更新成功| !=0 更新失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * @param $id
     * @return string
     * 更新应用基础配置
     */
    public function updateConf($id)
    {
        $data = request()->only(['server_ips', 'server_port']);
        if (!$this->validator->scene('update_conf')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }

        // 判断是否需要更新应用信息
        if ($data['server_port'] != $port['server_port'] || $data['server_ips'] != implode(',', $port['server_ip'])) {
            // 更新应用信息
            $data = ['server_port' => $data['server_port'], 'server_ip' => explode(',', $data['server_ips'])];
            if (!$this->repository->updatePort($data, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '应用配置更新失败！');
            }

            // 写入ES代理配置
            if (!$this->repository->setESProxyConf($id)) {
                return Finalfail(REP_CODE_ES_ERROR, 'HD Conf配置保存失败！');
            }

            // 写入ZK代理配置
            if (!$this->repository->setZKProxyConf($id)) {
                return Finalfail(REP_CODE_DB_ERROR, 'ZK error.');
            }
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Get(
     *      path="/port/{id}/ip-white-black-list",
     *      tags={"Port 非网站防护黑白名单"},
     *      summary="获取用户的应用防护黑白名单",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"ipBlacklist":{"127.0.0.1"},"ipWhitelist":{"127.0.0.1","127.0.0.2"}}}
     *          )
     *      )
     * )
     *
     * 获取应用该黑白名单
     *
     * @param $id
     * @return string
     */
    public function getIpWhiteBlackList($id)
    {
        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }
        $ipBlacklist = $port['filter']['ip_blacklist'] ?? [];
        $ipWhitelist = $port['filter']['ip_whitelist'] ?? [];

        return Finalsuccess(['data' => compact('ipBlacklist', 'ipWhitelist')]);
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/ip-black-list",
     *      tags={"Port 非网站防护黑白名单"},
     *      summary="更新应用IP黑名单",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="应用IP黑名单",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ipBlacklist":{"192.168.1.1","192.168.1.2"}}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 设置成功| !=0 设置失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * 设置应用黑白名单
     *
     * @param $id
     * @return string
     */
    public function setIpBlacklist($id)
    {
        $ipBlacklist = request()->param('ipBlacklist/a');
        if (!$this->validator->scene('ip_black_list')->check(input())) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }
        // 设置应用黑名单
        $ipBlacklist = array_unique($ipBlacklist);
        if (!$this->repository->updatePort(['filter' => ['ip_blacklist' => $ipBlacklist]], $port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'IP黑名单设置失败！');
        }

        // 更新Proxy Conf中的记录
        $port = $this->repository->getPortById($id);
        if (!$this->repository->setESProxyConf($port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf更新失败！');
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/ip-white-list",
     *      tags={"Port 非网站防护黑白名单"},
     *      summary="更新应用IP白名单",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="应用IP白名单",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ipWhitelist":{"192.168.1.1","192.168.1.2"}}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 设置成功| !=0 设置失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * 设置应用黑白名单
     *
     * @param $id
     * @return string
     */
    public function setIpWhitelist($id)
    {
        $ipWhitelist = request()->param('ipWhitelist/a');
        if (!$this->validator->scene('ip_white_list')->check(input())) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if (!$port = $this->repository->getPortById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
        }

        // 设置应用黑名单
        $ipWhitelist = array_unique($ipWhitelist);
        if (!$this->repository->updatePort(['filter' => ['ip_whitelist' => $ipWhitelist]], $port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'IP白名单设置失败！');
        }

        // 更新Proxy Conf中的记录
        $port = $this->repository->getPortById($id);
        if (!$this->repository->setESProxyConf($port)) {
            return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf更新失败！');
        }

        return Finalsuccess();
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/cname-generate",
     *      tags={"Port 非网站防护CNAME自动调度"},
     *      summary="生成应用CNAME",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="原站域名",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"domain":"abc.com"}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 生成成功| !=0 生成失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","cname":"3a4tbpna0e.hd.vedasec.net"}
     *          )
     *      )
     * )
     * @param $id
     * @param Request $request
     * @return string
     */
    public function cnameGenerate($id, Request $request)
    {
        try {
            $domain = $request->param('domain');
            if (!$this->validator->scene('generate_cname')->check($request->param())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$port = $this->repository->getPortById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }
            $cname = $this->repository->generatePortCname($domain, $port);

            return Finalsuccess(compact('cname'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, 'CNAME生成失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/port/{id}/cname-active",
     *      tags={"Port 非网站防护CNAME自动调度"},
     *      summary="启用应用CNAME自动调度",
     *     @SWG\Parameter(
     *         name="domain",
     *         in="body",
     *         description="CNAME配置信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"domain":"abc.com","cname":"3a4tbpna0e.hd.vedasec.net"}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 启用成功| !=0 启用失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * @param $id
     * @param Request $request
     * @return string
     */
    public function cnameActive($id, Request $request)
    {
        try {
            $data = $request->only(['domain', 'cname']);
            if (!$this->validator->scene('cname_active')->check($request->param())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$port = $this->repository->getPortById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }

            if ($data['domain'] != $port['name'] && $data['cname'] != $port['cname']) {
                // 当CNAME接入有更新时，执行配置更新操作
                if (!config('app_debug')) {
                    // 校验域名的Cname是否正确解析
                    if (!(new SiteRepository())->cNameVerify($data['domain'], $data['cname'])) {
                        return Finalfail(REP_CODE_ILLEGAL_OPERATION, 'CNAME未正确解析！');
                    }
                }
                // 如果该应用已接入，需要移除旧的DNS配置
                if (!empty($port['cname']) && !empty($port['name'])) {
                    // 移除旧的ES DNS配置
                    if (!$this->repository->rmESDNSConf($port)) {
                        return Finalfail('移除旧的ES DNS配置失败！');
                    }
                    // 移除旧的ZK DNS配置
                    if (!$this->repository->rmZKDNSConf($port)) {
                        return Finalfail('移除旧的ZK DNS配置失败！');
                    }
                }

                if (!$this->repository->updatePort($data, $port)) {
                    return Finalfail(REP_CODE_ES_ERROR, '更新应用信息失败！');
                }
                // 写入新的ES DNS配置
                $port = $this->repository->getPortById($id);
                if (!$this->repository->setESDNSConf($port, $port)) {
                    return Finalfail(REP_CODE_ES_ERROR, '设置ES DNS配置信息失败！');
                }
                // 写入新的ZK DNS配置
                if (!$this->repository->setZKDNSConf($port)) {
                    return Finalfail(REP_CODE_ZK_ERROR, '设置ZK DNS配置信息失败！');
                }
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, 'CNAME自动调度启用失败！');
        }
    }

}