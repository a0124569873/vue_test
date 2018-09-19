<?php
namespace app\index\event;
use think\Controller;
use think\Request;
use think\Session;
use think\Loader;
use app\index\model\SysConfModel;
use app\index\model\BlackWhiteList;
use app\index\model\DnsWhiteList;
use app\index\model\HostConf as HostConfig;
use app\index\model\TcpSetConf;
use app\index\model\UdpSetConf;
use app\index\model\HostSetConf;

/**
 * 构建配置json 
 */
class Confbuilder extends Controller {
    
    //开机初始化构建全部配置json
    public function forAll(){
        
        $json_content['system_para'] = [];
        $json_content['b_list'] = [];
        $json_content['w_list'] = [];
        $json_content['server'] = [];

        // 获取系统配置参数————system_param
        $sys_conf = new SysConfModel;
        $conf_arr = $sys_conf->selectAllConfValue();
        $system_para = $this->_buildSysParam($conf_arr);

        // 获取黑白名单参数————b_w_list
        $black_white_list = new BlackWhiteList;
        $conf_arr = $black_white_list->selectAllList();
        $b_w_list = $this->_buildBwList("add", $conf_arr);

        // 获取DNS白名单参数————dns_w_list
        $dns_white_list = new DnsWhiteList;
        $conf_arr = $dns_white_list->selectAllList();
        $dns_w_list = $this->_buildDnswList("add", $conf_arr);

        // 获取防护主机参数————server
        $host_conf = new HostConfig;
        $conf_arr = $host_conf->selectAllHosts();
        $server = $this->_buildHostProt("add", $conf_arr);

        $json_content['system_para'] = $system_para;
        $json_content['b_list'] = $b_w_list['b_list'];
        $json_content['w_list'] = $b_w_list['w_list'];
        $json_content['dns_w_list'] = $dns_w_list;
        $json_content['server'] = $server;

        return json_encode($json_content);
    }

    /** 
     *  server for 系统防护参数部分
     *  @param Array  $conf_arr : conf_type、conf_value
     *  @return String json 
     */
    public function forSysParam($conf_arr){
        $json_content = [];
        $json_content["system_para"] = $this->_buildSysParam($conf_arr);
        return json_encode($json_content);
    }

    /**
     *  server for 黑白名单部分
     *  @param  String $oper  黑白名单操作 :add del
     *  @param  Array  $conf_arr  黑白名单配置 :set_num list_type ip
     *  @return String json 
     */
    public function forBwList($oper, $conf_arr){
        $json_content = [];
        $b_w_list = $this->_buildBwList($oper, $conf_arr);

        if(!empty($b_w_list['b_list'])){
            $json_content['b_list'] = $b_w_list['b_list'];
        }
        if(!empty($b_w_list['w_list'])){
            $json_content['w_list'] = $b_w_list['w_list'];
        }

        return json_encode($json_content);
    }

    /**
     *  server for DNS白名单部分
     *  @param  String $oper  白名单操作 :add del
     *  @param  Array  $conf_arr  白名单配置 :set_num ip
     *  @return String json 
     */
    public function forDnswList($oper, $conf_arr){
        $json_content = [];
        $dns_w_list = $this->_buildDnswList($oper, $conf_arr);

        if(!empty($dns_w_list)){
            $json_content['dns_w_list'] = $dns_w_list;
        }

        return json_encode($json_content);
    }

    /**
     *  server for 主机防护参数部分
     *  @param String  $oper : update 
     *  @param Array  $conf_arr : conf_value、ip
     *  @return String json 
     */
    public function forHostParam($oper, $conf_arr){
        $json_content = [];
        $mask = 2;
        foreach($conf_arr as $conf){
            $ip = $conf['ip'];
            $host_para = $this->_buildHostParam($conf['conf_value']);
            $json_content['server'][] = [
                "ip"    =>  $ip,
                "mask"  =>  $mask,
                "oper"  =>  $oper,
                "host_para"  =>  $host_para
            ];
        }
        return json_encode($json_content);
    }

    /**
     *  server for TCP端口防护参数部分
     *  @param Array  $conf_arr : ip、conf_value
     *  @return String json 
     */
    public function forTcpPort($conf_arr){
        $json_content = [];
        $mask = 4;
        $oper = 'update';

        $tmp_arr = [];
        foreach($conf_arr as $item){
            $tmp_arr[$item['ip']][] = $item['conf_value'];
        }

        foreach($tmp_arr as $ip => $conf){
            $arr = [];
            $arr['ip'] = $ip;
            $arr['oper'] = 'update';
            $arr['mask'] = $mask;
            $arr['tcp_port_prot'] = $this->_buildTcpPort($conf);
            $json_content['server'][] = $arr;
        }
        return json_encode($json_content);
    }

