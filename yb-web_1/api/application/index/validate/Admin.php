<?php

namespace app\index\validate;
use think\Validate;

class Admin extends Validate{

    protected $rule = [
        'account'    =>  'require',
        'password'   =>  'require',
        'captcha'    =>  'require|alphaNum',
        'old_pwd'    =>  'require',
        'new_pwd'    =>  'require|formatpwd',
        
    ];

    protected $message  =   [
        'account.require'   => '22001',
        'password.require'  => '22001',
        'captcha.require'   => '22001',
        'captcha.alphaNum'  => '11005',
        'old_pwd.require'   => '22001',
        'new_pwd.require'   => '22001',
        
    ];

    protected $scene = [
        'login'      =>  ['account', 'password', 'captcha'],
        'changepwd'  =>  ['old_pwd', 'new_pwd'],
        
    ];

    // 验证密码复杂度 长度至少为8位 包括数字、字母大小写
    protected function formatpwd($value,$rule,$data){
        if(strlen($value) < 8 || strlen($value) > 16){
            return "10005";
        }
        $n=preg_match('/[0-9]/', $value);
        $e=preg_match('/[a-zA-Z]/', $value);
        if( $n && $e ){
            return true;
        }else{
            return "10005";
        }
    }

}

