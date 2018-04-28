<?php
namespace app\index\utils;
use think\Controller;

class IpFlux extends Controller {
    protected $redis;

    protected $timestamp;
    protected $last_handle_timestamp;

    protected $time30_flag;
    protected $time300_flag;
    protected $time3600_flag;

    protected $stat_json;
    protected $stat_count;
    protected $ipflux_info_30;
    protected $ipflux_info_300;
    protected $ipflux_info_3600;

    public function _initialize() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $this->ipflux_info_30 = [];
        $this->ipflux_info_300 = [];
        $this->ipflux_info_3600 = [];
    }

    protected function saveRedis($stat_json, $count) {
        $this->redis->hSet('dev_stat', 'ipflux', $stat_json);
        $this->redis->hSet('dev_stat', 'host_count', $count);
    }

    protected function save30Db() {
        $insert_start_str = "INSERT INTO CleanDB.ipflux_30 (ip,flux,time) VALUES ";
        $insert_str = "";
        $time_str = date('Y-m-d H:i:s', $this->time30_flag);
        foreach ($this->ipflux_info_30 as $ip => $if) {
            $i = $if;
            $if = [
                ceil($i[1]/$i[0]),ceil($i[2]/$i[0]),ceil($i[3]/$i[0]),ceil($i[4]/$i[0]),
                ceil($i[5]/$i[0]),ceil($i[6]/$i[0]),ceil($i[7]/$i[0]),
                $i[8],$i[9],$i[10],$i[11],$i[12],$i[13],$i[14],
                ceil($i[15]/$i[0]),ceil($i[16]/$i[0]),ceil($i[17]/$i[0]),ceil($i[18]/$i[0]),
                $i[19],$i[20],$i[21],$i[22]
            ];
            if (empty($insert_str)) {
                $insert_str .= $insert_start_str."('".$ip."','".json_encode($if)."','".$time_str."')";
            } else {
                $insert_str .= ",('".$ip."','".json_encode($if)."','".$time_str."')";
            }
        }
        if (!empty($insert_str)) {
            $file_name = "/var/tmp/ipflux30_sql_" . $this->timestamp;
            $insert_file = fopen($file_name,"w");
            fwrite($insert_file, $insert_str);
            fclose($insert_file);
            exec("nohup mysql -uroot -pveda --default-character-set=UTF8 CleanDB < $file_name && rm -rf $file_name > /dev/null 2>&1 &");
        }
    }

    protected function save300Db() {
        $insert_start_str = "INSERT INTO CleanDB.ipflux_300 (ip,flux,time) VALUES ";
        $insert_str = "";
        $time_str = date('Y-m-d H:i:s', $this->time300_flag);
        foreach ($this->ipflux_info_300 as $ip => $if) {
            $i = $if;
            $if = [
                ceil($i[1]/$i[0]),ceil($i[2]/$i[0]),ceil($i[3]/$i[0]),ceil($i[4]/$i[0]),
                ceil($i[5]/$i[0]),ceil($i[6]/$i[0]),ceil($i[7]/$i[0]),
                $i[8],$i[9],$i[10],$i[11],$i[12],$i[13],$i[14],
                ceil($i[15]/$i[0]),ceil($i[16]/$i[0]),ceil($i[17]/$i[0]),ceil($i[18]/$i[0]),
                $i[19],$i[20],$i[21],$i[22]               
            ];
            if (empty($insert_str)) {
                $insert_str .= $insert_start_str."('".$ip."','".json_encode($if)."','".$time_str."')";
            } else {
                $insert_str .= ",('".$ip."','".json_encode($if)."','".$time_str."')";
            }
        }
        if (!empty($insert_str)) {
            $file_name = "/var/tmp/ipflux300_sql_" . $this->timestamp;
            $insert_file = fopen($file_name,"w");
            fwrite($insert_file, $insert_str);
            fclose($insert_file);
            exec("nohup mysql -uroot -pveda --default-character-set=UTF8 CleanDB < $file_name && rm -rf $file_name > /dev/null 2>&1 &");
        }
    }

    protected function save3600Db() {
        $insert_start_str = "INSERT INTO CleanDB.ipflux_3600 (ip,flux,time) VALUES ";
        $insert_str = "";
        $time_str = date('Y-m-d H:i:s', $this->time3600_flag);
        foreach ($this->ipflux_info_3600 as $ip => $if) {
            $i = $if;
            $if = [
                ceil($i[1]/$i[0]),ceil($i[2]/$i[0]),ceil($i[3]/$i[0]),ceil($i[4]/$i[0]),
                ceil($i[5]/$i[0]),ceil($i[6]/$i[0]),ceil($i[7]/$i[0]),
                $i[8],$i[9],$i[10],$i[11],$i[12],$i[13],$i[14],
                ceil($i[15]/$i[0]),ceil($i[16]/$i[0]),ceil($i[17]/$i[0]),ceil($i[18]/$i[0]),
                $i[19],$i[20],$i[21],$i[22]            
            ];
            if (empty($insert_str)) {
                $insert_str .= $insert_start_str."('".$ip."','".json_encode($if)."','".$time_str."')";
            } else {
                $insert_str .= ",('".$ip."','".json_encode($if)."','".$time_str."')";
            }
        }
        if (!empty($insert_str)) {
            $file_name = "/var/tmp/ipflux3600_sql_" . $this->timestamp;
            $insert_file = fopen($file_name,"w");
            fwrite($insert_file, $insert_str);
            fclose($insert_file);
            exec("nohup mysql -uroot -pveda --default-character-set=UTF8 CleanDB < $file_name && rm -rf $file_name > /dev/null 2>&1 &");
        }
    }

    protected function reset30State() {
        $this->time30_flag = 0;
        $this->ipflux_info_30 = [];
    }

    protected function reset300State() {
        $this->time300_flag = 0;
        $this->ipflux_info_300 = [];
    }

    protected function reset3600State() {
        $this->time3600_flag = 0;
        $this->ipflux_info_3600 = [];
    }

    protected function handle30($ipflux) {
        foreach ($ipflux as $ip => $if) {
            if (empty($this->ipflux_info_30[$ip])) {
                $this->ipflux_info_30[$ip] = [
                    1,$if['in_bps'],$if['out_bps'],$if['in_pps'],$if['out_pps'],
                    $if['tcp_conn_in'],$if['tcp_conn_out'],$if['udp_conn'],
                    [$if['in_bps'],$this->timestamp],[$if['out_bps'],$this->timestamp],
                    [$if['in_pps'],$this->timestamp],[$if['out_pps'],$this->timestamp],
                    [$if['tcp_conn_in'],$this->timestamp],[$if['tcp_conn_out'],$this->timestamp],
                    [$if['udp_conn'],$this->timestamp],
                    $if['in_bps_after_clean'],$if['out_bps_after_clean'],$if['in_pps_after_clean'],$if['out_pps_after_clean'],
                    [$if['in_bps_after_clean'],$this->timestamp],[$if['out_bps_after_clean'],$this->timestamp],
                    [$if['in_pps_after_clean'],$this->timestamp],[$if['out_pps_after_clean'],$this->timestamp]
                ];
            } else {
                $i = $this->ipflux_info_30[$ip];
                $this->ipflux_info_30[$ip] = [
                    $i[0]+1,$i[1]+$if['in_bps'],$i[2]+$if['out_bps'],$i[3]+$if['in_pps'],$i[4]+$if['out_pps'],
                    $i[5]+$if['tcp_conn_in'],$i[6]+$if['tcp_conn_out'],$i[7]+$if['udp_conn'],
                    $i[8][0]<$if['in_bps']?[$if['in_bps'],$this->timestamp]:$i[8],
                    $i[9][0]<$if['out_bps']?[$if['out_bps'],$this->timestamp]:$i[9],
                    $i[10][0]<$if['in_pps']?[$if['in_pps'],$this->timestamp]:$i[10],
                    $i[11][0]<$if['out_pps']?[$if['out_pps'],$this->timestamp]:$i[11],
                    $i[12][0]<$if['tcp_conn_in']?[$if['tcp_conn_in'],$this->timestamp]:$i[12],
                    $i[13][0]<$if['tcp_conn_out']?[$if['tcp_conn_out'],$this->timestamp]:$i[13],
                    $i[14][0]<$if['udp_conn']?[$if['udp_conn'],$this->timestamp]:$i[14],
                    $i[15]+$if['in_bps_after_clean'],$i[16]+$if['out_bps_after_clean'],$i[17]+$if['in_pps_after_clean'],$i[18]+$if['out_pps_after_clean'],
                    $i[19][0]<$if['in_bps_after_clean']?[$if['in_bps_after_clean'],$this->timestamp]:$i[19],
                    $i[20][0]<$if['out_bps_after_clean']?[$if['out_bps_after_clean'],$this->timestamp]:$i[20],
                    $i[21][0]<$if['in_pps_after_clean']?[$if['in_pps_after_clean'],$this->timestamp]:$i[21],
                    $i[22][0]<$if['out_pps_after_clean']?[$if['out_pps_after_clean'],$this->timestamp]:$i[22]

                ];
            }
        }
        if ($this->time30_flag > 0) {
            $this->save30Db();
            $this->reset30State();
        }
    }

    protected function handle300($ipflux) {
        foreach ($ipflux as $ip => $if) {
            if (empty($this->ipflux_info_300[$ip])) {
                $this->ipflux_info_300[$ip] = [
                    1,$if['in_bps'],$if['out_bps'],$if['in_pps'],$if['out_pps'],
                    $if['tcp_conn_in'],$if['tcp_conn_out'],$if['udp_conn'],
                    [$if['in_bps'],$this->timestamp],[$if['out_bps'],$this->timestamp],
                    [$if['in_pps'],$this->timestamp],[$if['out_pps'],$this->timestamp],
                    [$if['tcp_conn_in'],$this->timestamp],[$if['tcp_conn_out'],$this->timestamp],
                    [$if['udp_conn'],$this->timestamp],
                    $if['in_bps_after_clean'],$if['out_bps_after_clean'],$if['in_pps_after_clean'],$if['out_pps_after_clean'],
                    [$if['in_bps_after_clean'],$this->timestamp],[$if['out_bps_after_clean'],$this->timestamp],
                    [$if['in_pps_after_clean'],$this->timestamp],[$if['out_pps_after_clean'],$this->timestamp]
                ];
            } else {
                $i = $this->ipflux_info_300[$ip];
                $this->ipflux_info_300[$ip] = [
                    $i[0]+1,$i[1]+$if['in_bps'],$i[2]+$if['out_bps'],$i[3]+$if['in_pps'],$i[4]+$if['out_pps'],
                    $i[5]+$if['tcp_conn_in'],$i[6]+$if['tcp_conn_out'],$i[7]+$if['udp_conn'],
                    $i[8][0]<$if['in_bps']?[$if['in_bps'],$this->timestamp]:$i[8],
                    $i[9][0]<$if['out_bps']?[$if['out_bps'],$this->timestamp]:$i[9],
                    $i[10][0]<$if['in_pps']?[$if['in_pps'],$this->timestamp]:$i[10],
                    $i[11][0]<$if['out_pps']?[$if['out_pps'],$this->timestamp]:$i[11],
                    $i[12][0]<$if['tcp_conn_in']?[$if['tcp_conn_in'],$this->timestamp]:$i[12],
                    $i[13][0]<$if['tcp_conn_out']?[$if['tcp_conn_out'],$this->timestamp]:$i[13],
                    $i[14][0]<$if['udp_conn']?[$if['udp_conn'],$this->timestamp]:$i[14],
                    $i[15]+$if['in_bps_after_clean'],$i[16]+$if['out_bps_after_clean'],$i[17]+$if['in_pps_after_clean'],$i[18]+$if['out_pps_after_clean'],
                    $i[19][0]<$if['in_bps_after_clean']?[$if['in_bps_after_clean'],$this->timestamp]:$i[19],
                    $i[20][0]<$if['out_bps_after_clean']?[$if['out_bps_after_clean'],$this->timestamp]:$i[20],
                    $i[21][0]<$if['in_pps_after_clean']?[$if['in_pps_after_clean'],$this->timestamp]:$i[21],
                    $i[22][0]<$if['out_pps_after_clean']?[$if['out_pps_after_clean'],$this->timestamp]:$i[22]
                ];
            }
        }
        if ($this->time300_flag > 0) {
            $this->save300Db();
            $this->reset300State();
        }
    }

    protected function handle3600($ipflux) {
        foreach ($ipflux as $ip => $if) {
            if (empty($this->ipflux_info_3600[$ip])) {
                $this->ipflux_info_3600[$ip] = [
                    1,$if['in_bps'],$if['out_bps'],$if['in_pps'],$if['out_pps'],
                    $if['tcp_conn_in'],$if['tcp_conn_out'],$if['udp_conn'],
                    [$if['in_bps'],$this->timestamp],[$if['out_bps'],$this->timestamp],
                    [$if['in_pps'],$this->timestamp],[$if['out_pps'],$this->timestamp],
                    [$if['tcp_conn_in'],$this->timestamp],[$if['tcp_conn_out'],$this->timestamp],
                    [$if['udp_conn'],$this->timestamp],
                    $if['in_bps_after_clean'],$if['out_bps_after_clean'],$if['in_pps_after_clean'],$if['out_pps_after_clean'],
                    [$if['in_bps_after_clean'],$this->timestamp],[$if['out_bps_after_clean'],$this->timestamp],
                    [$if['in_pps_after_clean'],$this->timestamp],[$if['out_pps_after_clean'],$this->timestamp]
                ];
            } else {
                $i = $this->ipflux_info_3600[$ip];
                $this->ipflux_info_3600[$ip] = [
                    $i[0]+1,$i[1]+$if['in_bps'],$i[2]+$if['out_bps'],$i[3]+$if['in_pps'],$i[4]+$if['out_pps'],
                    $i[5]+$if['tcp_conn_in'],$i[6]+$if['tcp_conn_out'],$i[7]+$if['udp_conn'],
                    $i[8][0]<$if['in_bps']?[$if['in_bps'],$this->timestamp]:$i[8],
                    $i[9][0]<$if['out_bps']?[$if['out_bps'],$this->timestamp]:$i[9],
                    $i[10][0]<$if['in_pps']?[$if['in_pps'],$this->timestamp]:$i[10],
                    $i[11][0]<$if['out_pps']?[$if['out_pps'],$this->timestamp]:$i[11],
                    $i[12][0]<$if['tcp_conn_in']?[$if['tcp_conn_in'],$this->timestamp]:$i[12],
                    $i[13][0]<$if['tcp_conn_out']?[$if['tcp_conn_out'],$this->timestamp]:$i[13],
                    $i[14][0]<$if['udp_conn']?[$if['udp_conn'],$this->timestamp]:$i[14],
                    $i[15]+$if['in_bps_after_clean'],$i[16]+$if['out_bps_after_clean'],$i[17]+$if['in_pps_after_clean'],$i[18]+$if['out_pps_after_clean'],
                    $i[19][0]<$if['in_bps_after_clean']?[$if['in_bps_after_clean'],$this->timestamp]:$i[19],
                    $i[20][0]<$if['out_bps_after_clean']?[$if['out_bps_after_clean'],$this->timestamp]:$i[20],
                    $i[21][0]<$if['in_pps_after_clean']?[$if['in_pps_after_clean'],$this->timestamp]:$i[21],
                    $i[22][0]<$if['out_pps_after_clean']?[$if['out_pps_after_clean'],$this->timestamp]:$i[22]
                ];
            }
        }
        if ($this->time3600_flag > 0) {
            $this->save3600Db();
            $this->reset3600State();
        }
    }

    public function handle($ipflux = []) {
        $this->saveRedis(json_encode($ipflux), count($ipflux['flux']));
        $this->timestamp = $ipflux['@timestamp'];
        if (!empty($this->last_handle_timestamp)) {
            if (floor($this->timestamp/30) - floor($this->last_handle_timestamp/30) == 1) {
                $this->time30_flag = floor($this->timestamp/30) * 30;
            }
            if (floor($this->timestamp/300) - floor($this->last_handle_timestamp/300) == 1) {
                $this->time300_flag = floor($this->timestamp/300) * 300;
            }
            if (floor($this->timestamp/3600) - floor($this->last_handle_timestamp/3600) == 1) {
                $this->time3600_flag = floor($this->timestamp/3600) * 3600;
            }
        }
        $this->handle30($ipflux['flux']);
        $this->handle300($ipflux['flux']);
        $this->handle3600($ipflux['flux']);
        $this->last_handle_timestamp = $this->timestamp;        
    }
}