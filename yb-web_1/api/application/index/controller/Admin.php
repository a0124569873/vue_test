<?php

namespace app\index\controller;
use app\index\model\AdminModel;
use think\Controller;
use think\Session;
use think\Request;
use think\Loader;

class Admin extends Controller{

    protected $m_admin;
    protected $v_admin;

    public function _initialize(){
        $this->m_admin = new AdminModel;  //构造管理员Model
        $this->v_admin = Loader::validate('Admin');  //构造管理员登陆验证器
    }

    protected $beforeActionList = [
        'check_login' => ['only' => 'changepwd'],
        'check_post'  => ['only' => 'login'],
        'check_get'   => ['only' => 'islogin,logout'],
    ];

    //【接口】登陆状态 
    public function islogin(){
        $user = session('user_auth');        
        if(empty($user)){
            header("Access-Control-Allow-Origin:*");
            return Finalfail('12001', '', ['isLogin'=>false]);
        }else{
            header("Access-Control-Allow-Origin:*");
            $user['isLogin'] = true;
            return Finalsuccess($user);
        }
    }

    //【接口】登陆 post
    public function login(){
        if(!$this->v_admin->scene('login')->check(input()))
            return Finalfail($this->v_admin->getError());

        $captcha = input('post.captcha');
        if(!captcha_check($captcha))
            return Finalfail('10003','captcha is wrong');

        $account  = input('post.account');
        $password = input('post.password');

        $admin = $this->m_admin->get(['account' => $account]);
        if(is_null($admin))
            return Finalfail('10001');

        if( PasswordHash($password) != $admin->password )
            return Finalfail('10002');

        $auth = [
            'id'         => $admin->id,
            'account'    => $admin->account,
            'login_ip'   => $_SERVER["REMOTE_ADDR"],
            'login_time' => NowTime()
        ];
        session('user_auth',$auth);
        session('user_auth_sign',DataAuthSign($auth));
        return Finalsuccess();
    }

    //【接口】注销
    public function logout(){
        if(IsLogin()){
            session('user_auth',null);
            session('user_auth_sign',null);
            session_destroy();
        }
        return Finalsuccess();
    }

    //【接口】修改密码
    public function chpwd(){
        if(!$this->v_admin->scene('changepwd')->check(input()))
            return Finalfail($this->v_admin->getError());

        $admin_id = session('user_auth.id');
        $old_pwd = input('post.old_pwd');
        $new_pwd = input('post.new_pwd');

        $admin = $this->m_admin->get($admin_id);
        if(PasswordHash($old_pwd) != $admin->password)
            return Finalfail('10004');

        $result = $this->m_admin->save([
            'password' => PasswordHash($new_pwd)
        ],['id' => $admin_id]);
        
        if($result == 0)
            return FinalFail('10006');
            
        return Finalsuccess();
    }

    protected function check_login(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }

    protected function check_post(){
        if(!request()->isPost())
            Error("21001","need post method");
    }

    protected function check_get(){
        if(!request()->isGet())
            Error("21002","need get method");
    }

    public function _empty(){
        $this->redirect('/errorpage');
    }

    // public function add_admin(){
    //     $account  = input('post.account');
    //     $password = input('post.password');

    //     $this->m_admin->account  = $account;
    //     $this->m_admin->password = PasswordHash($password);
    //     $this->m_admin->save();
    // }
}
