<?php

namespace app\index\model;

use think\Model;
use think\Db;

class ServerModel extends BaseModel
{

    protected $esIndex = 'phy_conf';

    protected $esType = 'type';


    //------------------------- Server 类型 -------------------------------
    const SERVER_TYPE_WEB_ALONE = 1;   //独享型

    const SERVER_TYPE_WEB_SHARED = 2; //共享型

    const SERVER_TYPE_WEB_PORT = 3; // 非网站类
    //------------------------- Server 类型 -------------------------------

    /**
     * 查询某个用户所添加的服务器列表
     * @param int $uid 用户id
     * @param int $page 分页页码
     * @param int $row 每页显示行数limit
     * @return  array $data 查询到的结果
     */
    public function get_user_servers($uid, $page, $row)
    {
        $datas = Db::table("user_server")->alias("a")
            ->field("s.server_ip, s.thr_in, s.thr_out, a.stat, s.last_update")
            ->join("servers s", "a.server_id=s.server_id")
            ->join("users u", "a.user_id=u.u_id")
            ->where("a.user_id", $uid)
            ->page($page, $row)
            ->select();

        return $datas;
    }

    /**
     * 统计某个用户所有服务器个数
     * @param int $uid 用户id
     * @return  int $count 查询到的结果
     */
    public function count_user_servers($uid)
    {
        $count = Db::table("user_server")->where("user_id", $uid)->count();

        return $count;
    }

    /**
     * 获取所有server的ip与id
     * @return  array $data 查询到的结果
     */
    public function get_servers()
    {
        $datas = Db::table("servers")->field("server_ip as ip, server_id as id")->select();

        return $datas;
    }

    /**
     * 获取用户添加的ip
     * @param int $uid 用户id
     * @param array $server_arr 所添加的ip数组
     * @return  array $data 查询到的结果
     */
    public function exist_user_servers($uid, $server_arr)
    {
        if (is_array($server_arr)) {
            $server_str = implode(",", $server_arr);
        } else {
            $server_str = $server_arr;
        }
        $data = Db::table("user_server")->alias("a")
            ->field("a.server_id")
            ->join("servers s", "a.server_id=s.server_id")
            ->join("users u", "a.user_id=u.u_id")
            ->where("a.user_id", $uid)
            ->where("s.server_ip", "IN", $server_str)
            ->select();

        return $data;
    }

    /**
     * 获取用户审核通过的ip
     * @param int $uid 用户id
     * @param array $server_arr 所添加的ip数组
     * @return  array $data 查询到的结果
     */
    public function check_user_servers($uid, $server_arr)
    {
        if (is_array($server_arr)) {
            $server_str = implode(",", $server_arr);
        } else {
            $server_str = $server_arr;
        }
        $data = Db::table("user_server")->alias("a")
            ->field("a.server_id")
            ->join("servers s", "a.server_id=s.server_id")
            ->join("users u", "a.user_id=u.u_id")
            ->where("a.user_id", $uid)
            ->where("s.server_ip", "IN", $server_str)
            ->where("a.stat", "2")
            ->select();

        return $data;
    }

    /**
     * server关联到用户
     * @param int $uid 用户id
     * @param array $server_id_arr 所添加的服务器id数组
     * @return  array $data 查询到的结果
     */
    public function add_user_servers($uid, $server_id_arr)
    {
        $data = [];
        foreach ($server_id_arr as &$server) {
            $a["server_id"] = $server;
            $a["user_id"] = $uid;
            $a["stat"] = 1;
        }
        $data[] = $a;
        $result = Db::table('user_server')->insertAll($data);

        return $result;
    }

    /**
     * 更新或批量更新服务器阈值
     * @param   array $datas 更新的阈值
     * @return  int     $i 更新成功的条数
     */
    public function update_servers($datas)
    {
        $i = 0;
        foreach ($datas as $d) {
            $result = Db::table('servers')->update($d);
            $result > 0 ? $i++ : $i;
        }

        return $i;
    }

}
    

