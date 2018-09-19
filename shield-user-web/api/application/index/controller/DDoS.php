<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 15:04
 */

namespace app\index\controller;


use app\index\model\UserInstanceModel;
use app\index\repository\DDoSRepository;
use app\index\service\Auth;
use app\index\service\MockData;
use app\index\traits\CheckLogin;
use app\index\validate\DDoS as DDoSValidate;
use think\Request;

class DDoS extends BaseController
{

    use CheckLogin;

    public function __construct(Request $request)
    {
        $this->validator = new DDoSValidate();

        $this->repository = new DDoSRepository();

        parent::__construct();
    }

    protected $beforeActionList = [
        'checkLogin'
    ];

    /**
     * @SWG\Get(
     *      path="/ddos",
     *      tags={"DDoS 高防实例"},
     *      summary="获取用户的高防实例列表",
     *      @SWG\Parameter(
     *          name="line",
     *          in="query",
     *          description="实例线路",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="ip",
     *          in="query",
     *          description="实例IP",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="实例ID",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          description="类型",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          in="query",
     *          description="状态",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="area",
     *          in="query",
     *          description="区域",
     *          type="integer"
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
     *          ref="#/definitions/DDoSList"
     *      )
     * )
     *
     * 获取高仿IP列表：
     * @return array     $domainList 返回结果数组
     * @throws \Exception
     */
    public function index()
    {
        $filter = [['term' => ['uid.keyword' => Auth::id()]]];
        $from = input('_from', 0);
        $size = input('_size', null);

        // 根据线路搜索
        $line = input('line', null);
        if ($line !== null && $line != '') {
            $filter[] = ['term' => ['instance_line' => $line]];
        }

        // 根据IP搜索
        $ip = input('ip', null);
        if ($ip !== null && $ip != '') {
            $filter[] = ['term' => ['hd_ip.ip' => $ip]];
        }
        // 根据ID搜索
        $id = input('id', null);
        if ($id !== null && $id != '') {
            $filter[] = ['term' => ['instance_id.keyword' => $id]];
        }
        // 根据状态搜索
        $status = input('status', null);
        if ($status !== null && $status != '') {
            $filter[] = ['term' => ['status' => $status]];
        }
        // 根据地域搜索
        $area = input('area', null);
        if ($area !== null && $area != '') {
            $relationAreas = $this->repository->getRelationAreaIds($area);
            $filter[] = ['terms' => ['area' => $relationAreas]];
        }

        // 根据类型搜索
        $type = input('type', null);
        if ($type !== null && $type != '' && $type != 0) {
            $types = explode(',', trim($type));
            !empty($types) && $filter[] = ['terms' => ['type' => $types]];
        }

        // 获取用户的高防实例列表
        $filter = [
            'query' => ['bool' => ['filter' => $filter]],
            "sort" => [["last_update" => ["order" => "desc"]]],
        ];
        $list = $this->repository->getDDoSList($filter, $from, $size);
        $total = $this->repository->getListTotal($filter);

        return Finalsuccess(compact('list', 'total'));
    }

    /**
     * @SWG\Post(
     *      path="/ddos/{id}/active",
     *      tags={"DDoS 高防实例"},
     *      summary="启用用户实例",
     *     @SWG\Parameter(
     *         name="area",
     *         in="query",
     *         type="string",
     *         description="实例接入区域",
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
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
     * @throws \Exception
     */
    public function active($id)
    {
        // 参数校验
        if (!$this->validator->scene('active_ddos')->check(input())) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        // 检查当前实例是否存在
        if (!$ddosInstance = $this->repository->getDDoSById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该实例！');
        }

        // 校验当前实例的状态是否为未激活
        if ($ddosInstance['status'] != UserInstanceModel::STATUS_CREATED) {
            return Finalfail(REP_CODE_ILLEGAL_OPERATION, '该实例状态异常！');
        }

        // 查询高仿节点信息
        if (!$proxyNodeInfo = $this->repository->getAvailableProxyNodeInfo(
            $ddosInstance['instance_line'], $ddosInstance['type'], input('area')
        )) {
            return Finalfail(REP_CODE_ILLEGAL_OPERATION, '暂无可用高仿节点');
        }

        // 更新当前高防节点信息接入信息
        if (!$this->repository->updateProxyNodeUserCount($proxyNodeInfo)) {
            return Finalfail(REP_CODE_ES_ERROR, '更新高防节点用户接入数失败！');
        }

        // 激活当前实例
        if (!$this->repository->ddosActive($id, $proxyNodeInfo)) {
            return Finalfail(REP_CODE_ILLEGAL_OPERATION, '实例启用失败！');
        }

        return Finalsuccess();
    }

