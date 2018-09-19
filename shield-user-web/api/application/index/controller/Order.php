<?php

namespace app\index\controller;

use app\index\model\OrderModel;
use app\index\repository\OrderRepository;
use app\index\service\Auth;
use app\index\traits\CheckLogin;
use app\index\validate\Order as OrderValidator;
use think\Request;

class Order extends BaseController
{
    use CheckLogin;

    protected $validator = null;

    protected $repository = null;

    public function __construct(Request $request = null)
    {
        $this->repository = new OrderRepository();

        $this->validator = new OrderValidator();

        parent::__construct($request);
    }

    protected $beforeActionList = [
        'checkLogin'
    ];

    /**
     * @SWG\Get(
     *      path="/order",
     *      tags={"Order 订单管理"},
     *      summary="获取订单列表",
     *      @SWG\Parameter(
     *          name="type",
     *          in="query",
     *          description="类型:1-充值;2-消费",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="status",
     *          in="query",
     *          description="支付状态:0-未支付；1-已支付；2-已作废",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="start_date",
     *          in="query",
     *          description="开始时间戳",
     *          type="integer"
     *      ),
     *      @SWG\Parameter(
     *          name="end_date",
     *          in="query",
     *          description="结束时间戳",
     *          type="integer"
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="",
     *          ref="#/definitions/OrderList"
     *      )
     * )
     *
     */
    public function index()
    {
        $must = [];

        // 分页相关
        $from = input('_from', 0);
        $size = input('_size', null);

        // 根据订单类型查询： 1 - 充值,2 - 消费
        $type = input('type', null);
        if ($type !== null && $type != '') {
            $must[] = ['term' => ['type' => $type]];
        }

        // 根据订单状态查询
        $status = input('status', null);
        if ($status !== null && $status != '') {
            $must[] = ['term' => ['status' => $status]];
        }

        // 根据开始时间查询
        $start = input('start_date', null);
        if ($start !== null && $start != '') {
            $must[] = ['range' => ['create_time' => ['gte' => $start]]];
        }

        // 根据结束时间查询
        $end = input('end_date', null);
        if ($end !== null & $end != '') {
            $must[] = ['range' => ['create_time' => ['lte' => $end]]];
        }

        // 根据当前用户查询
        $must[] = ['term' => ['uid.keyword' => Auth::id()]];
        $filter = [
            'query' => ['bool' => ['must' => $must]],
            "sort" => [["create_time" => ["order" => "desc"]]],
        ];
        $orders = $this->repository->orderList($filter, $from, $size);
        $count = $this->repository->getListTotal($filter);

        return Finalsuccess(['data' => $orders, 'total' => $count]);
    }

    /**
     * @SWG\Post(
     *      path="/order",
     *      tags={"Order 订单管理"},
     *      summary="订单生成",
     *     @SWG\Parameter(
     *         name="",
     *         in="body",
     *         description="订单详情",
     *         required=true,
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"type": "2", "fee": 100, "detail": {"product_id":1, "instance_line": "11_1", "base_bandwidth": 20,
     *              "bandwidth": 20, "normal_bandwidth": 20, "order_time":"1:month", "site_count": 50, "port_count": 50, "sp_num": 1}}
     *          )
     *     ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 创建成功| !=0 创建失败",
     *          @SWG\Property(
     *              property="12",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok", "data":{"id":"118041603473"}}
     *          )
     *      )
     * )
     */
    public function create()
    {
        $data = input();

        // 检查是否存在Type参数
        $invalid = $this->validate($data, ['type' => 'require|in:1,2', 'detail' => 'require',
            'detail.product_id' => 'require|in:0,1,2,3']);
        if ($invalid !== true) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $invalid);
        }

        if ($data['type'] == OrderModel::ORDER_TYPE_RECHARGE) {            // 充值订单
            // 根据Type检查订单参数
            if (!$this->validator->scene('recharge_order')->check($data)) {
                return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
            }
        }

        if ($data['type'] == OrderModel::ORDER_TYPE_PAID) {            //消费订单
            if (in_array($data['detail']['product_id'], [1, 2])) { // 站点
                if ($data['detail']) {
                    // 根据Type检查订单参数
                    if (!$this->validator->scene('site_paid_order')->check($data)) {
                        return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
                    }
                }
            }

            if ($data['detail']['product_id'] == 3) { //非站点
                if ($data['detail']) {
                    // 根据Type检查订单参数
                    if (!$this->validator->scene('port_paid_order')->check($data)) {
                        return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
                    }
                }
            }

        }

        // 执行创建订单逻辑
        $result = $this->repository->createOrder($data);
        if (!$result) {
            Finalfail(REP_CODE_DB_ERROR, '订单创建失败！');
        }

        return Finalsuccess(['data' => $result]);
    }

    /**
     * @SWG\Get(
     *      path="/order/{id}",
     *      tags={"Order 订单管理"},
     *      summary="获取订单详情",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="Order Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 获取成功| !=0 获取失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok","data":{"type":"2","fee":"15000","detail":{"instance_line":"8",
     *     "base_bandwidth":"10","bandwidth":"10","ord_time":"3","sp_num":"2","normal_bandwidth":"10","product_id":"3",
     *     "port_count":"50","start_date":1524034570,"end_date":1531896970,"instance_id":{"ddos-g7stzro","ddos-er5xsjs"}},
     *     "create_time":1524034570,"status":1,"uid":"test@veda.com","last_update":1524034571,"pay_time":1524625176,"oid":"11804181456660"}}
     *          )
     *      )
     * )
     * 获取订单详情
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function show($id)
    {
        if (!$this->validator->scene('show_order')->check(input())) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        $order = $this->repository->getOrderById($id);
        if (!$order) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, "未找到该订单！");
        }

        // 格式化字段返回值
        $order['create_time'] = strtotime($order['create_time']);
        $order['pay_time'] = strtotime($order['pay_time'] ?? null) ?: null;
        $order['last_update'] = strtotime($order['last_update'] ?? null) ?: null;
        $order['detail']['start_date'] = strtotime($order['detail']['start_date']);
        $order['detail']['end_date'] = strtotime($order['detail']['end_date']);
        $order['detail']['fee'] = $order['fee'];

        return Finalsuccess(['data' => $order]);
    }

    /**
     * @SWG\Delete(
     *      path="/order/{id}",
     *      tags={"Order 订单管理"},
     *      summary="订单作废",
     *      @SWG\Parameter(
     *          name="id",
     *          in="path",
     *          description="订单ID",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 操作成功| !=0 操作失败",
     *          @SWG\Property(
     *              property="12",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function destroy($id)
    {
        if (!$this->validator->scene('delete_order')->check(input())) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        $order = $this->repository->getOrderById($id);
        if (!$order) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '订单不存在！');
        }

        // 更新订单状态为已作废
        if (!$this->repository->deleteOrder($id)) {
            return Finalfail(REP_CODE_ES_ERROR, '删除失败！');
        }

        return Finalsuccess();
    }
}