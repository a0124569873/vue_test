<?php

namespace app\index\model;

class DnsConfModel extends BaseModel
{
    protected $esIndex = 'dns_conf';

    protected $esType = 'type';

    //------------------------- DNS Conf -------------------------------

    const DNS_CONF_TYPE = 4;

    //------------------------- DNS Conf -------------------------------

    //------------------------- DNS Conf 类型 START -------------------------------

        const TYPE_DYNAMIC = 'dynamic'; // 动态

        const TYPE_STATIC = 'static';   // 静态

    //------------------------- DNS Conf 类型 END -------------------------------
}
