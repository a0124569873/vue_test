<?php

namespace app\index\model;
use think\Model;
use think\Db;

class UserModel extends Model{

    protected $table = 'users';
    protected $pk = 'u_id';

    public function login($username,$password){
        $map = [
            'username'   =>  $username,
            'password'   =>  PasswordHash($password)
        ];
		$user = db('users')->where($map)->find();
		if(is_array($user)){
			return $user;
	    }else{
            return -1;
        }
    }

    public function selectUidByUnamePwd($username, $old_pwd){
        $map = [
            'username'=>$username,
            'password'=>PasswordHash($old_pwd)
        ];
        $user = db('users')->where($map)->select();
        if(count($user)>0){
            return $user[0]['u_id'];
        }
        return -1;
    }

    public function updatePwdByUid($uid, $new_pwd){
        $result = db('users')->where("u_id",$uid)->update(['password'=>PasswordHash($new_pwd)]);
        return $result;
    }

}