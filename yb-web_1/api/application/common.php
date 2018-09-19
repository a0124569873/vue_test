<?php 

include_once __DIR__ . "/command.php";

use think\DB;

function IsLogin(){
    $user = session('user_auth');
    if(empty($user)){
        return 0;
    }else{
        return session('user_auth_sign') == DataAuthSign($user) ? $user['id'] : 0;
    }
}

function DataAuthSign($data) {
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data);//对数组进行降序排列
    $code = http_build_query($data);//将数组处理为url-encoded 请求字符串
    $sign = sha1($code);//进行散列算法
    return $sign;
}

function NowTime(){
    return date("Y-m-d H:i:s");
}

function RawJsonToArr(){
    $data_arr = [];
    $row_data = file_get_contents("php://input");
    if( is_string($row_data) && $row_data==="")
        Error("22001");
    
    $data_arr = json_decode($row_data,true);
    if(!is_array($data_arr))
        Error("11019");

    return $data_arr;
}

function GetExecRes($order){
	$output=array();
	$status=0;
    exec($order,$output,$status);
	if($status){
        Error("20005","error: $order");
	}else if (!empty($output)) {
		$result = toUTF($output);
	} else {
        Error("20005","error: $order");
	}
	return array("status" => $status,"result" => $result);
}

function toUTF($result){
	return PATH_SEPARATOR==":"?$result:iconv("GB2312","UTF-8",$result);
}

function ExcuteExec($order){
    $output=[];
    $status=[];
    exec($order,$output,$status);
    if($status!=0){
        Error('20005','error: '.$order);
    }
}

/**
 * 接口返回成功方法
 * @param  string|array    $data  返回数据
 * @param  string    $code  状态码默认0为请求成功
 * @return string    json
 */
function Finalsuccess($data=NULL, $code="0"){
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok'];
    if(is_null($data)){
        return json($jsonData);
    }else if(is_array($data) && empty($data)){
        $jsonData = ['errcode' => 0, 'errmsg' => 'ok', 'data' => []];
    }else if(is_array($data)){
        foreach ($data as $name => $value) {
            $jsonData[$name] = $value;
        }
    }else{
        $jsonData = ['errcode' => 0, 'errmsg' => 'ok', 'data' => $data];
    }
    return json($jsonData);    
}

/**
 * 接口返回失败方法
 * @param  string    $code  状态码
 * @param  string    $errorMessage  错误信息
 * @param  array     $additionResults  追加data数据 
 * @return string    json
 */
function Finalfail($code, $errorMessage='', array $additionResults=[]){
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    $jsonData = ['errcode' => intval($code), 'errmsg'=>$errorMessage];
    if(!empty($additionResults)){
        foreach ($additionResults as $name => $value) {
            $jsonData[$name] = $value;
        }
    }
    return json($jsonData);
}

function Error($code,$errorMsg=''){
    die(json_encode(['errcode' => intval($code), 'errmsg' => $errorMsg]));
}

function o_exec($order){	
	$output=array();
	$status=0;
	exec($order,$output,$status);
	if($status){
		$result = "$order: error";
	}else if (!empty($output)) {
		$result = toUTF($output[0]);
	} else {
        $status = 1;
		$result = "exec error";
	}
	return array("status" => $status,"result" => $result);
}

// 验证IP范围
function CheckIpRange($ip){
    $ip_arr = explode('-', $ip);
    return count($ip_arr)==2 && ip2long($ip_arr[0]) && ip2long($ip_arr[1]) ;
}
// 验证IP/MASK
function CheckIpMask($ip){
    $ip_mask = explode('/', $ip);
    return count($ip_mask)==2 && ip2long($ip_mask[0]) && is_numeric($ip_mask[1]) && is_int((int)$ip_mask[1]) && $ip_mask[1] >= 1 && $ip_mask[1] <= 32;
}
/**
 * 解析ip范围 192.168.2.5-192.168.3.5
 * @param  string    $ip_range  192.168.2.5-192.168.3.5
 * @return array     ip
 */
function ParseIpRange(String $ip_range){
    $ip_arr = explode('-',$ip_range);
    
    if(ip2long($ip_arr[1]) > ip2long($ip_arr[0])){
        $ip_start = $ip_arr[0];
        $ip_end = $ip_arr[1];
    }else{
        $ip_start = $ip_arr[1];
        $ip_end = $ip_arr[0];
    }
    
    $pro_ip_arr = [];
    $tmp_ip_long = ip2long($ip_start);
    
    while($tmp_ip_long <= ip2long($ip_end)){
        if( !(($tmp_ip_long & 255) == 0 || ($tmp_ip_long & 255) == 255) ){
            $pro_ip_arr[] = long2ip($tmp_ip_long);
        }
        $tmp_ip_long ++ ;
    }

    return $pro_ip_arr;
}

