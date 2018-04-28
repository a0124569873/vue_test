<?php

namespace app\index\model;
use think\Model;
use think\Db;

class HostConf extends Model{

    protected $table = 'host_conf';
    
    //获取一个网段内防护主机信息
    public function selectHostsByIpRange($ip_range,$page,$row){
        $data = db('host_conf')->where("ip_range", "IN", $ip_range)->page($page,$row)->select();
        return $data;
    }

    //获取一个网段内all防护主机信息
    public function selectAllHostsByIpRange($ip_range){
        $data = db('host_conf')->where("ip_range", "IN", $ip_range)->select();
        return $data;
    }

    public function countIpRange($ip_range){
        $count = db('host_conf')->where("ip_range", "IN", $ip_range)->count();
        return $count;
    }

    //统计根据IP获取主机防护信息
    public function countHostsByIp($ip){
        $count = db('host_conf')->where('ip',$ip)->count();
        return $count;
    }

    //根据IP获取主机防护信息
    public function selectHostsByIp($ip,$page,$row){
        $data = db('host_conf')->where('ip', $ip)->page($page,$row)->select();
        return $data;
    }

    //统计所有网段主机防护信息
    public function countAllIpRange(){
        $count = db('host_conf')->group('ip_range')->count();
        return $count;
    }

    //分页查询所有主机防护信息
    public function selectAllIpRange($page,$row){
        $datas = db('host_conf')->group('ip_range')->page($page,$row)->select();
        return $datas;
    }

    //获取现有防护主机所有信息
    public function selectAllHosts(){
        $datas = db('host_conf')->select();
        return $datas;
    }

    //查询所有防护主机ip
    public function selectAllHostIp(){
        $datas = db('host_conf')->field('ip')->select();
        return $datas;
    }

    //插入防护主机配置
    public function insertAllHostConf($save_list){
        //构建insert语句写入SQL脚本
        $insert_str = "";
        foreach($save_list as $k => $s){
            if($k == 0){
                $insert_str .= "INSERT INTO ".DATABASE_NAME.".host_conf (ip_range,ip,host_set_num,tcp_set_num,udp_set_num,b_list_num,w_list_num,dns_w_list_num,conf_value) VALUES ('".
                $s['ip_range']."','".$s['ip']."','".$s['host_set_num']."','".$s['tcp_set_num']."','".$s['udp_set_num']."',".($s['b_list_num']==''?'NULL':$s['b_list_num']).",".
                ($s['w_list_num']==''?'NULL':$s['w_list_num']).",".($s['dns_w_list_num']==''?'NULL':$s['dns_w_list_num']).",'".$s['conf_value']."')";
                continue;
            }
            $insert_str .= ",('".$s['ip_range']."','".$s['ip']."','".$s['host_set_num']."','".$s['tcp_set_num']."','".$s['udp_set_num']."',".
            ($s['b_list_num']==''?'NULL':$s['b_list_num']).",".($s['w_list_num']==''?'NULL':$s['w_list_num']).",".($s['dns_w_list_num']==''?'NULL':$s['dns_w_list_num']).",'".$s['conf_value']."')";
        }

        $result = ImportSqlStr($insert_str);
        return $result;
    }

    //查询是否存在此id的记录
    public function selectHostById($id){
        $data = db('host_conf')->where('id', $id)->select();
        return $data;
    }

    //更新记录
    public function updateHostById($id,$conf_arr){
        $result = db('host_conf')->where('id', $id)->update($conf_arr);
        return $result;
    }

    //根据iprange删除
    public function delectByIpRange($ips_arr){
        $result = db('host_conf')->where('ip_range', 'IN', $ips_arr)->delete();
        return $result;
    }

    //根据id删除
    public function delectById($ids){
        $result = db('host_conf')->where('id', 'IN', $ids)->delete();
        return $result;
    }
}