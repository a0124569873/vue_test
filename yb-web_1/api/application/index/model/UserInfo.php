<?php

namespace app\index\model;
use think\Model;
use think\Db;

class UserInfo extends Model{

    //统计用户接入信息个数
    public function countAllUserInfo(){
        $count = db('user_info')->count();
        return $count;
    }
    //分页查询所有用户接入信息
    public function selectUserInfo($page, $row){
        $datas = db('user_info')->page($page, $row)->select();
        return $datas;
    }
    //获取所有用户私有IP
    public function selectAllUserInfoPip(){
        $datas = db('user_info')->field('p_ip')->select();
        return $datas;
    }
    //查找p_ip是否存在
    public function selectPipByIp($ip){
        $data = db('user_info')->where('p_ip',$ip)->select();
        return $data;
    }
    //获取所有完整用户接入信息,用于reload
    public function selectCompleteUserInfo(){
        $datas = db('user_info')->field("p_ip,hide_ip,uid,username,interval")->where('hide_ip', '<>', '')->where("uid",'not null')->select();
        return $datas;
    }
    //添加用户接入时检查是否重复
    public function selectUserInfoByNameIp($username, $ip){
        $datas = db('user_info')->where('username', $username)->whereOr('p_ip', $ip)->select();
        return $datas;
    }
    //修改用户接入时检查是否与其他记录重复
    public function selectUserInfoByNameIpId($username, $ip, $id){
        $datas = db('user_info')->bind(['id'=>[$id,\PDO::PARAM_INT],'username'=>$username,'p_ip'=>$ip])
                 ->where('(username=:username or p_ip=:p_ip) and id<>:id')->select();
        return $datas;
    }
    //添加用户接入
    public function addUserInfo($conf){
        $result = db('user_info')->insert($conf);
        return $result;
    }
    //修改用户接入
    public function updateUserInfoById($conf){
        $result = db('user_info')->update($conf);
        return $result;
    }
    //删除用户接入
    public function deleteUserInfoById($id_arr){
        $result = db('user_info')->delete($id_arr);
        return $result;
    }
    //清除所有用户接入
    public function deleteUserInfoAll(){
        $result = Db::query('DELETE FROM user_info');
        return $result;
    }

    //
    public function selectUser_infoByIds($ids){
        $data = db('user_info')->where('id',"IN",$ids)->select();
        return $data;
    }
}