/**
 * 解析ip/mask 192.168.2.5/24
 * @param  string    $ip_mask  ip/mask 192.168.2.5/24
 * @return array     ip
 */
function ParseIpMask(String $ip_mask){
    $ip_mask = explode('/',$ip_mask);
    $ip = $ip_mask[0];
    $mask = $ip_mask[1];
    $pro_ip_arr = [];

    if(!is_numeric($mask)){
        return $pro_ip_arr;
    }
    
    $a = pow(2,(32-$mask)) - 1;
    $start_ip_long = ~(ip2long($ip) & $a) & ip2long($ip);
    $end_ip_long = $start_ip_long + $a;
    
    $tmp_ip_long = $start_ip_long;
    while($tmp_ip_long <= $end_ip_long){
        if( !(($tmp_ip_long & 255) == 0 || ($tmp_ip_long & 255) == 255) ){
            $pro_ip_arr[] = long2ip($tmp_ip_long);
        }
        $tmp_ip_long ++ ;
    }
    
    return $pro_ip_arr;
}

function PasswordHash($password) {
    $password = md5(hash("sha1", $password));
    return $password;
}

function ip_parse($ip_str) {
    $mark_len = 32;
    if (strpos($ip_str, "/") > 0) {
       list($ip_str, $mark_len) = explode("/", $ip_str);
    }
    $ip = ip2long($ip_str);
    $mark = 0xFFFFFFFF << (32 - $mark_len) & 0xFFFFFFFF;
    $ip_start = $ip & $mark;
    $ip_end = $ip | (~$mark) & 0xFFFFFFFF;
    return array($ip, $mark, $ip_start, $ip_end);
}

function rewriteGConf(){

    #getalllist
    $relink_list = db('revert_link')->select();
    $vpn_info_list = db('vpn_info')->select();
    $user_info_list = db('user_info')->select();

    $res_str = "";

    if (!empty($user_info_list)) {
        foreach ($user_info_list as $key => $value) {

            if (empty($value["hide_ip"])){
                continue;
            }

            $each_hide_ip = explode("|", $value["hide_ip"]);

            foreach ($each_hide_ip as $key => $each_hide_ip_value) {
                if(strstr($each_hide_ip_value,"-")){
                    $ips = ParseIpRange($each_hide_ip_value);
                    foreach ($ips as $keyip => $valueip) {
                        $tmp_str="0,0,".$valueip.",255.255.255.255,0,0,0,0,0,1,32\n";
                    }
                }elseif(strstr($each_hide_ip_value,"/")){
                    $ips = ip_parse($each_hide_ip_value);
                    $tmp_str="0,0,".long2ip($ips[0]).",".long2ip($ips[1]).",0,0,0,0,0,1,32\n";

                }else{
                    $tmp_str="0,0,".$each_hide_ip_value.",255.255.255.255,0,0,0,0,0,1,32\n";
                }

                if (strstr($res_str, $tmp_str)) {
                    continue;
                }
                if (count(explode(",", $tmp_str)) != 11) {
                    continue;
                }

                $res_str.=$tmp_str;

            }

        }
    }

    if (!empty($vpn_info_list)) {
        foreach ($vpn_info_list as $key => $value) {
            $res_str.="0,0,".$value["v_vpn"].",255.255.255.255,0,0,0,0,0,1,32\n";
        }
    }

    if (!empty($relink_list)) {
        foreach ($relink_list as $key => $value) {
            $conf_param = "";

            #para src_ip
            if (empty($value["src_ip"])) {
                $conf_param.="0,0,";
            }elseif(strstr($value["src_ip"], "/")){
                $ips = ip_parse($value["src_ip"]);
                $conf_param.=long2ip($ips[0]).",".long2ip($ips[1]).",";
            }else{
                $conf_param.=$value["src_ip"].",255.255.255.255,";
            }

            #para dst_ip
            if (empty($value["dst_ip"])) {
                $conf_param.="0,0,";
            }elseif(strstr($value["dst_ip"], "/")){
                $ips = ip_parse($value["dst_ip"]);
                $conf_param.=long2ip($ips[0]).",".long2ip($ips[1]).",";
            }else{
                $conf_param.=$value["dst_ip"].",255.255.255.255,";
            }

            // para src_port 
            if (empty($value["src_port"])) {
                $conf_param.="0,0,";
            }else{
                $port_mask = get_mask($value["src_port"]);
                $conf_param.=$port_mask[0].",".$port_mask[1].",";
            }

            // para dst_port
            if (empty($value["dst_port"])) {
                $conf_param.="0,0,";
            }elseif(strstr($value["dst_port"], ":")){
                $port_mask = get_mask(explode(":",$value["dst_port"])[0]);
                $conf_param.=$port_mask[0].",".$port_mask[1].",";
            }else{
                $port_mask = get_mask($value["dst_port"]);
                $conf_param.=$port_mask[0].",".$port_mask[1].",";
            }

            // para protype
            if (empty($value["pro_type"]) || $value["pro_type"] == "all") {
                $conf_param.="0,";
            }else{
                $conf_param.=$value["pro_type"].",";
            }

            $conf_param.="1,32\n";
            if (strstr($res_str, $conf_param)){
                continue;
            }
            if (count(explode(",", $conf_param)) != 11) {
                continue;
            }
            
            $res_str.= $conf_param;
        }
    }

    #write_str_to_disk
    file_put_contents("/tmp/g_conf_str_file", $res_str);

    $G_server = db("sys_conf")->where("conf_type","1")->select();
    if (empty($G_server)){
        return "G设备规则上传服务器连接失败!";
    }
    // echo $G_server[0]["conf_value"];
    $G_server_arr = explode("|", $G_server[0]["conf_value"]);

    #link g_server and update file
    if(!filter_var($G_server_arr[0],FILTER_VALIDATE_IP)){
        return "G设备规则上传服务器连接失败!";
    }

    try {
        $ssh2 = ssh2_connect($G_server_arr[0], $G_server_arr[1]);
        $stream = ssh2_auth_password($ssh2, $G_server_arr[2], $G_server_arr[3]);

        if (!$stream) {
            return "G设备规则上传服务器连接失败!";
        }

        $stream=ssh2_scp_send($ssh2, "/tmp/g_conf_str_file", $G_server_arr[4], 0777);
        if (!$stream) {
            return "G设备规则上传服务器连接失败!";
        }
    } catch (Exception $e) {
       return "G设备规则上传服务器连接失败!"; 
    }

    return "success";

    // echo $res_str;
}

