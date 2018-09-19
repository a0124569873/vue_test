<?php

namespace app\index\model;
use think\Model;
use think\Db;

class SysConfModel extends Model{
    
    // 查询系统配置信息
    public function selectConfValue($conf_type){
        $data = db('sys_conf')->field('conf_value')->where("conf_type", $conf_type)->select();
        return $data;
    }

    // 更新系统配置信息
    public function updateConfValue($conf_type, $conf_value){
        $result = db('sys_conf')->where('conf_type', $conf_type)->update(['conf_value'=>$conf_value]);
        return $result;
    }

    //查询所有系统配置信息
    public function selectAllConfValue(){
        $data = db('sys_conf')->field('conf_type,conf_value')->select();
        return $data;
    }

}