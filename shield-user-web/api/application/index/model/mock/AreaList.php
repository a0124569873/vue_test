<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/25
 * Time: 11:22
 */

namespace app\index\model\mock;


class AreaList implements MockDataInterface
{
    public function data()
    {
        return $this->getData();
    }

    public function getData()
    {
        $data = file_get_contents(__DIR__ . '/AreaList.json');

        return json_decode($data, true);
    }
}