<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/5/2
 * Time: 12:38
 */

namespace app\index\traits;

trait ZKQuery
{

    protected static $zkInstance = null;
    protected $zkACL = [
        ['perms' => \Zookeeper::PERM_ALL, 'scheme' => 'world', 'id' => 'anyone']
    ];


    /**
     * 获取ZK client 实例
     * @return \Zookeeper Object|null
     */
    protected static function zkInstance()
    {
        if (!self::$zkInstance) {
            $ZKHost = config('database.zk_host');
            self::$zkInstance = new \Zookeeper($ZKHost);
        }

        return self::$zkInstance;
    }


    /**
     * 将数据写入ZK节点
     *
     * @param $path
     * @param string $data
     * @return bool
     */
    public function zkSetData($path, $data = '')
    {
        try {
            $data = is_string($data) ? $data : json_encode($data, JSON_UNESCAPED_SLASHES);

            //检查node 是否存在，不存在的话，先创建
            if (!self::zkInstance()->exists($path)) {
                $this->zkCreateNode($path);
            }

            return self::zkInstance()->set($path, $data);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 创建ZK的Node节点
     * 如果节点嵌套多层，则递归创建
     *
     * @param $path
     * @param null $acl
     * @return bool
     * @throws \Exception
     */
    public function zkCreateNode($path, $acl = null)
    {

        try {
            $acl = $acl ?: $this->zkACL;
            // 检查当前ZK节点是否存在
            if (!empty($path) && !self::zkInstance()->exists($path)) {
                // 去掉首个 '/' 之前的路径为下次需要生成的路径
                $newPath = substr($path, 0, strrpos($path, '/'));
                // 递归的创建ZK节点
                $this->zkCreateNode($newPath);
                if (!empty($path) && !self::zkInstance()->create($path, '', $acl)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            throw  $e;
        }
    }

}