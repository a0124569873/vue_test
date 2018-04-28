<?php
namespace app\index\event;
use think\Controller;
use think\Db;

/**
 * 日志与报表 分层控制器
 */

class Logs extends Controller {

    public function attackLogs($start_time, $end_time, $target_ip, $target_port, $attack_type, $page, $row) {

        if (strlen(strval($start_time)) == 13) {
            $start_time = $start_time/1000;
        }
        if (strlen(strval($end_time)) == 13) {
            $end_time = $end_time/1000;
        }

        $where = [];
        $logs = [];
        $count = 0;
        $time = array();

        if (!empty($start_time)) {
            $where["start_time"] = [">=", date('Y-m-d H:i:s', $start_time)];
        }
        if (!empty($end_time)) {
            $where["end_time"] = ["<=", date('Y-m-d H:i:s', $end_time)];
        }
        if (!empty($target_ip)) {
            $where['target_ip'] = $target_ip;
        }
        if (!empty($target_port)) {
            $where['target_port'] = $target_port;
        }
        if (!empty($attack_type)) {
            $where['attack_type'] = $attack_type;
        }
        if (empty($page)) {
            $page = 1;
        }
        if (empty($row)) {
            $row = 1;
        }
        $count = Db::table('attack_info')->where($where)->count();
        $logs = Db::table('attack_info')
            ->field('id,attack_type,target_ip,target_port,start_time,end_time')
            ->where($where)
            ->limit(($page - 1) * $row, $row)
            ->order('start_time desc')
            ->select();

        if(!is_null(input('get.export')) && (input('get.export') == 'true') ){ // 导出
            $export_logs = Db::table('attack_info')->field('id,attack_type,target_ip,target_port,start_time,end_time')->where($where)->select();
            $file_name = '';
            $attack = ["SYN_Flood", "UDP_Flood", "CC_Attack"];
            if(isset($where['target_ip'])){
                $file_name .= $where['target_ip']."_";
            }
            if(isset($where['target_port'])){
                $file_name .= $where['target_port']."_";
            }
            if(isset($where['start_time'])){
                $file_name .= date('YmdHis', $start_time)."_";
            }
            if(isset($where['end_time'])){
                $file_name .= date('YmdHis', $end_time)."_";
            }
            if(isset($where['attack_type'])){
                $file_name .= $attack[(int)($where['attack_type']-1)]."攻击统计报表";
            }else{
                $file_name .= "攻击统计报表";
            }
            $this->_exportExcel('atteck', $export_logs, $file_name);
            return;
        }

        return ['logs'=>$logs, 'count'=>$count];
    }

    public function fluxLogs($start_time, $end_time, $host_ip, $range, $page, $row, $conn = false) {

        if (strlen(strval($start_time)) == 13) {
            $start_time = $start_time/1000;
        }
        if (strlen(strval($end_time)) == 13) {
            $end_time = $end_time/1000;
        }

        $table = 'sysflux_10';
        $where = [];
        $logs = [];
        $count = 0;
        $scale = 'min';
        $time = array();
        $range_arr = ["30","300","3600"];
        $table_pre = "sysflux_";

        if (!empty($host_ip)) {
            $table_pre = 'ipflux_';
            $where['ip'] = $host_ip;
        }
        $table = !empty($range) ? $table_pre.$range_arr[(int)($range-1)] : $table_pre."30";
        if (!empty($start_time)) {
            $where["time"][] = [">=", date('Y-m-d H:i:s', $start_time)];
        }
        if (!empty($end_time)) {
            $where["time"][] = ["<=", date('Y-m-d H:i:s', $end_time)];
        }
        if (empty($page)) {
            $page = 1;
        }
        if (empty($row)) {
            $row = 10;
        }

        $count = Db::table($table)->where($where)->count();
        $logs = Db::table($table)->field('id,flux,time')->where($where)
                ->limit(($page - 1) * $row, $row)->order('time desc')->select();

        if(!is_null(input('get.export')) && (input('get.export') == 'true') ){
            $export_logs = Db::table($table)->field('id,flux,time')->where($where)->order('time desc')->select();
            $export_logs = $this->_filteFluxLogs($export_logs, $scale, $conn);
            
            $file_name = '';
            $type = $conn ? "connect" : "flux";
            $range_name_arr = ["秒级","分钟","小时"];

            if(isset($where['ip'])){
                $file_name .= $where['ip']."_";
            }
            if(!empty($start_time)){
                $file_name .= date('YmdHis', $start_time)."_";
            }
            if(!empty($start_time)){
                $file_name .= date('YmdHis', $end_time)."_";
            }
            if($type === "connect"){
                $file_name .= $range_name_arr[(int)($range-1)]."连接统计报表";
            }elseif($type === "flux"){
                $file_name .= $range_name_arr[(int)($range-1)]."流量统计报表";
            }

            $this->_exportExcel($type, $export_logs, $file_name);
            return;
        }

        $logs = $this->_filteFluxLogs($logs, $scale, $conn);
        return ['logs'=>$logs, 'count'=>$count];
    }

