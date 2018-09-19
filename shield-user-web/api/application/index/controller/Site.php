<?php

namespace app\index\controller;

use app\index\model\PortModel;
use app\index\model\SiteModel;
use app\index\model\UserInstanceModel;
use app\index\repository\SiteRepository;
use app\index\service\Auth;
use app\index\traits\ControllerGuard;
use app\index\validate\Site as SiteValidator;
use think\Request;

class Site extends BaseController
{

    use ControllerGuard;

    protected $site;
    protected $validator;

    protected $repository = null;

    public function __construct(Request $request)
    {
        $this->repository = new SiteRepository();

        $this->validator = new SiteValidator();  //构造Site验证器

        parent::__construct($request);
    }

    protected $beforeActionList = [
        'checkLogin' => ['except' => 'GetHttpsCert'],
        'IsDDoSIps'  => ['only' => 'GetHttpsCert']
    ];

    /**
     * @SWG\Get(
     *      path="/site",
     *      tags={"Site 网站防护"},
     *      summary="获取用户的域名",
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          description="类型:0 未设置; 1 独享型; 2 共享型",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="name",
     *          in="query",
     *          description="域名",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="ip",
     *          in="query",
     *          description="ip",
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
     *          ref="#/definitions/SiteList"
     *      )
     * )
     *
     * 域名状态码：
     * 0：待审核 1：待接入 2：正在接入 3：正常
     * 4：接入错误 5：正在删除 6：删除异常
     * @return array     $domainList 返回结果数组
     */
    public function index()
    {
        $from = input('_from', 0);
        $size = input('_size', null);

        $must = [];
        // 根据类型进行筛选
        $type = input('type', null);
        if ($type !== null && $type != '') {
            $must[] = ['term' => ['type' => $type]];
        }

        // 根据域名进行筛选
        $name = input('name', null);
        if ($name !== null && $name != '') {
            $must[] = ['term' => ['name.keyword' => $name]];
        }

        // 根据高防IP进行筛选
        $ip = input('ip', null);
        if ($ip !== null && $ip != '') {
            $must[] = ['term' => ['proxy_ip.ip' => $ip]];
        }

        // 根据当前用户查询
        $must[] = ['term' => ['uid.keyword' => Auth::id()]];
        $filter = [
            'query' => ['bool' => ['must' => $must, 'must_not' => ['term' => ['type' => PortModel::USER_APP_TYPE_PORT]]]],
            "sort"  => [["last_update" => ["order" => "desc"]]],
        ];
        $domainList = $this->repository->domainList($filter, $from, $size);
        $count = $this->repository->getSiteCount($filter);

        return Finalsuccess(["list" => $domainList, 'total' => $count]);
    }

