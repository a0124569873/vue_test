<?php
namespace app\index\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use send_mail\SendMail;
use think\DB;

class Metrics extends Command
{
    protected function configure()
    {
        $this->setName('metrics')->setDescription('collect metrics ... ');
    }

    protected function execute(Input $input, Output $output)
    {
        $pid = pcntl_fork();
        if (!$pid) {
            cli_set_process_title('Loghandle: metrics');
            $output->writeln('Metrics process started on pid ' . posix_getpid());
            $this->worker("metrics");
            exit;
        }

        // 更新链路信息
        $pid = pcntl_fork();
        if (!$pid) {
            cli_set_process_title('Loghandle: link_stat');
            $output->writeln('update link_stat process started on pid ' . posix_getpid());
            $this->worker("link_stat");
            exit;
        }

        $pid = pcntl_fork();
        if (!$pid) {
            cli_set_process_title('Loghandle: G-topo');
            $output->writeln('G-topo process started on pid ' . posix_getpid());
            $this->worker("G-topo");
            exit;
        }

        sleep(1);
        
        $output->writeln('All Collecting processes started ... ');
    }

    /**
     * 处理redis队列各项metric 
     * @access protected
     * @param string $type metric类型
     * @return void
     */
    protected function worker($type)
    {
        switch ($type) {
            // CPU 内存 硬盘数据处理
            case 'metrics':
                $redis = new \Redis();
                $redis->pconnect('127.0.0.1');
                $redis->delete(array('cpu_list','memory_list','disk_list'));
                while (true) {
                    $redis->pconnect('127.0.0.1');
                    $metric = $redis->blPop(array('cpu_list','memory_list','disk_list'), 10);
                    if (!empty($metric)) {
                        // CPU数据处理
                        if ($metric[0] == 'cpu_list') {
                            $cpu_info = json_decode($metric[1], true);
                            $cpu_info['@timestamp'] = strtotime($cpu_info['@timestamp']);
                            $cpu_stat = [
                                'user' => $cpu_info['system']['cpu']['user']['pct'] * 100,
                                'system' => $cpu_info['system']['cpu']['system']['pct'] * 100,
                                'idle' => $cpu_info['system']['cpu']['idle']['pct'] * 100,
                                'time' => date('Y-m-d H:i:s', $cpu_info['@timestamp'])
                            ];
                            if ($cpu_info['@timestamp'] % 5 == 0) {
                                db('cpu_10')->insert($cpu_stat);
                            }
                            $redis->hSet('dev_stat', 'cpu', json_encode($cpu_stat));
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
                            if ($memory_info['@timestamp'] % 5 == 0) {
                                db('memory_10')->insert($memory_stat);
                            }
                            $redis->hSet('dev_stat', 'memory', json_encode($memory_stat));
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
                            if ($disk_info['@timestamp'] % 5 == 0) {
                                db('disk_10')->insert($disk_stat);
                            }
                            $redis->hSet('dev_stat', 'disk', json_encode($disk_stat));
                        }

                    }
                }
            break;

            case 'link_stat':
                $redis = new \Redis();
                $redis->pconnect('127.0.0.1');
                $socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
                if (file_exists("/tmp/g_link_status")){
                    unlink("/tmp/g_link_status");
                }
                socket_bind($socket, '/tmp/g_link_status');
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>10,'usec'=>0]);
                while (true) {
                    $flux_json = "";
                    $recv_size = 1;
                    exec('uroot fpcmd g_link_status -s');
                    while ($recv_size) {
                        $recv_size = socket_recvfrom($socket, $buf, 1420, 0, $from);
                        $recv_size = empty($recv_size) ? 0 : $recv_size;
                        if ($recv_size == 1403) {
                            $buf = substr($buf, 0 , -3);
                        }
                        $flux_json .= $buf;
                        $buf = "";
                    }
                    $redis->pconnect('127.0.0.1');
                    if (!empty($flux_json)) {
                        $flux_arr = json_decode($flux_json, true);

                        // $time_seconds = ceil($flux_arr['timestamp'] / 1000); 
                        // $time_str = date('Y-m-d H:i:s', time());
                        // $redis->hSet('link_stat', "update_time", $time_str);

                        // 写入链路数
                        if (count($flux_arr)) {
                            $redis->hSet('link_stat', 'link_count', count($flux_arr));
                        }
                        
                        //删除之前数据
                        $redis->zRemRangeByRank('uid_top', 0, -1);

                        //根据uid排序添加数据
                        foreach ($flux_arr as $key => $value) {
                            // $redis->hSet('link_stat', $key, $value);
                            $tmp_val = explode(" ", $value);
                            $redis->zAdd('uid_top', $tmp_val[0], json_encode([$key=>$tmp_val[1]]));
                            $redis->hSet('link_stat', $key, $tmp_val[1]);
                        }

                    }
                }
                socket_close($socket);
                unlink('/tmp/g_link_status');
                break;

            case 'G-topo':
                $redis = new \Redis();
                $redis->pconnect('127.0.0.1');
                $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
                socket_bind($socket, '0.0.0.0', 7000);
                socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec'=>10, 'usec'=>0]);;
                while (true) {
                    $recv_size = socket_recvfrom($socket, $buf, 10240, 0, $from, $port);
                    $gstat = ['timestamp'=>intval(microtime(true)*1000)];
                    if ($recv_size > 0) {
                        foreach (explode("\n", $buf) as $i) {
                            if (empty($i)) {
                                continue;
                            }
                            $s = explode(" ", $i);
                            if (count($s) != 3) {
                                continue;
                            }
                            $gstat[$s[0]] = ['state'=>$s[1], 'timestamp'=>$s[2]];
                        }
                        $redis->hSet('G-status', $from, json_encode($gstat));
                    }
                }
                break;
        }
    }

}