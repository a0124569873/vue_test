<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\Db;

class Stats extends Controller {

    protected $validate;
    protected $redis;
    
    
    public function _initialize(){
        $this->redis = new \Redis();
        $this->validate = Loader::validate('Stats');
    }

    public function _empty(){
        $this->redirect('/errorpage');
    }

    protected $beforeActionList = [
        'check_login' 
    ];

    protected function check_login(){
        if(IsLogin() === 0)
            Error('12001','need login');
    }

    //【接口】设备概况及cpu、内存、硬盘使用状况接口
    public function dev(){
        if(!request()->isGet())
            return Finalfail("21002","need get method");

        if(!$this->validate->scene('dev')->check(input()))
            return Finalfail($this->validate->getError());

        $result = [];
        $conf_type = input('get.t');
        $recent = is_null(input('get.r')) ? 1 : input('get.r');
  
        foreach (explode('|', $conf_type) as $type) {
            switch($type){
                case "1":
                    $result['1'] = $this->CPUStats($recent);
                    break;
                case "2":
                    $result['2'] = $this->RAMStats($recent);
                    break;
                case "3":
                    $result['3'] = $this->HDStats($recent);
                    break;
                case "4":
                    $result['4'] = $this->LinkStat();
                    break;
                case "5":
                    $result['5'] = $this->startTime();
                    break;
                case "6":
                    $refresh = input('get.topo') == true ? true : false;
                    $result['6'] = $this->G_status($refresh);
                    break;
                default:
                    #code...
                    break;
            }
        }
        return Finalsuccess($result);
    }

    private function G_status($topo = false) {
        if ($topo) {
            $file = fopen("/usr/local/6WINDGate/etc/gtopo.txt", "r");
            $gtopo = [];
            while (!feof($file)) {
                $line = fgets($file);
                $line = explode(" ", trim($line));
                $gtopo[$line[0]] = array_slice($line, 1);
            }
            fclose($file);
            return $gtopo;
        } else {

            $file = fopen("/usr/local/6WINDGate/etc/gtopo.txt", "r");
            $glist = [];
            while (!feof($file)) {
                $line = fgets($file);
                $line = explode(" ", trim($line));
                $glist[] = $line[0];
            }
            fclose($file);

            $this->redis->pconnect('127.0.0.1');
            $gstat = $this->redis->hGetAll('G-status');
            foreach($gstat as $ip => $json) {
                if (!in_array($ip, $glist)) {
                    continue;
                }

                $gstat[$ip] = json_decode($json, true);
            }

            $gstat["server_time"] = intval(microtime(true)*1000);
            return $gstat;
        }
    }

    //【方法】获取所有链路信息
    private function LinkStat($uid = null){

        if(!request()->isGet())
            return Finalfail("21002","need get method");

        $this->redis->pconnect('127.0.0.1');

        $row = is_null(input('get.row')) ? 20 : input('get.row');
        $page = is_null(input('get.page')) ? 1 : input('get.page');

        $start = ($page -1) * $row;
        $end = $start + $row; 
        $orderby = is_null(input('get.orderby')) ? "uid" : input('get.orderby');

        $rank_arr = [];
        $rank = [];

        $result = [];

        switch ($orderby) {
            case 'uid':
                $rank_arr = $this->redis->zRevRange('uid_top', $start, $end - 1);
                break;
            
            default:
                # code...
                break;
        }

        foreach ($rank_arr as $r_json) {
            $r_arr = json_decode($r_json, true);
            $rank[] = $r_arr;
        }

        $ccount = $this->redis->hGet('link_stat', 'link_count');

        if (!$ccount){
            $ccount = 0;
        }


        $result["link_stat"]["link_stat"] = $rank;
        $result["link_stat"]["count"] = $ccount;

        return $result;
    }

    //【方法】获取cpu状态
    private function CPUStats($recent){
        if($recent == 1){
            $this->redis->pconnect('127.0.0.1');
            $stats = $this->redis->hGet('dev_stat', 'cpu');
        }else{
            $stats = $this->getSysLogs($recent, 'cpu');
        }
        return $stats;
    }
    //【方法】获取内存状态
    private function RAMStats($recent){
        if($recent == 1){
            $this->redis->pconnect('127.0.0.1');
            $stats = $this->redis->hGet('dev_stat', 'memory');
        }else{
            $stats = $this->getSysLogs($recent, 'memory');
        }
        return $stats;
    }
    //【方法】获取硬盘状态
    private function HDStats($recent){
        if($recent == 1){
            $this->redis->pconnect('127.0.0.1');
            $stats = $this->redis->hGet('dev_stat', 'disk');
        }else{
            $stats = $this->getSysLogs($recent, 'disk');
        }
        return $stats;
    }
    //【方法】获取开机启动时间
    private function startTime(){
        $this->redis->pconnect('127.0.0.1');
        $stats = $this->redis->info();
        return $stats['uptime_in_seconds'];
    }
    //【方法】查询系统状态日志方法
    private function getSysLogs($recent,$type){
        $date_arr = $this->parseCounts($recent);
        $log_arr = $this->selectStatLogs($date_arr, $type);

        $value_arr = array_map(function($time) use ($log_arr,$type){
            $value = [];
            foreach($log_arr as $k => $a){
                if(strtotime($a["time"]) == $time){
                    $value = $type == "cpu" ? ["user"=>$a["user"],"system"=>$a["system"],"idle"=>$a["idle"]] : ["total"=>$a["total"],"used"=>$a["used"]];
                }
            }
            if(empty($value)){
                $value = $type == "cpu" ? ["user"=>0,"system"=>0,"idle"=>100] : ["total"=>0,"used"=>0];
            }
            return ["value"=>$value,"timestamp"=>$time];
        },$date_arr);

        return $value_arr;
    }
    //【方法】根据周期计算时间节点
    private function parseCounts($recent){
        $now_time = strtotime("now");
        $data_arr = [];

        if(in_array($recent,["2","3","4","5","6"])){
            $end_time = date("s",$now_time) >= 30 ? date("Y-m-d H:i",$now_time).":30" : date("Y-m-d H:i",$now_time).":00" ;
        }else{
            $end_time = date("Y-m-d H",$now_time).":00:00" ;
        }
        $time = strtotime($end_time);
        switch($recent){
            case "2":
                $counts = 15*60/30;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-30 Second', $time);
                }
            break;
            case "3":
                $counts = 30*60/30;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-30 Second', $time);
                }
            break;
            case "4":
                $counts = 60;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-60 Second', $time);
                }
            break;
            case "5":
                $counts = 12*60/10;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-10 Minute', $time);
                }
            break;
            case "6":
                $counts = 24*60/30;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-30 Minute', $time);
                }
            break;
            case "7":
                $counts = 7*24/3;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-3 Hour', $time);
                }
            break;
            case "8":
                $counts = 24*60/30;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-30 Minute', $time);
                }
            break;
            case "9":
                $counts = 30*24/12;
                for($i = 0;$i <= $counts; $i++){
                    $date_arr[] = $time;
                    $time = strtotime('-12 Hour', $time);
                }
            break;
        }
        return $date_arr;
    }

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

        $dates = Db::table($teble_name)->where("time", "IN", $times_arr)->select();
        return $dates;
    }

}