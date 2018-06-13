<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/28
 * Time: 17:30
 */

namespace app\index\model;


class PortModel extends BaseModel
{
    protected $esIndex = 'user_app';

    protected $esType = 'type';

    //------------------------- Port 状态 -------------------------------

    const PORT_STATUS_LINING = 1;   // 接入中
    const PORT_STATUS_NORMAL = 2;   // 正常
    const PORT_STATUS_LINK_ERR = 3; // 接入失败
    const PORT_STATUS_DELETE_ERR = 4;   // 删除失败

    //------------------------- Port 状态 -------------------------------

    //------------------------- Type -------------------------------

    const USER_APP_TYPE_PORT = 3; // 非网站防护

    //------------------------- Type -------------------------------
    
    //------------------------- 转发协议 START -------------------------------
    
        const PROTOCOL_TYPE_TCP = 'TCP';

        const PROTOCOL_TYPE_UDP = 'UDP';
    
    //------------------------- 转发协议 END -------------------------------

    const PORT_ID_PREFIX = 'app-ddos-';

}