<?php

namespace app\index\controller;
use app\index\model\UserModel;
use think\Controller;
use think\Session;
use think\Loader;

class User extends Controller{

    protected $M_user; // user model
    protected $V_user; // user validate
    protected $beforeActionList = [
        'checkPost'  => ['only' => 'login,chpwd'],
        'checkGet'   => ['only' => 'islogin,gettoken,freshtoken'],
        'checkLogin' => ['only' => 'gettoken,freshtoken,chpwd'],
        'checkValid' => ['only' => 'chpwd,gettoken,freshtoken']
    ];

    public function _initialize(){
        $this->M_user = new UserModel;
        $this->V_user = Loader::validate('User');
    }

    //【接口】登陆状态检测
    public function islogin(){
        if(IsLogin() === 0){
            return Finalsuccess(['isLogin'=>false]);
        }else{
            $user['isLogin'] = true;
            $user['username'] = session("user_auth.username");
            $user['group_id'] = session("user_auth.group_id");
            return Finalsuccess($user);
        }
    }

    //【接口】登陆
    public function login(){
        if(!$this->V_user->scene('login')->check(input()))
            return Finalfail($this->V_user->getError());

        $username = input('post.username');
        $password = input('post.password');
        $captcha  = input('post.captcha');

        if(!captcha_check($captcha))
            return Finalfail('10006');

        $udata = $this->M_user->login($username,$password);
        if( $udata < 0 )
            return Finalfail('10005');

        $auth = array(
            'username'    => $udata['username'],
            'group_id'    => $udata['group_id'],
            'access_token'=> $udata['access_token']
        );
        session('user_auth',$auth);
        session('user_auth_sign',AuthSign($auth));
        return Finalsuccess(['group_id' => $udata['group_id']]);
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
        if(!$this->V_user->scene('chpwd')->check(input())) 
            return Finalfail($this->V_user->getError());

        $old_pwd = input('post.old_pwd');
        $new_pwd = input('post.new_pwd');
        $username = session("user_auth.username");
        //check old_pwd
        $uid = $this->M_user->selectUidByUnamePwd($username, $old_pwd);
        if($uid<0)
            return Finalfail("10012");

        $result = $this->M_user->updatePwdByUid($uid, $new_pwd);
        if($result == 0){
            return Finalfail("10013");
        }elseif($result < 0){
            return Finalfail("20001");
        }
        return Finalsuccess();
    }

    //【接口】查询现在用户access_token
    public function gettoken(){
        $token = session("user_auth.access_token");
        return Finalsuccess(["access_token"=>$token]);
    }

    //【接口】刷新生成access_token
    public function freshtoken(){
        $salt = RandChars();
        $username = session("user_auth.username");
        $token = TokenHash($username.$salt);
        $result = $this->M_user->updateAccessTokenByUname($username, $token);
        if($result > 0){
            return Finalsuccess(["access_token"=>$token]);
        }else{
            return Finalfail("20001");
        }
    }

    //【前置方法】验证登陆
    protected function checkLogin(){
        if(!CheckLoginToken())
            Error('12001','need login or token error');
    }
    //【前置方法】验证设备授权
    protected function checkValid(){
        $status = CheckValid();
        if($status != '0')
            Error($status,'need valid');
    }
    //【前置方法】验证post请求
    protected function checkPost(){
        if(!request()->isPost())
            Error("21001","need post method");
    }
    //【前置方法】验证get请求
    protected function checkGet(){
        if(!request()->isGet())
            Error("21002","need get method");
    }
    //【空方法】
    public function _empty(){
        $this->redirect('/errorpage');
    }
}
