<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/9
 * Time: 11:02
 */

namespace app\index\model\mock;


class ServerConfig implements MockDataInterface, \JsonSerializable, \Countable
{
    public function data()
    {
        return $this->getData();
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function getData()
    {
        $data = file_get_contents(__DIR__ . '/ServerConfig.json');

        return json_decode($data, true);
    }

}