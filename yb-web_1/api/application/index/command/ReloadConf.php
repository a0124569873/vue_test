<?php
namespace app\index\command;
 
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\DB;

 
class ReloadConf extends Command
{
    protected function configure()
    {
        $this->setName('reload_conf')->setDescription('reload all conf ');

    }
 
    protected function execute(Input $input, Output $output)
    {

        //虚假真实vpn配置
        exec("uroot fpcmd vpn_info -f");
        $vpn_info_list = db("vpn_info") ->select();
        if (!empty($vpn_info_list)) {
            foreach ($vpn_info_list as $key => $value) {
                exec("uroot fpcmd vpn_info -v ".$value["v_vpn"]." -r ".$value["r_vpn"]);
            }
        }

        //用户接入配置
        exec("uroot fpcmd user_info -f");
        $user_info_list = db('user_info')->field("p_ip,hide_ip,uid,username,interval")->where('hide_ip', '<>', '')->where("uid",'not null')->select();
        if (!empty($user_info_list)) {
            $a = 0;
            while (true) {

                $conf_arr = [];

                foreach($user_info_list as $key => $conf){

                    if ($conf["uid"] == "RANDOM") {
                        $conf["uid"] = "0";
                    }

                    // $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",".$conf['hide_ip'];

                    if (empty($conf["interval"])) {
                        $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",0,0,".$conf['hide_ip'];
                    }else{
                        $conf_arr[$conf['p_ip']] = $conf['username'].",".$conf['uid'].",-2147483648,".$conf['interval'].",".$conf['hide_ip'];
                    }
                    
                    $a = $a +1;
                    unset($user_info_list[$key]);
                    if ($a == 5 || count($user_info_list) == 0)
                        break;
                }

                $conf_param = json_encode($conf_arr);
                $conf_param_p = str_replace('"', '\\"', $conf_param);
                $order = "uroot 'fpcmd user_info -i \"".$conf_param_p."\"'";
                ExcuteExec($order);
                if (count($user_info_list) == 0)
                    break;
            }
        }
        
        //主动回连配置
        exec("uroot iptables -F");
        exec("uroot fpcmd cs -f");
        ExcuteExec("iptables -A FORWARD -m uid --uid 1");
        $revert_link_list = db("revert_link") ->select();
        if (!empty($revert_link_list)) {
            foreach ($revert_link_list as $key => $conf) {

                if ($conf["pro_type"] == "6" || $conf["pro_type"] == "17"){

                    $conf_param = "uroot iptables -A FORWARD";
                    if(!empty($conf["src_ip"])){
                        $conf_param.=(" -s ".$conf["src_ip"]);
                    }
                    if(!empty($conf["dst_ip"])){
                        $conf_param.=(" -d ".$conf["dst_ip"]);
                    }

                    $conf_param.=(" -p ".$conf["pro_type"]);

                    if(!empty($conf["src_port"])){
                        $conf_param.=(" --sport ".$conf["src_port"]);
                    }
                    if(!empty($conf["dst_port"])){
                        $conf_param.=(" --dport ".$conf["dst_port"]);
                    }

                    $conf_param.=(" -m uid --uid ".$conf["uid"]);

                    ExcuteExec($conf_param);
                    if (!empty($conf["r_port"])) {
                        ExcuteExec("uroot fpcmd cs -u ". $conf["uid"] ." -r ". $conf["p_ip"] . " -d ". $conf["r_port"]);
                    }

                    continue;
                }
                $conf_param = "uroot iptables -A FORWARD -d ". $conf["dst_ip"];
                
                if (!empty($conf["src_ip"])){
                    $conf_param.=(" -s ".$conf["src_ip"]);
                }
                $conf_param.= " -p ".$conf["pro_type"]." -m uid --uid ". $conf["uid"];
                ExcuteExec($conf_param);
                if (!empty($conf["r_port"])) {
                    ExcuteExec("uroot fpcmd cs -u ". $conf["uid"] ." -r ". $conf["p_ip"] . " -d ". $conf["r_port"]);
                }
            }
        }
        
        

        //网口配置
        $hide_ip_pool_list = db("sys_conf") -> where("conf_type","net") ->select();
        if (!empty($hide_ip_pool_list)) {
            $conf_arr_tmp = explode("|", $hide_ip_pool_list[0]["conf_value"]);
            exec("uroot fpcmd yb_conf_ip ".$conf_arr_tmp[0]." ".$conf_arr_tmp[1]." ".$conf_arr_tmp[2]." ".$conf_arr_tmp[3]);
            exec("uroot fpcmd log_ip ".$conf_arr_tmp[4]);
        }

        // exec("ifconfig eth0_0 192.168.2.18/24");
        // exec("route add default gw 192.168.2.1");

        #wait ip set
        sleep(10);

        #load all Gserver
        rewriteGConf();

        #load all vpn_server

        // $res_vpn_server = db('sys_conf')-> where("conf_type","2")->select();
        // foreach ($res_vpn_server as $key => $value) {
        //     update_vpn_server_conf(explode("|", $value["conf_value"])[0]);
        // }

    }

}