function update_vpn_server_conf($server_ip){

    #get_server_username_password
    $vpn_server = db("sys_conf")->where("conf_type","2")->where(["conf_value" => ["like","%".$server_ip."%"]])->select();
    if (empty($vpn_server)){
        return "not set vpn server ";
    }
    $vpn_server_arr = explode("|", $vpn_server[0]["conf_value"]);

    #make config_file
    $vpn_conf_list = db("user_info")->where("vpn_server",$server_ip)->select();

    // if (empty($vpn_conf_list))
    //     return;

    $vpn_conf_str = "";
    $vpn_conf_str .= "# Secrets for authentication using CHAP\n";
    $vpn_conf_str .= "# client\tserver\tsecret\tIP addresses\n";

    foreach ($vpn_conf_list as $key => $value) {
        $vpn_conf_str .= $value["username"]."\t*\t".$value["password"]."\t".$value["p_ip"]."\n";
    }


    file_put_contents("/tmp/vpn_conf_str_file", $vpn_conf_str);

    #login vpn_server replace conf_file and restart service 
    if(!filter_var($vpn_server_arr[0],FILTER_VALIDATE_IP)){
        return "vpn服务器:".$server_ip."连接失败!";
    }

    try {
        $ssh2 = ssh2_connect($vpn_server_arr[0], $vpn_server_arr[1]);
        $stream = ssh2_auth_password($ssh2, $vpn_server_arr[2], $vpn_server_arr[3]);

        $stream=ssh2_scp_send($ssh2, "/tmp/vpn_conf_str_file", "/etc/ppp/chap-secrets", 0777);
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $stream = ssh2_exec($ssh2, "service xl2tpd restart");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $stream = ssh2_exec($ssh2, "service pptpd restart");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
    } catch (Exception $e) {
        return "vpn服务器:".$server_ip."连接失败!";
    }
    

    return "success";

}

