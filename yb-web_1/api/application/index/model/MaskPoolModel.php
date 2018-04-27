<?php

namespace app\index\model;
use think\Model;
use think\Db;

class MaskPoolModel extends Model{

    protected $pk = 'id';
    protected $table = 'hide_ip_pool';

    //统计伪装原型池中ip个数
    public function countMaskPool(){
        $count = db('hide_ip_pool')->count();
        return $count;
    }
    //分页查询伪装原型池中所有ip
    public function selectMaskPool($page, $row){
        $datas = db('hide_ip_pool')->page($page, $row)->order('id desc')->select();
        return $datas;
    }
    //查询一个网段内伪装原型ip
    public function selectMaskPoolByRange($ip_range){
        $datas = db('hide_ip_pool')->field('ip')->where('ip_range',$ip_range)->select();
        return $datas;
    }
    //查询伪装原型池中所有ip
    public function selectWholeMaskPoolIp(){
        $datas = db('hide_ip_pool')->select();
        return $datas;
    }
    //查询伪装原型池中是否存在改ip
    public function selectHideIpByIp($ip){
        $datas = db('hide_ip_pool')->where('ip',$ip)->select();
        return $datas;
    }
    //批量或单个添加原型池ip
    public function insertAllMaskPool($ip_arr){
        $result = db('hide_ip_pool')->insertAll($ip_arr);
        return $result;
    }   
    //删除伪装原型ip池中ip
    public function deleteMaskPoolByIp($ip_arr){

        $result = db('hide_ip_pool')->where('ip','IN', $ip_arr)->delete();

        return $result;
    }
    //清除所有伪装原型ip池中ip
    public function deleteMaskPoolAll(){
        $result = Db::query('DELETE FROM hide_ip_pool');
        return $result;
    }
}