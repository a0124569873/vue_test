<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/13
 * Time: 19:12
 */

namespace app\index\traits;


trait CheckLogin
{
    protected function checkLogin()
    {
        if (IsLogin() === 0)
            Error('12001', 'need login');
    }
}