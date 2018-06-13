<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/15
 * Time: 10:13
 */

namespace app\index\traits;


trait Query
{

    /**
     * Model Find 不抛出任何异常
     * @param array|string|Query|\Closure $data
     * @return array|false|null|\PDOStatement|string|Model
     */
    public function findWithOutErr($data = null)
    {
        try {
            $result = $this->find();

            return $result;
        } catch (\Exception $e) {
            return null;
        }
    }
}