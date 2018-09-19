<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 13:58
 */

namespace app\index\controller;


use app\index\service\Auth;
use think\Controller;
use think\Log;

class BaseController extends Controller
{
    protected $validator;

    protected $repository;

    // 日志文件
    protected $logPath = RUNTIME_PATH . 'error-log';

    // 请求参数不合法
    const ERROR_PARAMS_INVALID = 11000;

    // 需要登陆
    const ERROR_NEED_LOGIN = 12001;

    // 数据操作异常
    const ERROR_DB_ERROR = 20000;
    const ERROR_DB_ERROR_ZK = 21000;
    const ERROR_DB_ERROR_ES = 22000;

    // 请求数据不存在
    const ERROR_SOURCE_NOT_FOUND = 13001;

    // 操作非法
    const ERROR_ILLEGAL_OPERATION = 14001;

    // 操作失败
    const ERROR_FAILED_OPERATION = 14200;

    public function checkOriginIp()
    {
        //TODO 添加源站IP校验
    }

    /**
     * 添加日志异常记录
     * @param int $msg
     * @param string $lvl
     */
    public function saveLog($msg = null, $lvl = 'error')
    {
        try {
            $logFile = $this->logPath . DS . date('Ymd') . '.log';

            // 检查日志目录是否存在
            if (!is_dir(dirname($logFile))) {
                mkdir(dirname($logFile), 0755, true);
            }

            $url = request()->method() . " " . request()->url();
            $content = sprintf('[' . date('Y-m-d H:i:s') . ']' . '[%s] [%s]:' . PHP_EOL, strtoupper($lvl), $url);
            $content .= 'AUTH_INFO: ' . var_export(Auth::user(), true) . PHP_EOL;
            $content .= 'REQUEST: ' . var_export($this->getLogRequest(), true) . PHP_EOL;
            $content .= 'ERROR_MSG: ' . var_export($msg, true) . PHP_EOL;
            $content .= '---------------------------------------------------------------' . PHP_EOL;

            file_put_contents($logFile, $content, FILE_APPEND);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }

    private function getLogRequest()
    {
        return ['URL' => request()->url(), 'HEADER' => request()->header(), 'PARAM' => request()->param()];
    }

    /**
     * 控制器异常处理
     *
     * @param \Exception $e
     */
    protected function errorHandle(\Exception $e)
    {
        $this->saveLog(['file' => $e->getFile(), 'code' => $e->getCode(), 'msg' => $e->getMessage()]);
    }
}