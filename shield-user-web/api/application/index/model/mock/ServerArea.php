<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/9
 * Time: 15:09
 */

namespace app\index\model\mock;


class ServerArea implements MockDataInterface
{

    public function data()
    {
        $mockArea = [
            [
                "text" => "北京",
                "value" => 11,
            ],
            [
                "text" => "天津",
                "value" => 12,
            ]
        ];

        return $mockArea;
    }

}