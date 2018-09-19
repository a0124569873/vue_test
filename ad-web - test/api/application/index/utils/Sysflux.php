<?php
namespace app\index\utils;
use think\Controller;
use think\DB;

class Sysflux extends Controller {
    protected $redis;

    protected $timestamp;
    protected $last_handle_timestamp;

    protected $time30_flag;
    protected $time300_flag;
    protected $time3600_flag;

    protected $stat_json;
    protected $sysflux_info_30;
    protected $sysflux_info_300;
    protected $sysflux_info_3600;

    public function _initialize() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
    }
    protected function saveRedis($stat_json) {
        if (!empty($stat_json)) {
            $this->redis->hSet('dev_stat', 'sysflux', $stat_json);
        }
    }
    protected function save30Db() {
        if (!empty($this->sysflux_info_30)) {
            $s = $this->sysflux_info_30;
            $this->sysflux_info_30 = [
                ceil($s[1]/$s[0]),ceil($s[2]/$s[0]),ceil($s[3]/$s[0]),ceil($s[4]/$s[0]),
                ceil($s[5]/$s[0]),ceil($s[6]/$s[0]),ceil($s[7]/$s[0]),
                $s[8],$s[9],$s[10],$s[11],$s[12],$s[13],$s[14],
                ceil($s[15]/$s[0]),ceil($s[16]/$s[0]),ceil($s[17]/$s[0]),ceil($s[18]/$s[0]),
                $s[19],$s[20],$s[21],$s[22]
            ];
            db('sysflux_30')->insert([
                'flux'=>json_encode($this->sysflux_info_30),
                'time'=>date('Y-m-d H:i:s', $this->time30_flag)
            ]);
        }
    }
    protected function save300Db($save_data = []) {
        if (!empty($this->sysflux_info_300)) {
            $s = $this->sysflux_info_300;
            $this->sysflux_info_300 = [
                ceil($s[1]/$s[0]),ceil($s[2]/$s[0]),ceil($s[3]/$s[0]),ceil($s[4]/$s[0]),
                ceil($s[5]/$s[0]),ceil($s[6]/$s[0]),ceil($s[7]/$s[0]),
                $s[8],$s[9],$s[10],$s[11],$s[12],$s[13],$s[14],
                ceil($s[15]/$s[0]),ceil($s[16]/$s[0]),ceil($s[17]/$s[0]),ceil($s[18]/$s[0]),
                $s[19],$s[20],$s[21],$s[22]
            ];
            db('sysflux_300')->insert([
                'flux'=>json_encode($this->sysflux_info_300),
                'time'=>date('Y-m-d H:i:s', $this->time300_flag)
            ]);
        }
    }
    protected function save3600Db($save_data = []) {
        if (!empty($this->sysflux_info_3600)) {
            $s = $this->sysflux_info_3600;
            $this->sysflux_info_3600 = [
                ceil($s[1]/$s[0]),ceil($s[2]/$s[0]),ceil($s[3]/$s[0]),ceil($s[4]/$s[0]),
                ceil($s[5]/$s[0]),ceil($s[6]/$s[0]),ceil($s[7]/$s[0]),
                $s[8],$s[9],$s[10],$s[11],$s[12],$s[13],$s[14],
                ceil($s[15]/$s[0]),ceil($s[16]/$s[0]),ceil($s[17]/$s[0]),ceil($s[18]/$s[0]),
                $s[19],$s[20],$s[21],$s[22]
            ];
            db('sysflux_3600')->insert([
                'flux'=>json_encode($this->sysflux_info_3600),
                'time'=>date('Y-m-d H:i:s', $this->time3600_flag)
            ]);
        }
    }
    protected function reset30State() {
        $this->time30_flag = 0;
        $this->sysflux_info_30 = [];
    }
    protected function reset300State() {
        $this->time300_flag = 0;
        $this->sysflux_info_300 = [];
    }
    protected function reset3600State() {
        $this->time3600_flag = 0;
        $this->sysflux_info_3600 = [];
    }
    protected function handle30($sysflux) {
        if (empty($this->sysflux_info_30)) {
            $this->sysflux_info_30 = [
                1,$sysflux['in_bps'],$sysflux['out_bps'],$sysflux['in_pps'],$sysflux['out_pps'],
                $sysflux['tcp_conn_in'],$sysflux['tcp_conn_out'],$sysflux['udp_conn'],
                [$sysflux['in_bps'],$this->timestamp],[$sysflux['out_bps'],$this->timestamp],
                [$sysflux['in_pps'],$this->timestamp],[$sysflux['out_pps'],$this->timestamp],
                [$sysflux['tcp_conn_in'],$this->timestamp],[$sysflux['tcp_conn_out'],$this->timestamp],
                [$sysflux['udp_conn'],$this->timestamp],
                $sysflux['in_bps_after_clean'],$sysflux['out_bps_after_clean'],$sysflux['in_pps_after_clean'],$sysflux['out_pps_after_clean'],
                [$sysflux['in_bps_after_clean'],$this->timestamp],[$sysflux['out_bps_after_clean'],$this->timestamp],
                [$sysflux['in_pps_after_clean'],$this->timestamp],[$sysflux['out_pps_after_clean'],$this->timestamp]
            ];
        } else {
            $s = $this->sysflux_info_30;
            $this->sysflux_info_30 = [
                $s[0]+1,$s[1]+$sysflux['in_bps'],$s[2]+$sysflux['out_bps'],$s[3]+$sysflux['in_pps'],$s[4]+$sysflux['out_pps'],
                $s[5]+$sysflux['tcp_conn_in'],$s[6]+$sysflux['tcp_conn_out'],$s[7]+$sysflux['udp_conn'],
                $s[8][0]<$sysflux['in_bps']?[$sysflux['in_bps'],$this->timestamp]:$s[8],
                $s[9][0]<$sysflux['out_bps']?[$sysflux['out_bps'],$this->timestamp]:$s[9],
                $s[10][0]<$sysflux['in_pps']?[$sysflux['in_pps'],$this->timestamp]:$s[10],
                $s[11][0]<$sysflux['out_pps']?[$sysflux['out_pps'],$this->timestamp]:$s[11],
                $s[12][0]<$sysflux['tcp_conn_in']?[$sysflux['tcp_conn_in'],$this->timestamp]:$s[12],
                $s[13][0]<$sysflux['tcp_conn_out']?[$sysflux['tcp_conn_out'],$this->timestamp]:$s[13],
                $s[14][0]<$sysflux['udp_conn']?[$sysflux['udp_conn'],$this->timestamp]:$s[14],
                $s[15]+$sysflux['in_bps_after_clean'],$s[16]+$sysflux['out_bps_after_clean'],$s[17]+$sysflux['in_pps_after_clean'],$s[18]+$sysflux['out_pps_after_clean'],
                $s[19][0]<$sysflux['in_bps_after_clean']?[$sysflux['in_bps_after_clean'],$this->timestamp]:$s[19],
                $s[20][0]<$sysflux['out_bps_after_clean']?[$sysflux['out_bps_after_clean'],$this->timestamp]:$s[20],
                $s[21][0]<$sysflux['in_pps_after_clean']?[$sysflux['in_pps_after_clean'],$this->timestamp]:$s[21],
                $s[22][0]<$sysflux['out_pps_after_clean']?[$sysflux['out_pps_after_clean'],$this->timestamp]:$s[22]
            ];
        }
        if ($this->time30_flag > 0) {
            $this->save30Db();
            $this->reset30State();
        }
    }
    protected function handle300($sysflux) {
        if (empty($this->sysflux_info_300)) {
            $this->sysflux_info_300 = [
                1,$sysflux['in_bps'],$sysflux['out_bps'],$sysflux['in_pps'],$sysflux['out_pps'],
                $sysflux['tcp_conn_in'],$sysflux['tcp_conn_out'],$sysflux['udp_conn'],
                [$sysflux['in_bps'],$this->timestamp],[$sysflux['out_bps'],$this->timestamp],
                [$sysflux['in_pps'],$this->timestamp],[$sysflux['out_pps'],$this->timestamp],
                [$sysflux['tcp_conn_in'],$this->timestamp],[$sysflux['tcp_conn_out'],$this->timestamp],
                [$sysflux['udp_conn'],$this->timestamp],
                $sysflux['in_bps_after_clean'],$sysflux['out_bps_after_clean'],$sysflux['in_pps_after_clean'],$sysflux['out_pps_after_clean'],
                [$sysflux['in_bps_after_clean'],$this->timestamp],[$sysflux['out_bps_after_clean'],$this->timestamp],
                [$sysflux['in_pps_after_clean'],$this->timestamp],[$sysflux['out_pps_after_clean'],$this->timestamp]
            ];
            return;
        } else {
            $s = $this->sysflux_info_300;
            $this->sysflux_info_300 = [
                $s[0]+1,$s[1]+$sysflux['in_bps'],$s[2]+$sysflux['out_bps'],$s[3]+$sysflux['in_pps'],$s[4]+$sysflux['out_pps'],
                $s[5]+$sysflux['tcp_conn_in'],$s[6]+$sysflux['tcp_conn_out'],$s[7]+$sysflux['udp_conn'],
                $s[8][0]<$sysflux['in_bps']?[$sysflux['in_bps'],$this->timestamp]:$s[8],
                $s[9][0]<$sysflux['out_bps']?[$sysflux['out_bps'],$this->timestamp]:$s[9],
                $s[10][0]<$sysflux['in_pps']?[$sysflux['in_pps'],$this->timestamp]:$s[10],
                $s[11][0]<$sysflux['out_pps']?[$sysflux['out_pps'],$this->timestamp]:$s[11],
                $s[12][0]<$sysflux['tcp_conn_in']?[$sysflux['tcp_conn_in'],$this->timestamp]:$s[12],
                $s[13][0]<$sysflux['tcp_conn_out']?[$sysflux['tcp_conn_out'],$this->timestamp]:$s[13],
                $s[14][0]<$sysflux['udp_conn']?[$sysflux['udp_conn'],$this->timestamp]:$s[14],
                $s[15]+$sysflux['in_bps_after_clean'],$s[16]+$sysflux['out_bps_after_clean'],$s[17]+$sysflux['in_pps_after_clean'],$s[18]+$sysflux['out_pps_after_clean'],
                $s[19][0]<$sysflux['in_bps_after_clean']?[$sysflux['in_bps_after_clean'],$this->timestamp]:$s[19],
                $s[20][0]<$sysflux['out_bps_after_clean']?[$sysflux['out_bps_after_clean'],$this->timestamp]:$s[20],
                $s[21][0]<$sysflux['in_pps_after_clean']?[$sysflux['in_pps_after_clean'],$this->timestamp]:$s[21],
                $s[22][0]<$sysflux['out_pps_after_clean']?[$sysflux['out_pps_after_clean'],$this->timestamp]:$s[22]
            ];
        }
        if ($this->time300_flag > 0) {
            $this->save300Db();
            $this->reset300State();
        }
    }
    protected function handle3600($sysflux) {
        if (empty($this->sysflux_info_3600)) {
            $this->sysflux_info_3600 = [
                1,$sysflux['in_bps'],$sysflux['out_bps'],$sysflux['in_pps'],$sysflux['out_pps'],
                $sysflux['tcp_conn_in'],$sysflux['tcp_conn_out'],$sysflux['udp_conn'],
                [$sysflux['in_bps'],$this->timestamp],[$sysflux['out_bps'],$this->timestamp],
                [$sysflux['in_pps'],$this->timestamp],[$sysflux['out_pps'],$this->timestamp],
                [$sysflux['tcp_conn_in'],$this->timestamp],[$sysflux['tcp_conn_out'],$this->timestamp],
                [$sysflux['udp_conn'],$this->timestamp],
                $sysflux['in_bps_after_clean'],$sysflux['out_bps_after_clean'],$sysflux['in_pps_after_clean'],$sysflux['out_pps_after_clean'],
                [$sysflux['in_bps_after_clean'],$this->timestamp],[$sysflux['out_bps_after_clean'],$this->timestamp],
                [$sysflux['in_pps_after_clean'],$this->timestamp],[$sysflux['out_pps_after_clean'],$this->timestamp]
            ];
            return;
        } else {
            $s = $this->sysflux_info_3600;
            $this->sysflux_info_3600 = [
                $s[0]+1,$s[1]+$sysflux['in_bps'],$s[2]+$sysflux['out_bps'],$s[3]+$sysflux['in_pps'],$s[4]+$sysflux['out_pps'],
                $s[5]+$sysflux['tcp_conn_in'],$s[6]+$sysflux['tcp_conn_out'],$s[7]+$sysflux['udp_conn'],
                $s[8][0]<$sysflux['in_bps']?[$sysflux['in_bps'],$this->timestamp]:$s[8],
                $s[9][0]<$sysflux['out_bps']?[$sysflux['out_bps'],$this->timestamp]:$s[9],
                $s[10][0]<$sysflux['in_pps']?[$sysflux['in_pps'],$this->timestamp]:$s[10],
                $s[11][0]<$sysflux['out_pps']?[$sysflux['out_pps'],$this->timestamp]:$s[11],
                $s[12][0]<$sysflux['tcp_conn_in']?[$sysflux['tcp_conn_in'],$this->timestamp]:$s[12],
                $s[13][0]<$sysflux['tcp_conn_out']?[$sysflux['tcp_conn_out'],$this->timestamp]:$s[13],
                $s[14][0]<$sysflux['udp_conn']?[$sysflux['udp_conn'],$this->timestamp]:$s[14],
                $s[15]+$sysflux['in_bps_after_clean'],$s[16]+$sysflux['out_bps_after_clean'],$s[17]+$sysflux['in_pps_after_clean'],$s[18]+$sysflux['out_pps_after_clean'],
                $s[19][0]<$sysflux['in_bps_after_clean']?[$sysflux['in_bps_after_clean'],$this->timestamp]:$s[19],
                $s[20][0]<$sysflux['out_bps_after_clean']?[$sysflux['out_bps_after_clean'],$this->timestamp]:$s[20],
                $s[21][0]<$sysflux['in_pps_after_clean']?[$sysflux['in_pps_after_clean'],$this->timestamp]:$s[21],
                $s[22][0]<$sysflux['out_pps_after_clean']?[$sysflux['out_pps_after_clean'],$this->timestamp]:$s[22]
            ];
        }
        if ($this->time3600_flag > 0) {
            $this->save3600Db();
            $this->reset3600State();
        }        
    }
    public function handle($sysflux = []) {
        $this->saveRedis(json_encode($sysflux));
        $this->timestamp = $sysflux['@timestamp'];
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
        $this->handle30($sysflux['flux']);
        $this->handle300($sysflux['flux']);
        $this->handle3600($sysflux['flux']);
        $this->last_handle_timestamp = $this->timestamp;
    }
}