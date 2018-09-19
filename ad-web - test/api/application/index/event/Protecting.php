<?php
namespace app\index\event;
use app\index\model\HostConf;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;

/**
 * 防护范围 分层控制器
 */
class Protecting extends Controller {

    //【接口】获取查询
    public function get(){
        $page = empty(input('get.page')) ? 1 : input('get.page');
        $row = empty(input('get.row')) ? 10 : input('get.row');
        $ip = empty(input('get.ip')) ? '' : input('get.ip');
        $ip_range = empty(input('get.ip_range')) ? '' : input('get.ip_range');

        if (strstr($ip_range, "-") || strstr($ip_range, "/")) {
            $ip = "";
        }else{
            $ip_range = "";
        }

        $counts = NULL;$result = [];$datas = [];
        $host_conf = new HostConf;
        if($ip_range !== ''){
            $datas = $host_conf->selectHostsByIpRange($ip_range,$page,$row);
            $counts = $host_conf->countIpRange($ip_range);
            $tmp_datas = array_map(function($item){
                return $item['id']."|".$item['ip']."|".$item['host_set_num']."|".$item['tcp_set_num']."|".$item['udp_set_num'].
                "|".$item['b_list_num']."|".$item['w_list_num']."|".$item['dns_w_list_num']."|".$item['conf_value']."|".$item['last_update'];
            },$datas);
        }elseif($ip !== ''){
            $counts = $host_conf->countHostsByIp($ip);
            $datas = $counts == 0 ? [] : $host_conf->selectHostsByIp($ip,$page,$row);
            $tmp_datas = array_map(function($item){
                return $item['id']."|".$item['ip']."|".$item['host_set_num']."|".$item['tcp_set_num']."|".$item['udp_set_num'].
                "|".$item['b_list_num']."|".$item['w_list_num']."|".$item['dns_w_list_num']."|".$item['conf_value']."|".$item['last_update'];
            },$datas);
        }else{
            $counts = $host_conf->countAllIpRange();
            $datas = $host_conf->selectAllIpRange($page,$row);
            $tmp_datas = array_map(function($item){
                return $item['id']."|".$item['ip_range']."|".$item['host_set_num']."|".$item['tcp_set_num']."|".$item['udp_set_num'].
                "|".$item['b_list_num']."|".$item['w_list_num']."|".$item['dns_w_list_num']."|".$item['conf_value']."|".$item['last_update'];
            },$datas);
        }

        $result['data'] = $tmp_datas;
        if(!is_null($counts))
            $result['count'] = $counts;

        return $result;
    }

    //【接口】添加
    public function add(){
        $conf_arr = TrimParams(input('post.7'));
        $ip_range = $conf_arr[0];
        $host_num = $conf_arr[1];
        $tcp_num = $conf_arr[2];
        $udp_num = $conf_arr[3];
        $b_list_num = $conf_arr[4];
        $w_list_num = $conf_arr[5];
        $dns_w_list_num = $conf_arr[6];
        $conf_value = $conf_arr[7];
        
        $host_conf = new HostConf;
        $datas = $host_conf->selectAllHostIp();
        $existed_ip = array_map(function($item){
            return $item['ip'];
        },$datas);
        
        $existed_ip = array_flip($existed_ip);
        $save_list = [];

        if(CheckIpMask($ip_range)){
            foreach(ParseIpMask($ip_range) as $ip){
                if(isset($existed_ip[$ip])){
                    Error('10025');
                }
                $save_list[] = [
                    'ip_range'=>$ip_range,'ip'=>$ip,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                    'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
                ];
            }
        }elseif(CheckIpRange($ip_range)){
            foreach(ParseIpRange($ip_range) as $ip){
                if(isset($existed_ip[$ip])){
                    Error('10025');
                }
                $save_list[] = [
                    'ip_range'=>$ip_range,'ip'=>$ip,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                    'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
                ];
            }
        }else{
            if(isset($existed_ip[$ip_range])){
                Error('10025');
            }
            $save_list[] = [
                'ip_range'=>$ip_range,'ip'=>$ip_range,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
            ];
        }

        if(count($save_list) > 2550)
            Error("15014");

        if(count($save_list)+count($existed_ip) > 10200)
            Error("15013");

        $result = $host_conf->insertAllHostConf($save_list);
        if(!$result)
            Error('20001');

        //构建配置json并写入
        $this->buildAndWrite("add", $save_list);
    }

