<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/2
 * Time: 14:26
 */

namespace app\index\service;


use Carbon\Carbon;

class Time extends Carbon
{
    public function __construct($time, $tz = null)
    {
        $tz = $tz ?: config('default_timezone');
        parent::__construct($time, $tz);
    }
}