    /**
     *  server for UDP端口防护参数部分
     *  @param Array  $conf_arr : ip、conf_value
     *  @return String json 
     */
    public function forUdpPort($conf_arr){
        $json_content = [];
        $mask = 8;
        $oper = 'update';

        $tmp_arr = [];
        foreach($conf_arr as $item){
            $tmp_arr[$item['ip']][] = $item['conf_value'];
        }
  
        foreach($tmp_arr as $ip => $conf){
            $arr = [];
            $arr['ip'] = $ip;
            $arr['oper'] = 'update';
            $arr['mask'] = $mask;
            $arr['udp_port_prot'] = $this->_buildUdpPort($conf);
            $json_content['server'][] = $arr;
        }
        return json_encode($json_content);
    }

    /**
     *  server for 防护范围（server）部分
     *  @param String  $oper : add, update, del 
     *  @param Array  $conf_arr : ip、conf_value、host_set_num、tcp_set_num、udp_set_num、b_list_num、w_list_num
     *  @return String json 
     */
    public function forHostProt($oper, $conf_arr){
        $json_content = [];
        $json_content["server"] = $this->_buildHostProt($oper, $conf_arr);
        return json_encode($json_content); 
    }

    /**
     *  解析构建系统防护参数部分
     *  @param Array  $conf_arr : conf_type、conf_value
     *  @return Array 
     */
    private function _buildSysParam($conf_arr){
        $sys_param = [];
        foreach($conf_arr as $conf){
            if($conf['conf_type'] == 1){
                $sys_param['def_mod'] = $conf['conf_value'];
            }
            if($conf['conf_type'] == 2){
                $sys_param['blk_time'] = (int)$conf['conf_value'];
            }
        }
        return $sys_param;
    }

    /**
     *  解析构建黑白名部分
     *  @param  String $oper  黑白名单操作 :add del
     *  @param  Array  $conf_arr:set_num,type,ip
     *  @return Array
     */
    private function _buildBwList($oper, $conf_arr){

        $b_w_list['b_list'] = [];
        $b_w_list['w_list'] = [];
        if(empty($conf_arr)){
            return $b_w_list;
        }

        $b_list = [];
        $w_list = [];
        foreach($conf_arr as $tmp){
            if($tmp['type'] == '1'){
                $b_list[$tmp['set_num']][] = $tmp['ip_range'];
            }else{
                $w_list[$tmp['set_num']][] = $tmp['ip_range'];
            }
        }
        
        if(!empty($b_list)){
            foreach($b_list as $k => $tmp){
                $b_w_list['b_list'][] = [
                    "grp"   =>  $k,
                    "oper"  =>  $oper,
                    "ip"    =>  $tmp
                ];
            }
        }
        if(!empty($w_list)){
            foreach($w_list as $k => $tmp){
                $b_w_list['w_list'][] = [
                    "grp"   =>  $k,
                    "oper"  =>  $oper,
                    "ip"    =>  $tmp
                ];
            }
        }
        
        return $b_w_list;
    }

    /**
     *  解析构建DNS白名部分
     *  @param  String $oper  黑白名单操作 :add del
     *  @param  Array  $conf_arr:set_num,ip
     *  @return Array
     */
    private function _buildDnswList($oper, $conf_arr){

        $dns_w_list = [];
        if(empty($conf_arr)){
            return $dns_w_list;
        }
        foreach($conf_arr as $tmp){
            $w_list[$tmp['set_num']][] = $tmp['ip_range'];
        }

        foreach($w_list as $k => $tmp){
            $dns_w_list[] = [
                "grp"   =>  $k,
                "oper"  =>  $oper,
                "ip"    =>  $tmp
            ];
        }
        
        return $dns_w_list;
    }

    /**
     *  解析构建主机防护参数部分
     *  @param String  $conf : 1000|100|200
     *  @return Array
     */
    private function _buildHostParam($conf){
        $host_para = [];

        $conf_arr = explode('|', $conf);
        $host_para['syn'] = (int)$conf_arr[0];
        $host_para['udp'] = (int)$conf_arr[1];
        $host_para['tcp_idle'] = (int)$conf_arr[2];
        //------no use------
        $host_para['syn_ss'] = 0;
        $host_para['ack_rst'] = 0;
        $host_para['icmp'] = 0;
        $host_para['tcp_con_in'] = 0;
        $host_para['tcp_con_out'] = 0;
        $host_para['tcp_con_ip'] = 0;
        $host_para['tcp_fre'] = 0;
        $host_para['udp_con'] = 0;
        $host_para['udp_fre'] = 0;
        $host_para['icmp_fre'] = 0;
        //------no use------

        return $host_para;
    }
    
    /**
     *  解析构建TCP端口防护参数部分
     *  @param Array  $conf : 0|335|1|100|3 或 为空null
     *  @return Array 
     */
    private function _buildTcpPort($conf){
        $tcp_port = [];
        if(!isset($conf[0])){
            return [];
        }        
        foreach($conf as $p){
            $p_conf = [];
            $p_arr = explode("|", $p);
            
            $p_conf = [
                "str"       =>(int)$p_arr[0],
                "end"       =>(int)$p_arr[1],
                "on_off"    =>(int)$p_arr[2],
                "con_lmt"   =>(int)$p_arr[3],
                "pro_mod"   =>$p_arr[4] === "" || $p_arr[4] === null ? 0 : ParseModType($p_arr[4]),
                //------no use------
                "pro_plug"  =>0,
                "atk_fre"   =>0,
                //------no use------
            ];
            $tcp_port[] = $p_conf;
        }
        return $tcp_port;
    }
    
