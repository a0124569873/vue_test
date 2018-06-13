<?php

namespace app\common\exception;

use app\index\controller\BaseController;
use Elasticsearch\Common\Exceptions\BadRequest400Exception;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use Exception;
use think\exception\Handle;

class Handler extends Handle
{

    public function render(Exception $e)
    {
        // 参数验证错误
        // if ($e instanceof ValidateException) {
        //     return json($e->getError(), 422);
        // }
        //------------------------- ES Exceptions -------------------------------

        //请求异常
        if ($e instanceof NoNodesAvailableException) {  // 没有活跃节点
            return response(json(['errcode' => 20001, 'errmsg' => $e->getMessage()]));
        }

        if ($e instanceof BadRequest400Exception) { // 查询构造错误
            return Finalfail(BaseController::ERROR_DB_ERROR_ES, $e->getMessage());
        }

        if ($e instanceof ElasticSearchException) {
            return Finalfail(REP_CODE_ES_ERROR, $e->getMessage());
        }

        //------------------------- ES Exceptions -------------------------------

        //------------------------- Zookeeper Exception -------------------------------

        if ($e instanceof ZookeeperException) {
            return Finalfail(BaseController::ERROR_DB_ERROR_ZK, $e->getMessage());
        }

        //------------------------- Zookeeper Exception -------------------------------

        if ($e instanceof PermissionDenyException) {
            return Finalfail(REP_CODE_PERMISSION_DENY, '没有权限进行操作！');
        }

        //TODO::开发者对异常的操作
        //可以在此交由系统处理
        return parent::render($e);
    }

}