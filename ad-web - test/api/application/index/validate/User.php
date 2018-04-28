<?php

namespace app\index\validate;
use think\Validate;

class User extends Validate{

    protected $rule = [
        'username'  =>  'require',
        'password'  =>  'require',
        'captcha'   =>  'require',
        'old_pwd'   =>  'require',
        'new_pwd'   =>  'require|password',
        
    ];

    protected $message  =   [
        'username.require'  =>  '10001',
        'password.require'  =>  '10002',
        'captcha.require'   =>  '10003',
        'old_pwd.require'   =>  '22001',
        'new_pwd.require'   =>  '22001',
        
    ];

    protected $scene = [
        'login'  =>  ['username','password','captcha'],
        'chpwd'  =>  ['old_pwd','new_pwd'],
        
    ];

    // 验证密码复杂度 长度至少为8位 包括数字、字母大小写
    protected function password($value,$rule,$data){
        if(strlen($value) < 8 || strlen($value) > 16){
            return "10009";
        }
        $n=preg_match('/[0-9]/', $value);
        $e=preg_match('/[a-zA-Z]/', $value);
        if( $n && $e ){
            return true;
        }else{
            return "10009";
        }
    }

}

