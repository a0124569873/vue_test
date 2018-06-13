<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/30
 * Time: 18:35
 */

namespace app\index\repository;


use app\index\model\PhyConfModel;

class PhyConfRepository
{

    public function __construct()
    {
        $this->model = new PhyConfModel();
    }

    public function getPhyConf($type = null)
    {
        $filter = [];
        if ($type) {
            $filter['query']['bool']['must'][] = ["term" => ['type' => 4]];
        }

        return $this->model->esSearch($filter);
    }
}