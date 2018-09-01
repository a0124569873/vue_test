<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 18:52
 */

namespace app\index\repository;

/**
 * 一系列针对Model 的操作
 *
 * Class BaseRepository
 * @package app\index
 */
abstract class BaseRepository
{
    protected $model = null;

    public function getListTotal($filter, $model = null)
    {
        try {
            $model = $model ?: $this->model;

            return (int)$model->esCount($filter);
        } catch (\Exception $e) {
            return 0;
        }
    }
}