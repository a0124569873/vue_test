<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/30
 * Time: 16:11
 */

namespace app\common\exception;


class ZookeeperException extends \Exception
{
    protected $code = REP_CODE_ZK_ERROR;
}