<?php
namespace app\index\event;
use app\index\model\SystemModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;
use think\Db;

/**
 * 系统状态 分层控制器
 */
class System extends Controller {

    protected $redis;
    protected $M_system;
    protected $time_now;

    public function _initialize(){
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $this->M_system = new SystemModel;
        $this->time_now = time();
    }

    // 【方法】获取系统流量日志实时监控
    public function GlobalNetLogs($ip) {

        // $recent = is_null(input('get.r')) ? 1 : input('get.r');
        $recent = 2;
        $date_arr = parseCounts($recent);
        $table_name = "";

        $table_pre = empty($ip)?"sysflux_":"ipflux_";

        if(in_array($recent,["2","3","4"])){
            $table_name = $table_pre."30";
        }elseif(in_array($recent,["5","6"])){
            $table_name = $table_pre."300";
        }elseif(in_array($recent, ["7","8","9"])) {
            $table_name = $table_pre."3600";
        }else{
            // $end_time = date("Y-m-d H",$now_time).":00:00" ;
            //select from 
        }


        $log_arr = selectFlowLogs($date_arr, $ip, $table_name);

        $value_arr = array_map(function($log) {
            $flux = json_decode($log['flux'], true);
            return [
                'max_in_bps'=>[$flux[7][0],$flux[7][1]],
                'max_out_bps'=>[$flux[8][0],$flux[8][1]],
                'tcp_conn'=>[$flux[11][0] + $flux[12][0],$flux[11][1]],
                'udp_conn'=>[$flux[13][0],$flux[13][1]],
                'max_in_bps_after_clean'=>[$flux[18][0],$flux[18][1]],
                'max_out_bps_after_clean'=>[$flux[19][0],$flux[19][1]],
                'timestamp'=>strtotime($log['time'])
            ];
        }, $log_arr);

        $fill_arr = [
            'max_in_bps'=>[0,0],
            'max_out_bps'=>[0,0],
            'tcp_conn'=>[0,0],
            'udp_conn'=>[0,0],
            'max_in_bps_after_clean'=>[0,0],
            'max_out_bps_after_clean'=>[0,0]
        ];

        $each_item_add = ["max_in_bps","max_out_bps","tcp_conn","udp_conn","max_in_bps_after_clean","max_out_bps_after_clean"];

        fill_timestamp($date_arr,$value_arr,$fill_arr,$each_item_add);

        return $value_arr;  
    }

    // 【方法】获取网卡收发包情况
    public function RealNetworkStats() {
        $interface_stat1 = $this->redis->hGetAll('interface_stat');

        $interface_stat = array();
        foreach($interface_stat1 as $eth => $json) {
            $interface_stat[$eth] = json_decode($json, true);
            $interface_stat[$eth]['id'] = substr(explode('_',$eth)[0],3);
        }
        $interface_stat = ArrKeySort($interface_stat, [['orderby'=>'id','order'=>SORT_ASC]]);
        return $interface_stat;
    }
    
    //【方法】获取cpu实时状态
    public function H_RealCpuStats($recent){

        $date_arr = parseCounts($recent);

        $cpu_table_name = "cpu_10";

        //TODO  real

        if($recent == 1){

            $cpu_stats = $this->redis->hGet('dev_stat', 'cpu');
            $cpu_stats = json_decode($cpu_stats,true);

            return [
                "cpu" => $cpu_stats
            ];

        }

        $cpu_arr = selectSystemLogs($date_arr, $cpu_table_name);


        $res_arr = array_map(function($log) {
            return ["value"=>["user" => $log["user"],"system" => $log["system"], "idle"=>$log["idle"]],"timestamp" => strtotime($log["time"])];
        }, $cpu_arr);

        $fill_arr = [
            "value" => ["user" => 0,"system" => 0, "idle"=>0]
        ];

        fill_timestamp($date_arr,$res_arr,$fill_arr,[]);

        return $res_arr;

    }

    //【方法】获取disk实时状态
    public function H_RealDiskStats($recent){

        $date_arr = parseCounts($recent);

        $disk_table_name = "disk_10";

        //TODO  real
        if($recent == 1){
            $disk_stats = $this->redis->hGet('dev_stat', 'disk');
            $disk_stats = json_decode($disk_stats,true);
            return [
                "disk" => $disk_stats
            ];

        }

        $disk_arr = selectSystemLogs($date_arr, $disk_table_name);

        $res_arr = array_map(function($log) {
            return ["value"=>["total" => $log["total"],"used" => $log["used"]],"timestamp" => strtotime($log["time"])];
        }, $disk_arr);

        $fill_arr = [
            "value" => ["total" => 0,"used" => 0]
        ];

        fill_timestamp($date_arr,$res_arr,$fill_arr,[]);

        return $res_arr;
    }

