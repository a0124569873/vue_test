<?php

namespace app\index\model;
use think\Model;
use think\Db;

class BlackWhiteList extends Model{

    //统计模糊查询某一ip
    public function countListByNumTypeIpFilter($gether, $list_type, $filter_ip){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type,
            'ip_range'  =>  ['LIKE', $filter_ip."%"]
        ];
        $counts = db('black_white_list')->where($map)->count();
        return $counts;
    }
    //分页模糊查询某一ip
    public function selectListByNumTypeIpFilter($gether, $list_type, $filter_ip, $page, $row){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type,
            'ip_range'  =>  ['LIKE', $filter_ip."%"]
        ];
        $datas = db('black_white_list')->where($map)->page($page, $row)->select();
        return $datas;
    }

    // 统计某一设置集的所有黑名单或白名单
    public function countListByNumType($gether, $list_type){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type
        ];
        $counts = db('black_white_list')->where($map)->count();
        return $counts;
    }
    // 分页获取某一设置集的所有黑名单或白名单
    public function selectListByNumType($gether, $list_type, $page, $row){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type
        ];
        $datas = db('black_white_list')->where($map)->page($page, $row)->select();
        return $datas;
    }
    //插入黑白名单
    public function insertBwListByNumType($conf_arr){
        $result = db('black_white_list')->insertAll($conf_arr);
        return $result;
    }

    // 获取某一配置集一网段内的ip 
    public function selectByNumTypeIpRange($set_num, $list_type, $ip_range){
        $map = [
            'set_num'   =>  $set_num,
            'type'      =>  $list_type,
            'ip_range'  =>  ['IN', $ip_range]
        ];
        $datas = db('black_white_list')->where($map)->select();
        return $datas;
    }

    // 删除某一配置集黑白名单网段
    public function delectByNumTypeIpRange($set_num, $list_type, $ip_range){
        $map = [
            'set_num'   =>  $set_num,
            'type'      =>  $list_type,
            'ip_range'  =>  ['IN', $ip_range]
        ];
        $result = db('black_white_list')->where($map)->delete();
        return $result;
    }

    // 获取某一设置集的所有黑名单或白名单
    public function selectAllListByNumType($gether, $list_type){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type
        ];
        $datas = db('black_white_list')->field('ip_range,set_num,type')->where($map)->select();
        return $datas;
    }

    //获取现在所有黑白名单
    public function selectAllList(){
        $datas = db('black_white_list')->field('ip_range,set_num,type')->select();
        return $datas;
    }

    //删除某一设置集的黑/白名单
    public function delectAllListByNumType($gether, $list_type){
        $map = [
            'set_num'   =>  $gether,
            'type'      =>  $list_type
        ];
        $result = db('black_white_list')->field('ip_range,set_num,type')->where($map)->delete();
        return $result;
    }

}