<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/31
 * Time: 14:37
 */

namespace app\common\exception;


class ElasticSearchException extends \Exception
{
    protected $code = REP_CODE_ES_ERROR;
}