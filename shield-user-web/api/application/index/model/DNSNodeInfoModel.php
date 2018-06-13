<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/12
 * Time: 11:13
 */

namespace app\index\model;


class DNSNodeInfoModel extends BaseModel
{
    protected $esIndex = 'dns_node_info';
    protected $esType = 'type';
}