    //【方法】获取memory实时状态
    public function H_RealMemoryStats($recent){

        $date_arr = parseCounts($recent);

        $disk_table_name = "memory_10";

        //TODO  real
        if($recent == 1){
            $memory_stats = $this->redis->hGet('dev_stat', 'memory');
            $memory_stats = json_decode($memory_stats,true);
            return [
                "memory" => $memory_stats
            ];

        }

        $memory_arr = selectSystemLogs($date_arr, $disk_table_name);

        $res_arr = array_map(function($log) {
            return ["value"=>["total" => $log["total"],"used" => $log["used"]],"timestamp" => strtotime($log["time"])];
        }, $memory_arr);

        $fill_arr = [
            "value" => ["total" => 0,"used" => 0]
        ];

        fill_timestamp($date_arr,$res_arr,$fill_arr,[]);
        
        return $res_arr;
    }



     //【方法】获取cpu实时状态
    public function RealCPUStats(){
        $stats = $this->redis->hGet('dev_stat', 'cpu');

        $stats = json_decode($stats);

        // $stats = array();

        // foreach ($stats1 as $key => $value) {
        //     if ($key == "time") {
        //         $stats[$key] = $value;
        //         continue;
        //     }
        //     $stats[$key] = (float)number_format($value,2);
        // }

        if (empty($stats)) {
            $stats = ["user" => 0,"system" => 0,"idle" => 0 , "time" => time()];
        }

        return $stats;
    }

    //【方法】获取内存状态
    public function RealRAMStats() {
        $stats = $this->redis->hGet('dev_stat', 'memory');
        $stats = json_decode($stats, true);
        if (empty($stats)) {
            $stats = ["used" => 0,"total" => 0, "time" => time()];
        }
        return $stats;
    }

    //【方法】获取硬盘状态
    public function RealHDStats(){
        $stats = $this->redis->hGet('dev_stat', 'disk');
        $stats = json_decode($stats, true);
        if (empty($stats)) {
            $stats = ["used" => 0,"total" => 0, "time" => time()];
        }
        return $stats;
    }

    //【方法】获取开机启动时间
    public function startTime(){
        $stats = $this->redis->info();
        return $stats['uptime_in_seconds'];
    }
    // 【方法】获取保护主机数
    public function HostCount() {
        $stats = $this->redis->hGet('dev_stat', 'host_count');

        if (empty($stats)) {
            $stats = "0";
        }

        return $stats;
    }
    // 【方法】获取系统实时流量状态
    public function GlobalNetStat() {
        $stats = $this->redis->hGet('dev_stat', 'sysflux');
        $stats = json_decode($stats, true);

        if (empty($stats)) {
            $stats = [
                "in_bps" => 0,
                "out_bps" => 0,
                "in_pps" => 0,
                "out_pps" => 0,
                "in_submit_bps" => 0,
                "out_submit_bps" => 0,
                "in_submit_pps" => 0,
                "out_submit_pps" => 0,
                "tcp_conn_in" => 0,
                "tcp_conn_out" => 0,
                "udp_conn" => 0,
                "timestamp" => time()
            ];

            return $stats;

        }

        $stats = [
            "in_bps" => $stats['flux']['in_bps'],
            "out_bps" => $stats['flux']['out_bps'],
            "in_pps" => $stats['flux']['in_pps'],
            "out_pps" => $stats['flux']['out_pps'],
            "in_submit_bps" => $stats['flux']['in_bps_after_clean'],
            "out_submit_bps" => $stats['flux']['out_bps_after_clean'],
            "in_submit_pps" => $stats['flux']['in_pps_after_clean'],
            "out_submit_pps" => $stats['flux']['out_pps_after_clean'],
            "tcp_conn_in" => $stats['flux']['tcp_conn_in'],
            "tcp_conn_out" => $stats['flux']['tcp_conn_out'],
            "udp_conn" => $stats['flux']['udp_conn'],
            "timestamp" => $stats['@timestamp']
        ];

        return $stats;
    }
    // 【方法】分钟时间段计算
    public function MinTimeRange() {
        $time_end = floor($this->time_now / 300) * 300;
        $time_start = $cursor = $time_end - 3600 * 24 * 3;

        $time_range = [];
        while ($cursor <= $time_end) {
            $time_range[] = date('Y-m-d H:i:s', $cursor);
            $cursor += 300;
        }

        return $time_range;
    }
    // 【方法】小时时间段计算
    public  function HourTimeRange() {
        $time_end = floor($this->time_now / 3600) * 3600;
        $time_start = $cursor = $time_end - 3600 * 24 * 7;

        $time_range = [];
        while ($cursor <= $time_end) {
            $time_range[] = date('Y-m-d H:i:s', $cursor);
            $cursor += 3600;
        }

        return $time_range;
    }
    // 【方法】每日时间段计算
    public function DayTimeRange() {
        $time_end = floor($this->time_now / 3600 / 24) * 3600 * 24;
        $time_start = $cursor = $time_end - 3600 * 24 * 24;

        $time_range = [];
        while ($cursor <= $time_end) {
            $time_range[] = date('Y-m-d H:i:s', $cursor);
            $cursor += 3600 * 24;
        }

        return $time_range;
    }

    //【方法】获取系统版本信息
    public function SysVersion() {
        exec('cat /etc/version', $sysinfo);
        $sysinfo = trim($sysinfo[0]);
        $sysinfo = preg_split("/\s+/", $sysinfo);
        return $sysinfo[0]." build-".$sysinfo[2];
    }

}