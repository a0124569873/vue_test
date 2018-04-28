<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use send_mail\SendMail;
use app\index\utils\Sysflux;
use app\index\utils\IpFlux;
use think\DB;
define("FP_STATUS_SHM_KEY", "/tmp/ddos_status");
define("FP_STATUS_SHM_SIZE",33554432);
define("FP_LOG_SHM_KEY", "/tmp/ddos_log");
define("FP_LOG_SHM_SIZE",16777216);

class Metrics extends Command
{
    protected $sysflux_stat;
    protected $ipflux_stat;
    protected $last_flux_update;
    protected $redis;
    protected $Sysflux;
    protected $IpFlux;

    protected function configure()
    {
        $this->setName('metrics')->setDescription('handle metrics ... ');
    }

    protected function execute(Input $input, Output $output)
    {
        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: metrics';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }
            cli_set_process_title($p_name);
            $output->writeln('metrics process started on pid ' . posix_getpid());
            $this->metrics();
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: logs_pusher';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }

            cli_set_process_title($p_name);
            $output->writeln('logs_pusher process started on pid ' . posix_getpid());
            $this->logsPusher();
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: logs_handle';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }

            cli_set_process_title($p_name);
            $output->writeln('logs_handle process started on pid ' . posix_getpid());
            $this->logsHandle();
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: flux_pusher';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }

            cli_set_process_title($p_name);
            $output->writeln('flux_pusher process started on pid ' . posix_getpid());
            $this->fluxPusher();
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: sysflux_handle';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }

            cli_set_process_title($p_name);
            $output->writeln('sysflux_handle process started on pid ' . posix_getpid());
            $this->sysfluxHandle();
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {

            $p_name = 'PHP Metrics: ipflux_handle';
            $p_pid = exec("ps -ef |grep '".$p_name."' |grep -v grep | awk '{print $2}'");
            if(!empty($p_pid)){
                exit;
            }

            cli_set_process_title($p_name);
            $output->writeln('ipflux_handle process started on pid ' . posix_getpid());
            $this->ipfluxHandle();
            exit;
        }

        sleep(1);
        
        $output->writeln('All php handle processes started ... ');
    }