    /**
     *
     * @SWG\Get(
     *      path="/ddos/{id}",
     *      tags={"DDoS 高防实例"},
     *      summary="获取用户实例详情",
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
     *              example={"errcode":0,"errmsg":"ok","data":{"uid":"test@veda.com","type":3,"status":0,"instance_line":"11",
     *     "normal_bandwidth":50,"bandwidth":20,"base_bandwidth":20,"start_date":1523331760,"end_date":1586490160,"area":"",
     *     "hd_ip":{},"port_count":50}}
     *          )
     *      )
     * )
     *
     * 获取实例详情
     *
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function show($id)
    {
        $data = input();
        if (!$this->validator->scene('get_ddos')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        if ($ddos = $this->repository->getDDoSById($id)) {
            return Finalsuccess(['data' => $ddos]);
        }

        return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该实例！');
    }

    /**
     *
     * @SWG\Get(
     *      path="/ddos/ips",
     *      tags={"DDoS 高防实例"},
     *      summary="获取用户实例高防IP",
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
     *      @SWG\Parameter(
     *          name="line",
     *          in="query",
     *          description="接入线路",
     *          type="string"
     *      ),
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          description="实例类型",
     *          type="string"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","list":{{"type":"3","area":11,"area_text":"北京","ddos_id":"ddos-l9umrps",
     *     "ips":{{"line":"1","ip":"192.168.9.20","port_count":2,"line_text":"电信"},{"line":"2","ip":"192.168.9.22",
     *     "port_count":2,"line_text":"联通"},{"line":"8","ip":"192.168.9.55","port_count":2,"line_text":"BGP"}}}},"total":3}
     *          )
     *      )
     * )
     *
     * @return string
     * @throws \Exception
     */
    public function ips()
    {
        $from = input('_from', 0);
        $size = input('_size', null);

        $line = input('line', null);
        $type = input('type', null);
        $ips = $this->repository->getUserDDoSIps($line, $type, $from, $size);

        return Finalsuccess($ips);
    }

    /**
    /**
     * @SWG\Get(
     *      path="/ddos/areas",
     *      tags={"DDoS 高防实例"},
     *      summary="获取地域列表",
     *      @SWG\Response(
     *          response="200",
     *          description="",
     *          ref="#/definitions/AreaList"
     *      )
     * )
     *
     * 获取所有
     * @return string
     * @throws \app\common\exception\MockDataModuleNotFound
     */
    public function areas()
    {
        // 获取所有的实例基础数据
        $data = MockData::value('AreaList');

        return Finalsuccess(['list' => $data]);
    }

    /**
     * @SWG\Get(
     *      path="/ddos/{id}/available-areas",
     *      tags={"DDoS 高防实例"},
     *      summary="用户实例可接入地域",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="用户实例Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 正常| !=0 异常",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","areas":{{"value":1,"label":"华北","children":{{"value":11,
     *     "label":"北京"}}},{"value":3,"label":"华东","children":{{"value":37,"label":"山东"}}}}}
     *          )
     *      )
     * )
     * 用户实例可接入地域
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function availableAreas($id)
    {
        if (!$instance = $this->repository->getDDoSById($id)) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该实例！');
        }
        $areas = $this->repository->getAvailableArea($instance['instance_line'], $instance['type']);

        return Finalsuccess(['areas' => $areas]);
    }
}