    //【接口】更新
    public function update(){
        $conf_arr = TrimParams(input('post.7'));
        $id = $conf_arr[0];
        $host_conf = new HostConf;

        $data = $host_conf->selectHostById($id); // 是否存在要更新的记录
        if(empty($data))
            Error("10027");

        if(input('get.multi') == 'true'){ // multi ip
            $ip_range = $data[0]["ip_range"];
            $save_list = [];
            $host_num  =  $conf_arr[1];
            $tcp_num  =  $conf_arr[2];
            $udp_num  =  $conf_arr[3];
            $b_list_num = $conf_arr[4];
            $w_list_num = $conf_arr[5];
            $dns_w_list_num = $conf_arr[6];
            $conf_value = $conf_arr[7];

            if(CheckIpMask($ip_range)){
                foreach(ParseIpMask($ip_range) as $ip){
                    $save_list[] = [
                        'ip_range'=>$ip_range,'ip'=>$ip,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                        'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
                    ];
                }
            }elseif(CheckIpRange($ip_range)){
                foreach(ParseIpRange($ip_range) as $ip){
                    $save_list[] = [
                        'ip_range'=>$ip_range,'ip'=>$ip,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                        'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
                    ];
                }
            }else{
                $save_list[] = [
                    'ip_range'=>$ip_range,'ip'=>$ip_range,'host_set_num'=>$host_num,'tcp_set_num'=>$tcp_num,'udp_set_num'=>$udp_num,
                    'b_list_num'=>$b_list_num,'w_list_num'=>$w_list_num,'dns_w_list_num'=>$dns_w_list_num,'conf_value'=>$conf_value
                ];
            }

            $result = $host_conf->delectByIpRange($ip_range); // 删除原网段配置
            if($result < 0)
                Error("20001");

            $result = $host_conf->insertAllHostConf($save_list);
            if(!$result)
                Error('20001');
            
            $data = $save_list;
        }else{ //single ip
            $conf_arr = [
                'host_set_num'  =>  $conf_arr[1],
                'tcp_set_num'   =>  $conf_arr[2],
                'udp_set_num'   =>  $conf_arr[3],
                'b_list_num'    =>  $conf_arr[4]==''?null:$conf_arr[4],
                'w_list_num'    =>  $conf_arr[5]==''?null:$conf_arr[5],
                'dns_w_list_num'=>  $conf_arr[6]==''?null:$conf_arr[6],
                'conf_value'    =>  $conf_arr[7]
            ];
            $result = $host_conf->updateHostById($id, $conf_arr);
            if($result < 0)
                Error('20001');

            $data = $host_conf->selectHostById($id);
        }
        
        //构建配置json并写入
        $this->buildAndWrite("update", $data);
    }

    //【方法】删除
    public function del(){
        $ips_arr = TrimParams(input('post.7'));

        $host_conf = new HostConf;
        $data = $host_conf->selectAllHostsByIpRange($ips_arr);//获取此ip范围内的ip

        $result = $host_conf->delectByIpRange($ips_arr);
        if($result < 0){
            Error("20001");
        }

        //构建配置json并写入
        $this->buildAndWrite('del', $data);
    }

    //【方法】将配置写入共享内存并执行命令调取
    private function buildAndWrite($oper, $conf){
        if(empty($conf)){
            return;
        }
        //构建配置json
        $C_builder = controller('Confbuilder', 'event');
        $json_conf = $C_builder->forHostProt($oper, $conf);
        //写入共享内存
        $result = WriteInShm($json_conf);
        if(!$result){
            Error("20006", "write in shm error");
        }
        ExcuteExec("uroot fpcmd server_config");
    }

}