    protected function metrics() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $this->redis->delete(array('cpu_list','memory_list','disk_list', 'interface_list'));
        while (true) {
            $metric = $this->redis->blPop(array('cpu_list','memory_list','disk_list','interface_list'), 10);
            if (empty($metric)) {
                continue;
            }
            // CPU数据处理
            if ($metric[0] == 'cpu_list') {
                $cpu_info = json_decode($metric[1], true);
                $cpu_info['@timestamp'] = strtotime($cpu_info['@timestamp']);
                if ($cpu_info['cmd']['cpuusage']['pct'] != 0) {
                    $cpu_stat = [
                        'user' => $cpu_info['cmd']['cpuusage']['pct'],
                        'system' => 0,
                        'idle' => 100 - $cpu_info['cmd']['cpuusage']['pct'],
                        'time' => date('Y-m-d H:i:s', $cpu_info['@timestamp'])
                    ];
                    if (isset($last_cpu_update) && (floor($cpu_info['@timestamp'] / 10) - floor($last_cpu_update / 10) == 1)) {
                        $cpu_stat['time'] = date('Y-m-d H:i:s', floor($cpu_info['@timestamp']/10) * 10);
                        db('cpu_10')->insert($cpu_stat);
                    }
                    $last_cpu_update = $cpu_info['@timestamp'];
                    $this->redis->hSet('dev_stat', 'cpu', json_encode($cpu_stat));
                }
            }
            // 内存数据处理
            if ($metric[0] == 'memory_list') {
                $memory_info = json_decode($metric[1], true);
                $memory_info['@timestamp'] = strtotime($memory_info['@timestamp']);
                $memory_stat = [
                    'used' => $memory_info['system']['memory']['used']['bytes'],
                    'total' => $memory_info['system']['memory']['total'],
                    'time' => date('Y-m-d H:i:s', $memory_info['@timestamp'])
                ];
                if ($memory_info['@timestamp'] % 10 == 0) {
                    db('memory_10')->insert($memory_stat);
                }
                $this->redis->hSet('dev_stat', 'memory', json_encode($memory_stat));
            }
            // 硬盘数据处理
            if ($metric[0] == 'disk_list') {
                $disk_info = json_decode($metric[1], true);
                $disk_info['@timestamp'] = strtotime($disk_info['@timestamp']);
                $disk_stat = [
                    'used' => $disk_info['system']['fsstat']['total_size']['used'],
                    'total' => $disk_info['system']['fsstat']['total_size']['total'],
                    'time' => date('Y-m-d H:i:s', $disk_info['@timestamp'])
                ];
                if ($disk_info['@timestamp'] % 10 == 0) {
                    db('disk_10')->insert($disk_stat);
                }
                $this->redis->hSet('dev_stat', 'disk', json_encode($disk_stat));
            } 
            // 网卡信息
            if ($metric[0] == 'interface_list') {
                $interface_info = json_decode($metric[1], true);
                $interface_info['@timestamp'] = strtotime($interface_info['@timestamp']);
                $interface_stat = [
                    'in' => $interface_info['system']['network']['in'],
                    'out' => $interface_info['system']['network']['out'],
                    'time' => $interface_info['@timestamp']
                ];
                $ifname = $interface_info['system']['network']['name'];
                if (isset($interface_info_last[$ifname]) && strpos($ifname, "eth") !== false) {
                    exec("cat /sys/class/net/$ifname/carrier 2>&1",$is_connect);
                    $interface_stat['name'] = $ifname;
                    $interface_stat['up'] = isset($is_connect[0]) ? $is_connect[0] : 0;
                    $interface_stat['in']['Bps'] = $interface_stat['in']['bytes'] - $interface_info_last[$ifname]['system']['network']['in']['bytes'];
                    $interface_stat['out']['Bps'] = $interface_stat['out']['bytes'] - $interface_info_last[$ifname]['system']['network']['out']['bytes'];

                    if ($interface_stat['up'] == "0" || $interface_stat['up'] == "1") {
                        $this->redis->hSet('interface_stat', $interface_stat['name'], json_encode($interface_stat));
                    }else{
                        $this->redis->hdel('interface_stat', $interface_stat['name']);
                    }

                    $is_connect = [];
                }
                $interface_info_last[$interface_info['system']['network']['name']] = $interface_info;
            }
        }
    }

    protected function logsPusher() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $shm_key = myftok(FP_LOG_SHM_KEY, "1");
        $shm_id = shmop_open($shm_key, 'c', 0644, FP_LOG_SHM_SIZE);
        if (!$shm_id) {
            $output->writeln("open logs shared mem failed !");
            return false;
        }
        while (true) {
            sleep(10);
            exec("fpcmd fp_shm_clear -log && fpcmd ddos_log");
            $shm_data = shmop_read($shm_id, 0, FP_LOG_SHM_SIZE);
            $shm_data = $this->str_from_mem($shm_data);
            if (!empty($shm_data)) {
                $shm_data = json_decode($shm_data, true);
            } else {
                continue;
            }
            foreach ($shm_data as $log) {
                $this->redis->rPush('attack_log_list', json_encode($log));
            }
        }
        shmop_close($shm_id);
    }

    protected function logsHandle() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        // $this->redis->delete('attack_log_list');
        while (true) {
            $logs = $this->redis->blPop('attack_log_list', 10);
            if (empty($logs)) {
                continue;
            }
            $attack_info = json_decode($logs[1], true);
            if (!empty($attack_info['start_time'])) {
                $attack_info['start_time'] = intval($attack_info['start_time'])/1000;
            }

            if (!empty($attack_info['end_time'])) {
                $attack_info['end_time'] = intval($attack_info['end_time'])/1000;
            }

            if (empty($attack_info['end_time'])) {

                //attack_log
                $ress = db('attack_info')->where([
                    'target_ip'=>$attack_info['target_ip'],
                    'target_port'=>$attack_info['target_port'],
                    'start_time'=>date('Y-m-d H:i:s', $attack_info['start_time']),
                    'attack_type'=>$attack_info['attack_type']
                ])->select();

                if (!empty($ress)) {
                    continue;
                }

                db('attack_info')->insert([
                    'target_ip'=>$attack_info['target_ip'],
                    'target_port'=>$attack_info['target_port'],
                    'start_time'=>date('Y-m-d H:i:s', $attack_info['start_time']),
                    'attack_type'=>$attack_info['attack_type']
                ]);

                //clean_log

                $resss = db('attack_logs')->where([
                    'attack_ip'=>$attack_info['attack_ip'],
                    'target_ip'=>$attack_info['target_ip'],
                    'target_port'=>$attack_info['target_port'],
                    'time'=>date('Y-m-d H:i:s', $attack_info['start_time']),
                    'attack_type'=>$attack_info['attack_type']
                ])->select();

                if (!empty($resss)) {
                    continue;
                }

                db('attack_logs')->insert([
                    'attack_ip'=>$attack_info['attack_ip'],
                    'target_ip'=>$attack_info['target_ip'],
                    'target_port'=>$attack_info['target_port'],
                    'time'=>date('Y-m-d H:i:s', $attack_info['start_time']),
                    'attack_type'=>$attack_info['attack_type']
                ]);

            } else {

                db('attack_info')->where([
                    'target_ip'=>$attack_info['target_ip'],
                    'start_time'=>date('Y-m-d H:i:s', $attack_info['start_time']),
                    'attack_type'=>$attack_info['attack_type']
                ])->update(['end_time'=>date('Y-m-d H:i:s', $attack_info['end_time'])]);
            }
        }
    }

    protected function fluxPusher() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1'); 
        $shm_key = myftok(FP_STATUS_SHM_KEY, "1");
        $shm_id = shmop_open($shm_key, 'c', 0644, FP_STATUS_SHM_SIZE);
        if (!$shm_id) {
            $output->writeln("open flux shared mem failed !");
            return false;
        }
        while (true) {
            sleep(2);
            exec("fpcmd fp_shm_clear -status && fpcmd sys_status");
            $shm_data = shmop_read($shm_id, 0, FP_STATUS_SHM_SIZE);
            $shm_data = $this->str_from_mem($shm_data);
            if (empty($shm_data)) {
                continue;
            }
            
            $shm_data = json_decode($shm_data, true);
            $flux_data['@timestamp'] = time();
            $flux_data['system'] = $shm_data['system'];
            unset($shm_data['system']);
            $flux_data['ips'] = $shm_data;

            $this->redis->rPush('sysflux_list', json_encode([
                '@timestamp'=>$flux_data['@timestamp'],
                'flux'=>$flux_data['system']
            ]));
            $this->redis->rPush('ipflux_list', json_encode([
                '@timestamp'=>$flux_data['@timestamp'],
                'flux'=>$flux_data['ips']
            ]));
        }
        shmop_close($shm_id);       
    }

    protected function sysfluxHandle() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $this->redis->delete('sysflux_list');
        $Sysflux_handle = new Sysflux();
        while (true) {
            $sysflux = $this->redis->blPop('sysflux_list', 10);
            if (empty($sysflux)) {
                continue;
            }
            $sysflux = json_decode($sysflux[1], true);
            $Sysflux_handle->handle($sysflux);
        }
    }

    protected function ipfluxHandle() {
        $this->redis = new \Redis();
        $this->redis->pconnect('127.0.0.1');
        $this->redis->delete('ipflux_list');
        $Ipflux_handle = new Ipflux();
        while (true) {
            $ipflux = $this->redis->blPop('ipflux_list', 10);
            if (empty($ipflux)) {
                continue;
            }
            $ipflux = json_decode($ipflux[1], true);
            $Ipflux_handle->handle($ipflux);
        }        
    }

    protected function str_from_mem($value) {
        $i = strpos($value, "\0");
        if ($i === false) {
        return $value;
        }
        $result =  substr($value, 0, $i);
        return $result;
    }
}