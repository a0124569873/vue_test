<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 17:55
 */

namespace app\index\service;

use app\index\model\UserModel;

/**
 * 用户登陆及处理类
 * Class Auth
 * @package app\index\service
 */
class Auth
{
    const USER_INFO_SESSION_KEY = 'user_auth';
    const USER_SIGN_SESSION_KEY = 'user_auth_sign';

    /**
     * 返回当前登陆用户的信息
     * @return array|null
     */
    public static function user()
    {
        return session(self::USER_INFO_SESSION_KEY) ?: null;
    }

    /**
     * 获取当前登陆用户的ID
     *
     * @return mixed|null
     */
    public static function id()
    {
        $userInfo = self::user();

        return !empty($userInfo) ? $userInfo['id'] : null;
    }

    /**
     * 用户登录
     * @param array $auth
     * @return array
     * @throws \Exception
     */
    public static function login($auth)
    {
        try {
            $auth = [
                'id' => $auth['email'],
                'login_time' => date("Y-m-d H:i:s")
            ];
            session(self::USER_INFO_SESSION_KEY, $auth);
            session(self::USER_SIGN_SESSION_KEY, AuthSign($auth));

            return $auth;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 注销登录
     * @return null
     */
    public static function logout()
    {
        try {
            session(self::USER_INFO_SESSION_KEY, null);
            session(self::USER_SIGN_SESSION_KEY, null);

            return;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * 检查当前用户是否登陆
     *
     * @return bool true 已登录| false 未登录
     */
    public static function isLogin()
    {
        return (bool)!empty(self::user());
    }
}