    /**
     *  解析构建UDP端口防护参数部分
     *  @param Array  $conf : 0|335|1 或 为空null
     *  @return Array 
     */
    private function _buildUdpPort($conf){
        $udp_port = [];
        if(!isset($conf[0])){
            return [];
        }
        foreach($conf as $p){
            $p_conf = [];
            $p_arr = explode("|", $p);
            $p_conf = [
                "str"       =>(int)$p_arr[0],
                "end"       =>(int)$p_arr[1],
                "on_off"    =>(int)$p_arr[2],
                //------no use------
                "atk_fre"   =>0,
                "pkt_fre"   =>0,
                "pro_mod"   =>0,
                "pro_plug"  =>0,
                //------no use------
            ];
            $udp_port[] = $p_conf;
        }
        return $udp_port;
    }

    /**
     *  解析构建防护范围（server）部分
     *  @param String  $oper : add, update, del 
     *  @param Array  $conf_arr : ip、conf_value、host_set_num、tcp_set_num、udp_set_num、dns_set_num
     *  @return String json 
     */
    private function _buildHostProt($oper, $conf_arr){
        $servers_arr = [];
        $mask = 31;
        $host_para_collection = [];
        $tcp_port_prot_collection = [];
        $udp_port_prot_collection = [];

        foreach($conf_arr as $conf){
            $server = [];
            $server['ip']= $conf['ip'];
            $server['oper']= $oper;
            $server['mask']= $mask;

            if($oper == 'del'){
                $servers_arr[] = $server;
                continue;
            }

            $host_arr = explode("|", $conf['conf_value']);
            $host = [
                'flow_pol'=>(int)$host_arr[0],
                //------no use------
                'flow_in'=>0,
                'pkt_in'=>0,
                'flow_out'=>0,
                'pkt_out'=>0
                //------no use------
            ];

            if(isset($host_para_collection[$conf['host_set_num']])){
                $host_para = $host_para_collection[$conf['host_set_num']];
            }else{
                $host_para = $this->_getHostPara($conf['host_set_num']);
                $host_para_collection[$conf['host_set_num']] = $host_para;
            }

            if(isset($tcp_port_prot_collection[$conf['tcp_set_num']])){
                $tcp_port_prot = $tcp_port_prot_collection[$conf['tcp_set_num']];
            }else{
                $tcp_port_prot = $this->_getTcpPort($conf['tcp_set_num']);
                $tcp_port_prot_collection[$conf['tcp_set_num']] = $tcp_port_prot;
            }

            if(isset($udp_port_prot_collection[$conf['udp_set_num']])){
                $udp_port_prot = $udp_port_prot_collection[$conf['udp_set_num']];
            }else{
                $udp_port_prot = $this->_getUdpPort($conf['udp_set_num']);
                $udp_port_prot_collection[$conf['udp_set_num']] = $udp_port_prot;
            }
   
            $server['host']= $host;
            $server['host_para']= $host_para;
            $server['tcp_port_prot']= $tcp_port_prot;
            $server['udp_port_prot']= $udp_port_prot;
            $server['b_w_list']= [
                "b_grp" => $conf['b_list_num'] === "" || is_null($conf['b_list_num']) ? -1 : (int)$conf['b_list_num'],
                "w_grp" => $conf['w_list_num'] === "" || is_null($conf['w_list_num']) ? -1 : (int)$conf['w_list_num'],
                "dns_w_grp" => $conf['dns_w_list_num'] === "" || is_null($conf['dns_w_list_num']) ? -1 : (int)$conf['dns_w_list_num'],
            ];
            $servers_arr[] = $server;
        }
        return $servers_arr;
    }

    //根据集编号获取集内配置信息（主机防御配置）
    private function _getHostPara($num){
        $host_set_conf = new HostSetConf;
        $data = $host_set_conf->selectProParamByGether($num);
        $host_para = $this->_buildHostParam($data[0]['conf_value']);
        return $host_para;
    }

    //根据集编号获取集内配置信息（TCP防御配置）
    private function _getTcpPort($num){
        $tcp_set_conf = new TcpSetConf;
        $data = $tcp_set_conf->selectTcpByGether($num);
        
        $conf_arr = array_map(function($item){
            return $item['conf_value'];
        },$data);

        $tcp_conf = $this->_buildTcpPort($conf_arr);
        return $tcp_conf;
    }

    //根据集编号获取集内配置信息（UDP防御配置）
    private function _getUdpPort($num){
        $udp_set_conf = new UdpSetConf;
        $data = $udp_set_conf->selectUdpByGether($num);

        $conf_arr = array_map(function($item){
            return $item['conf_value'];
        },$data);
        
        $udp_conf = $this->_buildUdpPort($conf_arr);
        return $udp_conf;
    }

}