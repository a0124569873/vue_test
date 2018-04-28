<?php

namespace app\index\model;
use think\Model;
use think\Db;

class HostSetConf extends Model{

    //查询主机防护参数
    public function selectProParamByGether($gether){
        $date = db('host_set_conf')->field('conf_value')->where("set_num", $gether)->select();
        return $date;
    }

    //更新配置集
    public function updateProParam($conf_num, $conf_value){
        $result = db('host_set_conf')->where('set_num',$conf_num)->update(['conf_value'=>$conf_value]);
        return $result;
    }

    //获取此配置集应用的所有的ip及配置信息
    public function selectIpAndConfValueByNum($conf_num){
        $datas = db('host_set_conf')->alias('b')->field('a.ip,b.conf_value')
                ->join('host_conf a', 'a.host_set_num = b.set_num')
                ->where('a.host_set_num', $conf_num)->select();
        return $datas;
    }
}