    public function cleanLogs($start_time, $end_time, $target_ip, $attack_type, $page, $row) {

        if (strlen(strval($start_time)) == 13) {
            $start_time = $start_time/1000;
        }
        if (strlen(strval($end_time)) == 13) {
            $end_time = $end_time/1000;
        }

        $where = [];
        $logs = [];
        $count = 0;
        $time = array();

        if (!empty($start_time) && !empty($end_time)) {
            $where["time"] = ["between time", [date('Y-m-d H:i:s', $start_time), date('Y-m-d H:i:s', $end_time)]];
        } elseif (!empty($start_time)) {
            $where["time"] = [">=", date('Y-m-d H:i:s', $start_time)];
        } elseif (!empty($end_time)) {
            $where["time"] = ["<=", date('Y-m-d H:i:s', $end_time)];
        }
        if (!empty($target_ip)) {
            $where['target_ip'] = $target_ip;
        }
        if (!empty($attack_type)) {
            $where['attack_type'] = $attack_type;
        }
        if (empty($page)) {
            $page = 1;
        }
        if (empty($row)) {
            $row = 1;
        }
        
        $count = Db::table('attack_logs')->where($where)->count();
        $logs = Db::table('attack_logs')->field('id,attack_type,target_ip,attack_ip,time')
            ->where($where)->limit(($page - 1) * $row, $row)->order('time desc')->select();

        if(!is_null(input('get.export')) && (input('get.export') == 'true') ){
            $export_logs = Db::table('attack_logs')->field('id,attack_type,target_ip,attack_ip,time')->where($where)->select();
            $file_name = '';
            $attack = ["SYN_Flood", "UDP_Flood", "CC_Attack"];

            if(isset($where['target_ip'])){
                $file_name .= $where['target_ip']."_";
            }
            if(!empty($start_time)){
                $file_name .= date('YmdHis', $start_time)."_";
            }
            if(!empty($end_time)){
                $file_name .= date('YmdHis', $end_time)."_";
            }
            if(isset($where['attack_type'])){
                $file_name .= $attack[(int)($where['attack_type']-1)]."攻击清洗统计报表";
            }else{
                $file_name .= "攻击清洗统计报表";
            }

            $this->_exportExcel('clean', $export_logs, $file_name);
            return;
        }

        return ['logs'=>$logs, 'count'=>$count];
    }

    /**
     * 构建excel文件写入日志信息
     * @param $type 导出报表类型
     * @param $datas 日志数据
     * @param $file_name 日志文件名
     * @return void  
     */
    private function _exportExcel($type, $datas, $file_name){
        $C_excel_builder = controller('Excelbuilder', 'event');
        $C_excel_builder->exportExcel($type, $datas, $file_name);
    }

    // 过滤流量或连接统计所需的字段
    private function _filteFluxLogs($logs, $scale, $conn){
        if (!$conn) {
            $logs = array_map(function ($log) use ($scale) {
                $flux = json_decode($log['flux'], true);
                return [
                    'id'=>$log['id'],
                    'flux'=>[
                        'in_max_bps'=>$flux[7][0],
                        'in_max_bps_after_clean'=>$flux[18][0],
                        'in_max_pps'=>$flux[9][0],
                        'in_max_pps_after_clean'=>$flux[20][0],
                        'out_max_bps'=>$flux[8][0],
                        'out_max_bps_after_clean'=>$flux[19][0],
                        'out_max_pps'=>$flux[10][0],
                        'out_max_pps_after_clean'=>$flux[21][0],
                        'in_avg_bps'=>$flux[0],
                        'in_avg_bps_after_clean'=>$flux[14],
                        'in_avg_pps'=>$flux[2],
                        'in_avg_pps_after_clean'=>$flux[16],
                        'out_avg_bps'=>$flux[1],
                        'out_avg_bps_after_clean'=>$flux[15],
                        'out_avg_pps'=>$flux[3],
                        'out_avg_pps_after_clean'=>$flux[17]
                    ],
                    'time'=>$log['time']
                ];
            }, $logs);
        } else {
            $logs = array_map(function ($log) use ($scale) {
                $flux = json_decode($log['flux'], true);
                return [
                    'id'=>$log['id'],
                    'flux'=>[
                        'tcp_max_conn_in'=>$flux[11][0],
                        'tcp_max_conn_out'=>$flux[12][0],
                        'udp_max_conn'=>$flux[13][0],
                        'tcp_avg_conn_in'=>$flux[4],
                        'tcp_avg_conn_out'=>$flux[5],
                        'udp_avg_conn'=>$flux[6],
                    ],
                    'time'=>$log['time']
                ];
            }, $logs);            
        }
        return $logs;
    }
}