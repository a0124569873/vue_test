<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 17:09
 */

namespace app\index\controller;


use app\index\model\OrderModel;
use app\index\repository\OrderRepository;
use app\index\repository\PayRepository;
use app\index\traits\CheckLogin;
use think\Request;
use app\index\validate\Pay as PayValidator;

class Pay extends BaseController
{
    use CheckLogin;

    public function __construct(Request $request)
    {
        $this->repository = new PayRepository();

        $this->validator = new PayValidator();

        parent::__construct($request);
    }

    protected $beforeActionList = [
        'checkLogin' => ['only' => 'index,create']
    ];

    /**
     *
     * @SWG\Put(
     *      path="/pay/{id}",
     *      tags={"Pay 支付"},
     *      summary="订单支付",
     *      @SWG\Parameter(
     *          name="id",
     *          in="query",
     *          description="Order Id",
     *          type="integer",
     *      ),
     *      @SWG\Response(
     *          response="200",
     *          description="errcode: 0 支付成功| !=0 支付失败",
     *         @SWG\Property(
     *              property="",
     *              type="object",
     *              example={"errcode":0,"errmsg":"ok"}
     *          )
     *      )
     * )
     *
     * 订单支付
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function update($id)
    {
        $data = input();
        $data['order_id'] = $id;

        // 参数校验
        if (!$this->validator->scene('pay')->check($data)) {
            return Finalfail(REP_CODE_PARAMS_INVALID, $this->validator->getError());
        }

        // 订单信息校验
        $order = (new OrderRepository())->getOrderById($data['order_id']);
        if (!$order) {
            return Finalfail(REP_CODE_SOURCE_NOT_FOUND, '未找到该订单！');
        }

        if ($order['status'] == OrderModel::ORDER_STATUS_PAID) {
            return Finalfail(REP_CODE_ILLEGAL_OPERATION, '订单状态异常！');
        }

        $payResult = $this->repository->orderPay($data);
        if (!$payResult) {
            return Finalfail(REP_CODE_DB_ERROR, '支付失败！');
        }

        return Finalsuccess();
    }
}