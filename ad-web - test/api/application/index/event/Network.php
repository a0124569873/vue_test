<?php
namespace app\index\event;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;
use think\Db;

/**
 * 网络及流量 分层控制器
 */
class Network extends Controller {

    protected $redis;
    protected $time_now;

    public function _initialize() {
        $this->redis = new \Redis();
        $this->time_now = time();
    }

    // 【方法】获取实时流量状态
    public function RealFlux($recent,$ip) {

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

            //TODO real stats
            $stats = [];
            $this->redis->pconnect('127.0.0.1');
            if (!empty($ip)) {
                $stats = $this->redis->hGet('dev_stat', 'ipflux');
                $stats = json_decode($stats, true);
                if (!isset($stats["flux"][$ip])) {
                    Error("ip not find!");
                }
                $stats["flux"] = $stats["flux"][$ip];
            } else {
                $stats = $this->redis->hGet('dev_stat', 'sysflux');
                $stats = json_decode($stats, true);
            }

            if (empty($stats))
                return [
                    'in_bps'=> 0,
                    'out_bps'=> 0,
                    'tcp_conn'=> 0,
                    'udp_conn'=> 0,
                    'in_bps_after_clean'=> 0,
                    'out_bps_after_clean'=> 0,
                    'timestamp'=> time()
                ];
            return [
                'in_bps'=>$stats["flux"]["in_bps"],
                'out_bps'=>$stats["flux"]["out_bps"],
                'tcp_conn'=>$stats["flux"]["tcp_conn_in"] + $stats["flux"]["tcp_conn_out"],
                'udp_conn'=>$stats["flux"]["udp_conn"],
                'in_bps_after_clean'=>$stats["flux"]["in_bps_after_clean"],
                'out_bps_after_clean'=>$stats["flux"]["out_bps_after_clean"],
                'timestamp'=>$stats["@timestamp"]
            ];
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

    //【方法】主机流量排名
    public function HostFluxTop($orderby, $limit, $desc) {

        # code...
        if($desc == "true"){
            $desc = "desc";
        }else{
            $desc = "asc";
        }

        $this->redis->pconnect('127.0.0.1');
        $ip_arr = $this->redis->hGet('dev_stat', 'ipflux');
        $ip_arr = json_decode($ip_arr, true);
        $ip_arr = $ip_arr["flux"];

        if($orderby == "tcp_conn"){
            $ip_arr_tmp = [];
            foreach ($ip_arr as $key => $value) {
                $ip_arr_tmp[$key] = $value;
                $ip_arr_tmp[$key]["tcp_conn"] = $value["tcp_conn_in"] + $value["tcp_conn_out"];
            }
            $ip_arr = $ip_arr_tmp;
        }

        $sort_param = array();
        $sort_param["order"] = $desc;
        $sort_param["orderby"] = $orderby;

        $res_arr = ArrKeySort($ip_arr,[$sort_param]);
        $rank_arr = array_slice($res_arr, 0,$limit);

        $res_arr = array();
        foreach ($rank_arr as $key => $value) {
            array_push($res_arr, [
                "ip"=>$key,
                "in_bps"=>$value["in_bps"],
                "in_bps_after_clean"=>$value["in_bps_after_clean"],
                "out_bps"=>$value["out_bps"],
                "out_bps_after_clean"=>$value["out_bps_after_clean"],
                "in_pps"=>$value["in_pps"],
                "in_pps_after_clean"=>$value["in_pps_after_clean"],
                "out_pps"=>$value["out_pps"],
                "out_pps_after_clean"=>$value["out_pps_after_clean"],
                "tcp_conn"=>$value["tcp_conn_in"] + $value["tcp_conn_out"],
                "udp_conn"=>$value["udp_conn"],
                "timestamp"=>time()
            ]);
        }
        
        return $res_arr;
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

}