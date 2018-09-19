<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/15
 * Time: 9:42
 */

namespace app\index\model;


use app\index\traits\ESQuery;
use app\index\traits\ZKQuery;
use think\Model;

class BaseModel extends Model
{

    use ESQuery, ZKQuery;

    //------------------------- ZK -------------------------------

    const HD_ZK_ACTION_CREATE = 'update';

    const HD_ZK_ACTION_DELETE = 'delete';

    const ZK_TYPE_WEB = 'web';

    const ZK_TYPE_APP = 'app';

    //------------------------- ZK -------------------------------


    public function __construct($data = [])
    {
        parent::__construct($data);
    }
}