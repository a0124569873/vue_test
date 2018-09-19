<?php

namespace app\index\model;
use think\Model;
use think\Db;

class TcpSetConf extends Model{

    //统计TCP端口防护参数
    public function countTcpByGether($gether){
        $count = db('tcp_set_conf')->where("set_num", "IN", $gether)->count();
        return $count;
    }
    
    //查询TCP端口防护参数
    public function selectTcpByGether($gether){
        $data = db('tcp_set_conf')->field('id,conf_value')->where("set_num", "IN", $gether)->select();
        return $data;
    }

    //更新操作，除修改这一条，剩下其他的配置
    public function selectUdpByGetherNoThis($gether, $id){
        $data = db('tcp_set_conf')->field('id,conf_value')->where("set_num", "IN", $gether)->where("id", "<>", $id)->select();
        return $data;
    }

    //给一个参数集添加一条TCP端口防护
    public function insertTcp($gether, $conf_value){
        $result = db('tcp_set_conf')->insert(['set_num'=>$gether, 'conf_value'=>$conf_value]);
        return $result;
    }

    //查询是否存在此id的记录
    public function selectTcpById($id){
        $data = db('tcp_set_conf')->where('id', 'IN', $id)->select();
        return $data;
    }

    //更新记录
    public function updateTcpById($id,$conf_value){
        $result = db('tcp_set_conf')->where('id', $id)->update(['conf_value'=>$conf_value]);
        return $result;
    }

    //根据id删除记录
    public function delectById($ids){
        $result = db('tcp_set_conf')->where('id', 'IN', $ids)->delete();
        return $result;
    }

    //获取此配置集应用的所有的ip及配置信息
    public function selectIpAndConfValueByNum($num){
        $datas = db('tcp_set_conf')->alias('b')->field('a.ip,b.conf_value')
                ->join('host_conf a', 'a.tcp_set_num = b.set_num','RIGHT')
                ->where('a.tcp_set_num', "IN", $num)->select();
        return $datas;
    }

    //根据id获取num
    public function selectNumById($ids){
        $data = db('tcp_set_conf')->field('set_num')->where('id', 'IN', $ids)->select();
        return $data;
    }
}