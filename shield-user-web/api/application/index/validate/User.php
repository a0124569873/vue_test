<?php

namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'email'     =>  'require|email',
        'password'  =>  'require',
        'captcha'   =>  'require',
        'mobile'    =>  'require|mobile',
        'token'     =>  'require',
        'old_pwd'   =>  'require',
        'new_pwd'   =>  'require|password',
        'real_name' =>  'require',
        'id_number' =>  'require',
        'safemail'  =>  'require|email',
        'safephone' =>  'require|mobile',
        
    ];

    protected $message = [
        'email.require'     => '11000|param missing:email',
        'email.email'       => '11001|format error:email',
        'password.require'  => '11000|param missing:password',
        'captcha.require'   => '11000|param missing:captcha',
        'mobile.require'    => '11000|param missing:mobile',
        'mobile.mobile'     => '11003|format error:mobile',
        'token.require'     => '11000|param missing:token',
        'old_pwd.require'   => '11000|param missing:old_pwd',
        'new_pwd.require'   => '11000|param missing:new_pwd',
        'new_pwd.password'  => '11002|password is not stronger enough',
        'real_name.require' => '11000|param missing:real_name',
        'id_number.require' => '11000|param missing:id_number',
        'safemail.require'  => '11000|param missing:safemail',
        'safemail.email'    => '11001|format error:safemail',
        'safephone.require' => '11000|param missing:safephone',
        'safephone.mobile'  => '11003|format error:mobile',
    ];

    protected $scene = [
        'login'             =>  ['email'=>'require', 'password', 'captcha'],
        'register'          =>  ['email', 'password', 'mobile', 'captcha'],
        'password_send'     =>  ['email', 'captcha'],
        'password_token_update'  =>  ['email', 'token', 'new_pwd'],
        'password_update'   =>  ['old_pwd', 'new_pwd'],
        'realname'          =>  ['real_name', 'id_number', 'captcha'],
        'safemail_send'     =>  ['safemail'],
        'safemail_update'   =>  ['safemail', 'token'],
        'safephone_send'    =>  ['safephone'],
        'safephone_update'  =>  ['safephone', 'token'],
    ];

    protected function mobile($value, $rule, $data) // 验证手机号
    {
        return preg_match("/^1[3456789]\d{9}$/", $value) ? true : false;
    }

    protected function password($value, $rule, $data) // 验证密码复杂度 长度至少为8位 包括数字、字母大小写
    {
        if (strlen($value) < 8 || strlen($value) > 16) {
            return false;
        }
        $n=preg_match('/[0-9]/', $value);
        $e=preg_match('/[a-zA-Z]/', $value);
        return $n && $e ? true : false;
    }
}
