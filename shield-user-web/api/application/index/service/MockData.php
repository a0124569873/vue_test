<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/9
 * Time: 10:42
 */

namespace app\index\service;


use app\common\exception\MockDataModuleNotFound;

class MockData
{

    const MOCK_DATA_NAMESPACE = '\\app\\index\\model\\mock\\';

    protected static $instances = [];

    /**
     * 获取Module Mock实例
     * @param $module
     * @return mixed
     * @throws MockDataModuleNotFound
     */
    public static function instance($module)
    {
        if (!isset(self::$instances[$module])) {
            self::$instances[$module] = self::initModule($module);
        }

        return self::$instances[$module];
    }

    /**
     * 获取对应Module的Mock数据
     * @param string $module
     * @param null $default
     * @return mixed|null
     * @throws MockDataModuleNotFound
     */
    public static function value($module = '', $default = null)
    {
        return call_user_func([self::instance($module), 'data']) ?? $default;
    }

    /**
     *
     * @param $module
     * @return mixed
     * @throws MockDataModuleNotFound
     */
    private static function initModule($module)
    {
        try {
            if (!class_exists($module)) {
                // 如果对应的Module类不存在，使用默认的Module命名空间拼接得到
                $module = self::MOCK_DATA_NAMESPACE . ltrim($module, '\\');
            }

            return new $module;
        } catch (\Exception $e) {
            throw new MockDataModuleNotFound($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}