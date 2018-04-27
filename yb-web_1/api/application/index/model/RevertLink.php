<?php

namespace app\index\model;
use think\Model;
use think\Db;

class RevertLink extends Model{

    protected $pk = 'id';
    protected $table = 'revert_link';

    //统计回连配置个数
    public function countReLink(){
        $count = db('revert_link')->count();
        return $count;
    }
    //分页查询回连配置
    public function selectReLink($page, $row){
        $datas = db('revert_link')->page($page, $row)->select();
        return $datas;
    }
    //查询回连配置中所有uid
    public function selectAllRelinkUid(){
        $datas = db('revert_link')->field('uid')->select();
        return $datas;
    }
    //查询回连配置用于reload
    public function selectAllRelink(){
        $datas = db('revert_link')->select();
        return $datas;
    }
    //批量或单个添加回连配置
    public function insertAllRelink($conf_arr){

        $result = array();

        foreach ($conf_arr as $key => $value) {
            
            db('revert_link')->insert($value);
            $result[] = db("revert_link")->getLastInsID();
        }

        return $result;
    }   
    //删除回连配置
    public function deleteRelinkByUid($uid_arr){
        $result = db('revert_link')->where('uid', 'IN', $uid_arr)->delete();
        return $result;
    }

    //get回连配置
    public function getRelinkByUids($uid_arr){
        $result = db('revert_link')->where('uid', 'IN', $uid_arr)->select();
        return $result;
    }
    //清除所有回连配置
    public function deleteRelinkAll(){
        $result = Db::query('DELETE FROM revert_link');
        return $result;
    }


}