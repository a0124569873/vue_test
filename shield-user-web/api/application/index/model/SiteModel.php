<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 18:57
 */

namespace app\index\model;


class SiteModel extends BaseModel
{
    protected $table = 'user_domains';

    protected $esIndex = 'user_app';

    protected $esType = 'type';

    //自动写入时间戳
    protected $autoWriteTimestamp = 'datetime';

    //------------------------- 域名状态 -------------------------------

    const DOMAIN_STATUS_CREATED = 0;     // 用户已提交，待审核

    const DOMAIN_STATUS_APPROVED = 1;   // 已审核，待接入

    const DOMAIN_STATUS_LINKING = 2;    // 正在接入

    const DOMAIN_STATUS_LINKED = 3;     // 已接入，未修改DNS解析

    const DOMAIN_STATUS_NORMAL = 4;     // 正常

    const DOMAIN_STATUS_LINK_ERR = 5;   // 接入错误

    const DOMAIN_STATUS_DELETING = 6;   // 正在删除

    const DOMAIN_STATUS_DELETE_ERR = 7; // 删除异常

    //------------------------- 域名状态 -------------------------------


    ///------------------------- 域名类型 -------------------------------

    const DOMAIN_TYPE_NONE = 0; // 未接入

    const DOMAIN_TYPE_SINGLE = 1;   // 独享型

    const DOMAIN_TYPE_SHARED = 2;   // 共享型

    const DOMAIN_TYPE_PORT = 3;     // 非网站类型

    //------------------------- 域名类型 -------------------------------

    //------------------------- 域名前缀 -------------------------------

    const DOMAIN_PREFIX_HD = 'hdverify';

    //------------------------- 域名前缀 -------------------------------

    const DOMAIN_CNAME_TAIL = '.hd.vedasec.cn';

    const DOMAIN_DYNAMIC_TAIL = '-dynamic';

    //------------------------- Scope -------------------------------

    public function scopeNot80($query)
    {
        $query->whereNotIn('port', [80, 443]);
    }

    public function scope80($query)
    {
        $query->whereIn('port', [80, 443]);
    }

    //------------------------- Scope -------------------------------
}