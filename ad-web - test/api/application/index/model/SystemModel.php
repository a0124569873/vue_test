<?php

namespace app\index\model;
use think\Model;
use think\Db;

class SystemModel extends Model{

    //查询状态日志
    public function selectStatLogs($date_arr,$type){
        $times_arr = array_map(function($timestamp){
            return date("Y-m-d H:i:s",$timestamp);
        },$date_arr);

        if($type == 'cpu'){
            $teble_name = 'cpu_10';
        }elseif($type == 'memory'){
            $teble_name = 'memory_10';
        }elseif($type == 'disk'){
            $teble_name = 'disk_10';
        }

        $dates = db($teble_name)->where("time", "IN", $times_arr)->select();
        return $dates;
    }

}