    /**
     *
     * @SWG\Post(
     *      path="/site",
     *      tags={"Site 网站防护"},
     *      summary="用户添加站点",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="站点信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"name": "test.com", "http": 0, "https": 1, "upstream": "127.0.0.1,127.0.0.2"}
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
        try {
            // 参数校验
            if (!$this->validator->scene('site_add')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            $data = $request->only(['name', 'http', 'https', 'upstream']);
            // 检查当前域名是否存在
            if ($result = $this->repository->domainIsExist($data['name'])) {
                return Finalfail(REP_CODE_ILLEGAL_OPERATION, '站点已经存在，不能重复添加！');
            }

            // 添加新域名
            if (!$domain = $this->repository->addDomain($data)) {
                return Finalfail(REP_CODE_FAILED_OPERATION, '站点配置保存失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            // 日志记录当前错误信息
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '站点创建失败！');
        }
    }

    /**
     *
     * @SWG\Put(
     *      path="/site/{id}",
     *      tags={"Site 网站防护"},
     *      summary="更新站点配置信息",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="站点信息",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"http": 0, "https": 1, "upstream": "127.0.0.1,127.0.0.2"}
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
     *
     * 更新站点信息
     *
     * @param $id
     * @return bool|string
     */
    public function update($id)
    {
        try {
            $data = request()->only(['upstream', 'http', 'https']);
            if (!$this->validator->scene('update_site')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 更新域名配置信息
            $data['upstream'] = explode(',', $data['upstream']);
            if (!$this->repository->updateSiteInfo($data, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点配置失败！');
            }

            // 如果站点配置有更改，且站点已接入需要移除已经写入的ZK Proxy配置信息
            if ((in_array($site['status'], [SiteModel::DOMAIN_STATUS_LINKED, SiteModel::DOMAIN_STATUS_NORMAL]))) {
                // 删除原有的zkProxy配置
                if (!$this->repository->rmZKProxyConf($site)) {
                    return Finalfail('移除原有的ZK配置失败！');
                }

                // 更新ES Proxy Conf 配置信息
                if (!$this->repository->setESProxyConf($id)) {
                    return Finalfail(REP_CODE_FAILED_OPERATION, 'Proxy Conf 配置保存失败！');
                }

                // 重新写入站点高防节点ZK配置信息
                if (!$this->repository->setZKProxyConf($id)) {
                    return Finalfail('Proxy信息写入ZK失败！');
                }
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '站点接入失败！');
        }
    }

    /**
     *
     * @SWG\Get(
     *      path="/site/{id}",
     *      tags={"Site 网站防护"},
     *      summary="获取域名详情",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="用户实例 Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"app_id":"abc.com","uid":"test@veda.com","name":"abc.com",
     *     "type":0,"http":"1","https":"1","https_cert":"","https_cert_key":"","upstream":{"192.168.10.1"},"status":1,
     *     "text_code":"wLAnNoU6Jfu5yFmZ","proxy_ip":{{"area":11,"instance_id":"ddos-kzxrdnp","line":"8","ip":"192.9.2.1",
     *     "instance_line":"8"}},"last_update":"1524445499"}}
     *          )
     *      )
     * )
     *
     * 获取站点详情
     *
     * @param $id
     * @return string
     */
    public function show($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }
            $site['last_update'] = strtotime($site['last_update']);

            return Finalsuccess(['data' => $site]);
        } catch (\Exception $e) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/txtcode",
     *      tags={"Site 网站防护"},
     *      summary="获取txt类型记录值",
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         required=true,
     *         type="string",
     *         description="用户站点"
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","vdomain":"hdverify.cncn.com","way":"TXT","utextcode":"f72004acd5a6a9a8"}
     *          )
     *      )
     * )
     *
     * 获取txt类型记录值
     * @param $id
     * @return string    json     返回记录值
     * @throws \Exception
     */
    public function txtCode($id)
    {
        // 生成幻盾安全域名
        $HDDomain = SiteRepository::generateHDDomain($id);
        // 获取该域名的textCode值
        $textCode = $this->repository->getDomainTextCode($id);
        if (!$textCode) {
            return Finalfail('13001', "Domain not found");
        }

        $verifydata = [
            'vdomain'   => $HDDomain,
            'way'       => 'TXT',
            'utextcode' => $textCode
        ];

        return Finalsuccess($verifydata);
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/txtcode-verify",
     *      tags={"Site 网站防护"},
     *      summary="审核此站点txt记录值是否符合",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"domain": "www.cncn.com"},
     *              description="站点"
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 审核成功| !=0 审核失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * 审核此站点txt记录值是否符合
     * @param $id
     * @return string    json     返回结果
     * @throws \Exception
     */
    public function txtcodeVerify($id)
    {
        try {
            // 检查站点是否存在
            $exist = $this->repository->getSiteById($id);
            if (!$exist) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '站点不存在!');
            }

            //生成幻盾安全域名
            $vDomain = SiteRepository::generateHDDomain($id);
            if (!config('app_debug')) { // 测试环境不进行校验

                // 获取系统生成的TextCode与用户填写的进行比较
                if (!$textCode = $this->repository->getDomainTextCode($id)) {
                    return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点TextCode值!');
                }

                //检查域名是否有效
                $result = SiteRepository::domainIsValid($vDomain);
                if ($result === false) {
                    return Finalfail(REP_CODE_ILLEGAL_OPERATION, '站点Txt解析异常！');
                }

                // 系统生成的TextCode与用户填写的进行比较
                if ($result != $textCode) {
                    return Finalfail(REP_CODE_ILLEGAL_OPERATION, '站点解析TxTCode值不匹配！');
                }
            }

            // 更新站点状态为已审核
            $result = $this->repository->updateSiteStatus($id, SiteModel::DOMAIN_STATUS_APPROVED);
            if (!$result) {
                return Finalfail(REP_CODE_ES_ERROR, 'DB error');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '站点TxTCode值校验失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/linkup",
     *      tags={"Site 网站防护"},
     *      summary="用户接入站点",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="接入信息",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"app_id":"e.com","uid":"test@veda.com","name":"e.com",
     *     "type":"1","http":"1","https":"0","https_cert":"","https_cert_key":"","upstream":{"192.15.57.1"},"status":3,
     *     "text_code":"N87PW2jOsXpw64YZ","proxy_ip":{{"area":41,"instance_id":"ddos-rbpavgc","line":"2","ip":"192.168.9.22",
     *     "instance_line":"3"}},"last_update":"2018-04-24T07:57:33.000Z","cname":"nekex91bli.hd.vedasec.net"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 接入成功| !=0 接入失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":""}
     *          )
     *      )
     * )
     *
     * 用户接入站点
     * @param $id
     * @return string    json     返回结果
     */
    public function linkup($id)
    {
        try {
            if (!$this->validator->scene('linkup')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            $data = input();
            if (!$siteModel = $this->repository->getSiteById($id)) {    //站点不存在
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '域名不存在！');
            }

            // 检查当前域名状态
            if ($siteModel['status'] != SiteModel::DOMAIN_STATUS_APPROVED) { //站点未通过审核
                return Finalfail(REP_CODE_ILLEGAL_OPERATION, '域名状态异常！');
            }

            // 生成当前站点的CName记录
            $cname = $this->repository->generateDomainCName();
            // 更新站点状态为接入中
            $attributes = [
                'http'   => $siteModel['http'], 'https' => $siteModel['https'], 'linked_ips' => $data['linked_ips'],
                'status' => SiteModel::DOMAIN_STATUS_LINKING, 'cname' => $cname, 'type' => $data['type']
            ];
            if (!$this->repository->updateSiteInfo($attributes, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '域名配置更新失败！');
            }

            $data['cname'] = $cname;
            // 更新站点状态为接入中，同时将接入信息写入ES
            if (!$this->repository->updateSiteProxyConf($attributes, $id)) {
                return Finalfail(REP_CODE_DB_ERROR, '域名状态更新失败！');
            }

            // 在proxy_conf 中加入新的DNS配置
            if (!$this->repository->setESProxyConf($id)) {
                return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf 配置保存失败！');
            }

            // 下发配置
            if (!$this->repository->addESDNSConf($data, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, 'DNS Node Info配置写入失败！');
            }

            // 将DNS配置写入Zookeeper
            if (!$this->repository->setZKDnsConf($data, $id)) {
                return Finalfail(REP_CODE_ZK_ERROR, 'DNS配置信息写入失败！');
            }

            // 将转发设置写入Zookeeper
            if (!$this->repository->setZKProxyConf($id)) {
                return Finalfail(REP_CODE_ZK_ERROR, 'Proxy信息写入ZK失败！');
            }

            // 更新站点状态为已接入
            if (!$result = $this->repository->updateSiteStatus($id, SiteModel::DOMAIN_STATUS_LINKED)) {
                return Finalfail(REP_CODE_DB_ERROR, '域名状态更新失败！');
            }
            $site = $this->repository->getSiteById($id);

            return Finalsuccess(['data' => $site]);
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '域名接入失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/linkup-update",
     *      tags={"Site 网站防护"},
     *      summary="更新站点接入信息",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="接入信息",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"linked_ips": {{"line": "1", "ip": "192.168.1.0",
     *     "ddos_id":"ddos-ofanbvo"},{"line": "2", "ip": "192.168.1.1", "ddos_id":"ddos-ofanbvo"}}}
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
     * @param $id
     * @return string
     */
    public function updateLinkUp($id)
    {
        try {
            $data = request()->only('linked_ips');
            if (!$this->validator->scene('update_linkup')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            //------------------------- 移除旧的配置 START -------------------------------

            if (!$this->repository->rmESDNSConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '移除DNS Conf 信息失败！');
            }

            if (!$this->repository->rmESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '移除Proxy Conf信息失败！');
            }

            if (!$this->repository->rmZKProxyConf($site)) {
                return Finalfail(REP_CODE_ZK_ERROR, 'ZK删除Proxy数据写入失败！');
            }

            if (!$this->repository->rmZKDNSConf($site)) {
                return Finalfail(REP_CODE_ZK_ERROR, 'ZK删除DNS数据写入失败！');
            }

            if (!$this->repository->resetUserInstanceSiteCount($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新用户实例域名接入数量失败！');
            }

            //------------------------- 移除旧的配置 END -------------------------------
            // 更新站点的接入信息
            if (!$this->repository->updateSiteProxyConf($data, $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点配置失败！');
            }

            //------------------------- 写入新配置 START -------------------------------
            if (!$this->repository->setESProxyConf($id)) {
                return Finalfail(REP_CODE_ES_ERROR, 'Proxy Conf 配置保存失败！');
            }

            if (!$this->repository->addESDNSConf($data, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, 'DNS Node Info配置写入失败！');
            }

            if (!$this->repository->setZKDnsConf($data, $id)) {
                return Finalfail(REP_CODE_ZK_ERROR, 'DNS配置信息写入失败！');
            }

            if (!$this->repository->setZKProxyConf($id)) {
                return Finalfail('Proxy信息写入ZK失败！');
            }

            //------------------------- 写入新配置 END -------------------------------

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '站点接入信息更新失败！');
        }
    }

    /**
     * @SWG\Delete(
     *      path="/site/{id}",
     *      tags={"Site 网站防护"},
     *      summary="用户删除站点",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="站点Id",
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
        try {
            // 参数校验
            if (!$this->validator->scene('del_site')->check(compact('id'))) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 已经成功接入的站点需要进行ZK和ES信息移除
            if (in_array($site['status'], [SiteModel::DOMAIN_STATUS_LINKED, SiteModel::DOMAIN_STATUS_NORMAL])) {
                // 移除Proxy Conf配置信息
                if (!$this->repository->rmESProxyConf($site)) {
                    return Finalfail(REP_CODE_ES_ERROR, '移除Proxy Conf信息失败！');
                }

                // 移除ES DNS Conf配置信息
                if (!$this->repository->rmESDNSConf($site)) {
                    return Finalfail(REP_CODE_ES_ERROR, '移除DNS Conf 信息失败！');
                }

                // 将删除DNS配置写入Zookeeper
                if (!$this->repository->rmZKDNSConf($site)) {
                    return Finalfail(REP_CODE_ZK_ERROR, 'ZK删除DNS数据写入失败！');
                }

                // 将删除DNS配置写入Zookeeper
                if (!$this->repository->rmZKProxyConf($site)) {
                    return Finalfail(REP_CODE_ZK_ERROR, 'ZK删除Proxy数据写入失败！');
                }

                // 更新用户实例中的域名接入数量
                if (!$this->repository->resetUserInstanceSiteCount($site)) {
                    return Finalfail(REP_CODE_ES_ERROR, '更新用户实例域名接入数量失败！');
                }
            }

            // 移除站点
            if (!$this->repository->removeDomain($id)) {
                // 更新域名状态为删除异常
                $this->repository->updateSiteStatus($id, SiteModel::DOMAIN_STATUS_DELETE_ERR);

                return Finalfail(REP_CODE_DB_ERROR, 'DB error');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '站点删除失败！');
        }
    }

    /**
     *
     * @SWG\Post(
     *      path="/site/{id}/cname-verify",
     *      tags={"Site 网站防护"},
     *      summary="校验CNAME是否正确解析",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="站点Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 校验通过| !=0 校验失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * 校验域名DNS解析状态
     *
     * @param $id
     * @return string
     */
    public function cNameVerify($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            if (!config('app_debug')) {
                // 校验域名的Cname是否正确解析
                if (!$this->repository->cNameVerify($site['name'], $site['cname'])) {
                    return Finalfail(REP_CODE_FAILED_OPERATION, 'CNAME未正确解析！');
                }
            }

            // 更新域名状态为正常
            if (!$this->repository->updateSiteStatus($id, SiteModel::DOMAIN_STATUS_NORMAL)) {
                return Finalfail(REP_CODE_ES_ERROR, '域名状态更新失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            return Finalfail(REP_CODE_FAILED_OPERATION, 'CNAME验证失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/https-cert/upload",
     *      tags={"Site 网站防护"},
     *      summary="站点防护Https 证书上传",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="HTTPS证书信息",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={ "certificate": "LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpDRVJUSUZJQ0FURQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==",
     *              "certificate_key": "LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KS0VZDQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQ=="}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 上传成功| !=0 上传失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function httpsCertUpload($id)
    {
        try {
            $data = input();
            // 参数校验
            if (!$this->validator->scene('https_cert_upload')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            // 检查站点是否存在
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 更新站点的HTTPS证书
            if (!$this->repository->updateSiteCertificate($site, base64_decode($data['certificate']), base64_decode($data['certificate_key']))) {
                return Finalfail(REP_CODE_ES_ERROR, '站点HTTPS证书更新失败！');
            }

            //更新ES Proxy ConfHTTP证书信息
            if (!$this->repository->setESProxyConf($id)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf证书信息失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, 'HTTPS证书上传失败！');
        }
    }

    /**
     * @SWG\post(
     *      path="/site/{id}/https-cert",
     *      tags={"Site 网站防护"},
     *      summary="获取站点上传的HTTPS证书",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="站点Id, example: www.abc.com",
     *          type="string",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","cert":{"site":"www.abc.com",
     *     "certificate":"LS0tLS1CRUdJTiBDRVJUSUZJQ0FURS0tLS0tDQpDRVJUSUZJQ0FURQ0KLS0tLS1FTkQgQ0VSVElGSUNBVEUtLS0tLQ==",
     *     "certificate_key":"LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQ0KS0VZDQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQ=="}}
     *          )
     *      )
     * )
     *
     * @return string
     */
    public function GetHttpsCert()
    {
        try {
            $data = input();
            // 参数校验
            if (!$this->validator->scene('get_https_cert')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            // 查找站点
            if (!$site = $this->repository->getSiteById($data['site'])) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 获取该站点的HTTPS证书
            if (!$cert = $this->repository->getSiteCerts($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '未找到该站点的HTTPS证书信息！');
            }

            return Finalsuccess(compact('cert'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '服务端异常，证书获取失败！');
        }
    }

    /**
     * @SWG\Delete(
     *      path="/site/bundle/delete",
     *      tags={"Site 网站防护"},
     *      summary="批量删除站点",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="站点ID数组",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ids": {"test1.com", "test3.com"}}
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
     *
     * @return string
     */
    public function bundleDelete()
    {
        $data = input();
        if (!$this->validator->scene('bundle_delete')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        foreach ($data['ids'] as $id) {
            // 依次移除对应站点的ES和ZK配置
            $result = ($this->destroy($id))->getData();
            if ($result['errcode'] != 0) {
                return Finalfail($result['errcode'], $result['errmsg']);
            }
        }

        return Finalsuccess();
    }

    /**
     *
     * @SWG\Post(
     *      path="/site/{id}/cache-expire",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="设置站点的缓存有效期",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="缓存有效期配置",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"static_expire":"1:minute","html_expire":"2:minute","index_expire":"5:minute","directory_expire":"10:minute"}
     *          )
     *      ),
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
     *
     * 设置站点的缓存配置
     * @param $id
     * @return string
     */
    public function setSiteCacheExpire($id)
    {
        try {
            $data = request()->only(['static_expire', 'html_expire', 'index_expire', 'directory_expire']);
            if (!$this->validator->scene('set_site_cache_expire')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 设置站点缓存有效期
            if (!$this->repository->setSiteCacheExpire($data, $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '设置站点缓存有效期失败！');
            }

            if (!$this->repository->setSiteProxyCache($data, $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新高防节点缓存配置失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '更新高防节点缓存配置失败！');
        }
    }

    /**
     *
     * @SWG\Get(
     *      path="/site/{id}/cache-expire",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="获取站点的缓存有效期配置",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","cache":{"static_expire":"1:minute","html_expire":"2:minute",
     *     "index_expire":"5:hour","directory_expire":"10:minute"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function getSiteCacheExpire($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalsuccess(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 获取站点的缓存有效期设置
            $cache = $this->repository->getSiteCacheExpire($site);

            return Finalsuccess(compact('cache'));
        } catch (\Exception $e) {
            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点缓存配置失败！');
        }
    }

    /**
     *
     *
     * @SWG\Post(
     *      path="/site/{id}/cache-whitelist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="添加站点缓存白名单",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="缓存白名单关键字",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"keyword":"test","expire":"1:hour"}
     *          )
     *      ),
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
     * 添加站点缓存策略
     * @param $id
     * @return string
     */
    public function addSiteCacheWhiteList($id)
    {
        try {
            $data = request()->only(['keyword', 'expire']);
            if (!$this->validator->scene('set_site_cache_keywords')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 检查缓存关键字是否存在
            if ($this->repository->isWhiteCacheKeywordExist($data['keyword'], $site)) {
                return Finalfail(REP_CODE_ILLEGAL_OPERATION, '关键字已存在，不能重复添加！');
            }

            // 添加站点缓存策略
            if (!$this->repository->addSiteCacheWhiteList($data, $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '添加站点缓存策略失败！');
            }

            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf Cache失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '更新站点缓存白名单失败！');
        }
    }

    /**
     *
     * @SWG\Get(
     *      path="/site/{id}/cache-whitelist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="获取站点的缓存白名单配置",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","whiteList":{{"time":"1:hour","keyword":"test"}}}
     *          )
     *      )
     * )
     *
     *
     * @param $id
     * @return string
     */
    public function getSiteCacheWhiteList($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }
            $whiteList = $this->repository->getSiteCacheWhiteList($site);

            return Finalsuccess(compact('whiteList'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点缓存白名单失败！');
        }
    }

    /**
     * @SWG\Delete(
     *      path="/site/{id}/cache-whitelist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="删除站点缓存白名单关键字",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="需要删除的缓存关键字",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"keywords": {"test1"}}
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
     * @param $id
     * @return string
     */
    public function delSiteCacheWhiteList($id)
    {
        try {
            $data = request()->only('keywords');
            if (!$this->validator->scene('del_site_cache_keywords')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 移除缓存白名单关键字
            if (!$this->repository->rmSiteCacheWhiteList($data['keywords'], $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '删除失败！');
            }

            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf Cache失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '删除站点缓存白名单失败！');
        }
    }

    /**
     *
     * @SWG\Get(
     *      path="/site/{id}/cache-blacklist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="获取站点的缓存黑名单配置",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","blacklist":{"test2","test3"}}
     *          )
     *      )
     * )
     *
     *
     * @param $id
     * @return string
     */
    public function getSiteCacheBlackList($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }
            $blacklist = $this->repository->getSiteCacheBlackList($site);

            return Finalsuccess(compact('blacklist'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点缓存黑名单失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/cache-blacklist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="添加站点缓存黑名单",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="黑名单关键字",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"keyword":"test"}
     *          )
     *      ),
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
     * 添加站点缓存黑名单
     * @param $id
     * @return string
     */
    public function addSiteCacheBlackList($id)
    {
        try {
            $data = request()->only(['keyword']);
            if (!$this->validator->scene('set_site_cache_blackList')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }
            // 检查缓存关键字是否存在
            if ($this->repository->isBlackCacheKeywordExist($data['keyword'], $site)) {
                return Finalfail(REP_CODE_ILLEGAL_OPERATION, '关键字已存在，不能重复添加！');
            }

            if (!$this->repository->addSiteCacheBlackList($data, $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '添加站点缓存黑名单失败！');
            }
            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf Cache失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '设置站点缓存黑名单失败！');
        }
    }

    /**
     * @SWG\Delete(
     *      path="/site/{id}/cache-blacklist",
     *      tags={"Site 网站防护缓存加速"},
     *      summary="删除站点缓存黑名单关键字",
     *      @SWG\Parameter(
     *          name="",
     *          in="body",
     *          description="需要删除的缓存黑名单关键字",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"keywords": {"test1"}}
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
     * @param $id
     * @return string
     */
    public function delSiteCacheBlackList($id)
    {
        try {
            $data = request()->only('keywords');
            if (!$this->validator->scene('del_site_cache_blacklist')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 移除缓存白名单关键字
            if (!$this->repository->rmSiteCacheBlackList($data['keywords'], $id)) {
                return Finalfail(REP_CODE_ES_ERROR, '删除失败！');
            }

            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf Cache失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点缓存黑名单失败！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/url-whitelist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="获取站点Url白名单列表",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","urlWhitelist":{"a.com","b.com"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function getUrlWhiteList($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }

            $urlWhitelist = $site['filter']['url_whitelist'] ?? [];

            return Finalsuccess(compact('urlWhitelist'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点网址白名单失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/url-whitelist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="更新站点Url白名单列表",
     *      @SWG\Parameter(
     *          name="urlWhitelist",
     *          in="body",
     *          description="站点URL白名单",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"urlWhitelist":{"a.com","b.com"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function setUrlWhiteList($id)
    {
        try {
            $urlWhitelist = request()->param('urlWhitelist/a');
            if (!$this->validator->scene('set_url_whitelist')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }

            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 重复的不进行保存
            $urlWhitelist = array_unique($urlWhitelist);
            if (!$this->repository->updateSiteInfo(['filter' => ['url_whitelist' => $urlWhitelist]], $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点URL白名单失败！');
            }

            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf配置信息失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '设置站点网址白名单失败！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/url-blacklist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="获取站点Url黑名单列表",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","urlBlacklist":{"a.com","b.com"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function getUrlBlackList($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }
            $urlBlacklist = $site['filter']['url_blacklist'] ?? [];

            return Finalsuccess(compact('urlBlacklist'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点网址黑名单失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/url-blacklist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="更新站点Url黑名单列表",
     *      @SWG\Parameter(
     *          name="urlBlacklist",
     *          in="body",
     *          description="站点URL黑名单",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"urlBlacklist":{"a.com","b.com"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function setUrlBlackList($id)
    {
        try {
            $urlBlacklist = request()->param('urlBlacklist/a');
            if (!$this->validator->scene('set_url_blacklist')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 重复的不进行保存
            $urlBlacklist = array_unique($urlBlacklist);
            if (!$this->repository->updateSiteInfo(['filter' => ['url_blacklist' => $urlBlacklist]], $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点URL白名单失败！');
            }
            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf配置信息失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '设置站点网址黑名单失败！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/ip-blacklist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="获取站点IP黑名单列表",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","ipBlacklist":{"127.0.0.1","127.0.0.2"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function getIpBlacklist($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }
            $ipBlacklist = $site['filter']['ip_blacklist'] ?? [];

            return Finalsuccess(compact('ipBlacklist'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点IP黑名单失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/ip-blacklist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="更新站点IP黑名单列表",
     *      @SWG\Parameter(
     *          name="ipBlacklist",
     *          in="body",
     *          description="站点IP黑名单",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ipBlacklist":{"127.0.0.1","127.0.0.2"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function setIpBlacklist($id)
    {
        try {
            $ipBlacklist = request()->param('ipBlacklist/a');
            if (!$this->validator->scene('set_ip_blacklist')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 重复的不进行保存
            $ipBlacklist = array_unique($ipBlacklist);
            if (!$this->repository->updateSiteInfo(['filter' => ['ip_blacklist' => $ipBlacklist]], $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点IP白名单失败！');
            }
            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf配置信息失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '设置站点IP黑名单失败！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/ip-whitelist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="获取站点IP白名单列表",
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","ipWhitelist":{"127.0.0.1","127.0.0.2"}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function getIpWhitelist($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该应用！');
            }
            $ipWhitelist = $site['filter']['ip_whitelist'] ?? [];

            return Finalsuccess(compact('ipWhitelist'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点IP白名单失败！');
        }
    }

    /**
     * @SWG\Post(
     *      path="/site/{id}/ip-whitelist",
     *      tags={"Site 网站防护黑白名单"},
     *      summary="更新站点IP白名单列表",
     *      @SWG\Parameter(
     *          name="ipWhitelist",
     *          in="body",
     *          description="站点IP白名单",
     *          required=true,
     *          @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"ipWhitelist":{"127.1.1.1","127.1.1.2"}}
     *          )
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function setIpWhitelist($id)
    {
        try {
            $ipWhitelist = request()->param('ipWhitelist/a');
            if (!$this->validator->scene('set_ip_whitelist')->check(input())) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }

            // 重复的不进行保存
            $ipWhitelist = array_unique($ipWhitelist);
            if (!$this->repository->updateSiteInfo(['filter' => ['ip_whitelist' => $ipWhitelist]], $site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新站点IP白名单失败！');
            }
            $site = $this->repository->getSiteById($id);
            if (!$this->repository->setESProxyConf($site)) {
                return Finalfail(REP_CODE_ES_ERROR, '更新Proxy Conf配置信息失败！');
            }

            return Finalsuccess();
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '设置站点IP白名单失败！');
        }
    }

    /**
     * @SWG\Get(
     *      path="/site/{id}/proxy-ips",
     *      tags={"Site 网站防护"},
     *      summary="获取站点高仿节点列表",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="站点ID",
     *          type="string"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","proxyIps":{{"area":33,"line":"8","ip":"192.16.19.1","ddos_id":"ddos-dknajde",
     *     "area_text":"浙江","line_text":"BGP"}}}
     *          )
     *      )
     * )
     *
     * @param $id
     * @return string
     */
    public function proxyIps($id)
    {
        try {
            if (!$site = $this->repository->getSiteById($id)) {
                return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该站点！');
            }
            $proxyIps = $site['proxy_ip'] ?? [];
            foreach ($proxyIps as &$proxyIp) {
                $proxyIp['ddos_id'] = $proxyIp['instance_id'];
                $proxyIp['area_text'] = UserInstanceModel::$instanceAreas[$proxyIp['area']];
                $proxyIp['line_text'] = UserInstanceModel::$instanceLines[$proxyIp['line']];

                unset($proxyIp['instance_id'], $proxyIp['instance_line']);
            }

            return Finalsuccess(compact('proxyIps'));
        } catch (\Exception $e) {
            $this->errorHandle($e);

            return Finalfail(REP_CODE_FAILED_OPERATION, '获取站点高仿IP失败！');
        }
    }
}