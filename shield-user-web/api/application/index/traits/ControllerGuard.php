<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/19
 * Time: 9:39
 */

namespace app\index\traits;

use app\index\repository\SiteRepository;
use app\index\service\Auth;

/*
 * 控制器守卫
 *
 * @method checkLogin() 检查用户是否登陆
 * @method IsDDoSIps()
 * @method getDDoSIps()
 *
 */

trait ControllerGuard
{
    /**
     * 检查当前的用户是否登陆
     */
    protected function checkLogin()
    {
        if (!Auth::isLogin()) {
            Error(self::ERROR_NEED_LOGIN, 'Login required.');
        }
    }

    /**
     * 检查请求是否来自于高防节点或者已登录Web请求
     */
    public function IsDDoSIps()
    {

        //Debug 模式下不进行请求合法性检测
        if (config('app_debug')) {
            return true;
        }

        // 已登录的请求允许访问
        if (Auth::isLogin()) {
            return true;
        }

        // 检查当前请求是否来自于合法IP或已登录WEb请求
        // 未登录的请求需要进行IP校验
        $reqIp = request()->ip();
        if (!in_array($reqIp, $this->getDDoSIps())) {
            abort(response('Permission Deny!', 403));
        }
    }

    /**
     * 获取高防节点的IP
     *
     */
    private function getDDoSIps()
    {
        $ddosIps = [];
        $ddosNodes = (new SiteRepository)->getDDoSList();
        foreach ($ddosNodes as $ddosNode) {
            foreach ($ddosNode['node_ip'] as $nodeIp) {
                $ddosIps[] = $nodeIp['ip'];
            }
        }

        return array_unique($ddosIps);
    }
}