function gencert($server_ip,$username,$p_ip){

    #get_server_username_password
    $vpn_server = db("sys_conf")->where("conf_type","2")->where(["conf_value" => ["like","%".$server_ip."%"]])->select();
    if (empty($vpn_server)){
        return "not set vpn server ";
    }

    $vpn_server_arr = explode("|", $vpn_server[0]["conf_value"]);

    try {
        $ssh2 = ssh2_connect($vpn_server_arr[0], $vpn_server_arr[1]);
        $stream = ssh2_auth_password($ssh2, $vpn_server_arr[2], $vpn_server_arr[3]);

        $stream = ssh2_exec($ssh2, "rm -rf /etc/openvpn/easy-rsa/2.0/keys/".$username.".*");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $stream = ssh2_exec($ssh2, "cd /etc/openvpn/easy-rsa/2.0/ && sed -i '\$d' vars");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $strs = "cd /etc/openvpn/easy-rsa/2.0/ && echo export KEY_CN=".$username." >> vars";
        $stream = ssh2_exec($ssh2, $strs);
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $stream = ssh2_exec($ssh2, "cd /etc/openvpn/easy-rsa/2.0/ && source vars && ./pkitool ".$username);
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        usleep(1500000);
        $stream = ssh2_exec($ssh2, "echo ifconfig-push ".$p_ip." 255.255.0.0 > /etc/openvpn/ccd/".$username);
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        $stream = ssh2_exec($ssh2, "chmod 777 /etc/openvpn/ccd/".$username);
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        $stream = ssh2_exec($ssh2, "cp /etc/openvpn/client.ovpn /etc/openvpn/easy-rsa/2.0/keys/".$username.".ovpn");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        $stream = ssh2_exec($ssh2, "echo cert ".$username.".crt >> /etc/openvpn/easy-rsa/2.0/keys/".$username.".ovpn");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        $stream = ssh2_exec($ssh2, "echo key ".$username.".key >> /etc/openvpn/easy-rsa/2.0/keys/".$username.".ovpn");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }
        $stream = ssh2_exec($ssh2, "cd /etc/openvpn/easy-rsa/2.0/keys && tar -cvf ".$username.".tar ta.key ca.crt ca.key ".$username.".crt ".$username.".csr ".$username.".key ".$username.".ovpn");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        $stream = ssh2_exec($ssh2,"systemctl restart openvpn@server");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        exec("mkdir -p /hard_disk/openvpn_cert");
        $stream = ssh2_scp_recv($ssh2, "/etc/openvpn/easy-rsa/2.0/keys/".$username.".tar", "/hard_disk/openvpn_cert/".$username.".tar");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        return "success";

    } catch (Exception $e) {
        return "vpn服务器:".$server_ip."连接失败!";
    }

}

function delcert($server_ip,$username){

    // print_r($username);

    #get_server_username_password
    $vpn_server = db("sys_conf")->where("conf_type","2")->where(["conf_value" => ["like","%".$server_ip."%"]])->select();
    if (empty($vpn_server)){
        return "not set vpn server ";
    }

    $vpn_server_arr = explode("|", $vpn_server[0]["conf_value"]);

    try {
        $ssh2 = ssh2_connect($vpn_server_arr[0], $vpn_server_arr[1]);
        $stream = ssh2_auth_password($ssh2, $vpn_server_arr[2], $vpn_server_arr[3]);

        if(!is_array($username))
            return "vpn服务器:".$server_ip."连接失败!";

        foreach ($username as $key => $value) {
            $stream = ssh2_exec($ssh2, "rm -rf /etc/openvpn/easy-rsa/2.0/keys/".$value.".*");
            if (!$stream) {
                return "vpn服务器:".$server_ip."连接失败!";
            }
            $stream = ssh2_exec($ssh2, "rm -rf /etc/openvpn/ccd/".$value);
            if (!$stream) {
                return "vpn服务器:".$server_ip."连接失败!";
            }

            exec("rm -rf /hard_disk/openvpn_cert/".$value.".tar");
        }

        

        $stream = ssh2_exec($ssh2,"systemctl restart openvpn@server");
        if (!$stream) {
            return "vpn服务器:".$server_ip."连接失败!";
        }

        return "success";

    } catch (Exception $e) {
        return "vpn服务器:".$server_ip."连接失败!";
    }

}

function get_mask($port){
    if ($port == 65535) {
        return [65534,15];
    }
    $b_port = decbin($port);
    $res = strpos($b_port, "0");
    $sub_str = substr($b_port, 0,$res);
    $mask = strlen($sub_str);
    $mask_port_str = get_mask_str($sub_str,16-$mask);
    $mask_port = bindec($mask_port_str);
    return [$mask_port,$mask];
}

function get_mask_str($sub_str,$len){
    $append_str = "";
    for($i = 0 ;$i < $len ; $i++) {
        $append_str.="0";
    }
    $sub_str.=$append_str;
    return $sub_str;
}
