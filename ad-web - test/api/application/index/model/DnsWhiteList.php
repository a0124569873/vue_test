<?php

namespace app\index\model;
use think\Model;
use think\Db;

class DnsWhiteList extends Model{

    //统计模糊查询某一ip
    public function countListByNumFilter($gether, $filter_ip){
        $map = [
            'set_num'   =>  $gether,
            'ip_range'  =>  ['LIKE', $filter_ip."%"]
        ];
        $counts = db('dns_white_list')->where($map)->count();
        return $counts;
    }
    //分页模糊查询某一ip
    public function selectListByNumFilter($gether, $filter_ip, $page, $row){
        $map = [
            'set_num'   =>  $gether,
            'ip_range'  =>  ['LIKE', $filter_ip."%"]
        ];
        $datas = db('dns_white_list')->where($map)->page($page, $row)->select();
        return $datas;
    }

    // 统计某一设置集的所有黑名单或白名单
    public function countListByNum($gether){
        $map = [
            'set_num'   =>  $gether
        ];
        $counts = db('dns_white_list')->where($map)->count();
        return $counts;
    }
    // 分页获取某一设置集的所有黑名单或白名单
    public function selectListByNum($gether, $page, $row){
        $map = [
            'set_num'   =>  $gether
        ];
        $datas = db('dns_white_list')->where($map)->page($page, $row)->select();
        return $datas;
    }
    //插入黑白名单
    public function insertBwList($conf_arr){
        $result = db('dns_white_list')->insertAll($conf_arr);
        return $result;
    }

    // 获取某一配置集一网段内的ip 
    public function selectByNumIpRange($set_num, $ip_range){
        $map = [
            'set_num'   =>  $set_num,
            'ip_range'  =>  ['IN', $ip_range]
        ];
        $datas = db('dns_white_list')->where($map)->select();
        return $datas;
    }

    // 删除某一配置集黑白名单网段
    public function delectByNumIpRange($set_num, $ip_range){
        $map = [
            'set_num'   =>  $set_num,
            'ip_range'  =>  ['IN', $ip_range]
        ];
        $result = db('dns_white_list')->where($map)->delete();
        return $result;
    }

    // 获取某一设置集的所有黑名单或白名单
    public function selectAllListByNum($gether){
        $map = [
            'set_num'   =>  $gether
        ];
        $datas = db('dns_white_list')->field('ip_range,set_num')->where($map)->select();
        return $datas;
    }

    //获取现在所有黑白名单
    public function selectAllList(){
        $datas = db('dns_white_list')->field('ip_range,set_num')->select();
        return $datas;
    }

    //删除某一设置集的黑/白名单
    public function delectAllListByNum($gether){
        $map = [
            'set_num'   =>  $gether
        ];
        $result = db('dns_white_list')->field('ip_range,set_num')->where($map)->delete();
        return $result;
    }

}