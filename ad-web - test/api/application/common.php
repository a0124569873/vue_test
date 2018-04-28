<?php 

use think\Db;

define("PROTECT_CONF_SHM_FILE", "/tmp/ddos_config"); // 防护配置共享内存filetokey
define("PROTECT_CONF_SHM_SIZE",33554432); // 防护配置共享内存大小
define("TMP_BW_LIST_SHM_FILE", "/tmp/ddos_tmp_b_w"); // 临时黑白名单共享内存filetokey
define("TMP_BW_LIST_SHM_SIZE",33554432); // 临时黑白名单共享内存大小
define("MYSQL_USERNAME", "root");
define("MYSQL_PASSWORD", "veda");
define("DATABASE_NAME", "CleanDB");

function IsLogin(){
    $user = session('user_auth');
    if(empty($user)){
        return 0;
    }else{
        return session('user_auth_sign') == AuthSign($user) ? $user['username'] : 0;
    }
}

function AuthSign($data) {
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data);//对数组进行降序排列
    $code = http_build_query($data);//将数组处理为url-encoded 请求字符串
    $sign = sha1($code);//进行散列算法
    return $sign;
}

//（前置方法中）登陆或token验证
function CheckLoginToken(){
    $user_auth = session('user_auth');
    $user_token = input('get.token');

    if(!is_null($user_auth) && (session('user_auth_sign') == AuthSign($user_auth)) ){
        return true;
    }

    if(empty($user_token)){
        return false;
    }

    $result = Db::table('users')->where('access_token', $user_token)->select();
    if(empty($result)){
        return false;
    }else{
        $auth = array(
            'username'    => $result[0]['username'],
            'group_id'    => $result[0]['group_id'],
            'access_token'=> $result[0]['access_token']
        );
        session('user_auth',$auth);
        return true;
    }
}

function CheckValid(){ //（前置方法中）证书合格验证
    exec("uroot fpcmd licence_status",$cert_status);
    if(empty($cert_status)){
        return "12003";
    }

    $cert_status = json_decode($cert_status[0],true);
    if($cert_status["status"] != "valid") {
        return "12003";
    }else{
        return '0';
    }
}

function PasswordHash($password) {
    $password = md5(hash("sha1", $password));
    return $password;
}

function TokenHash($token) {
    $token = base64_encode(hash("sha1", $token));
    return $token;
}

/**
 * 接口返回成功方法
 * @param  string|array    $data  返回数据
 * @param  string    $code  状态码默认0为请求成功
 * @return string    json
 */
function Finalsuccess($data=NULL, $code="0"){
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

function ExcuteExec($order){
    $output=[];
    $status=[];
    exec($order,$output,$status);
    if($status!=0){
        Error('20005','error: '.$order);
    }
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

//随机生成6个字母
function RandChars(){
    $chars = '';
    for($i=1;$i<=4;$i++){
        $chars .= chr(rand(97,122));
    }
    return $chars;
}

/**
 * 解析十进制和为二进制中哪几位相加组成
 * @param  int    $int  
 * @return array  
 */
function ParseMaskSum(int $int){
    $mask = decbin($int);
    $mask_arr = [];
    for( $i=0 ; $i<strlen($mask) ; $i++ ){
        if($mask{$i} == 1){
            array_push($mask_arr, pow(2,$i));
        }
    }
    return $mask_arr;
}

/**
 * 解析组成的mask串之和 如1,2,3 得 7 
 * @param  String    $type_str  
 * @return int       mask之和
 */
function ParseModType($type_str){
    $type_arr = explode(',', $type_str);
    $sum = 0;
    foreach($type_arr as $type){
        $sum += pow(2,($type-1));
    }
    return $sum;
}

/**
 * trim配置参数中的空格等
 * @param  string    $param  "0|80|100|210|1,2"
 * @return array    
 */
function TrimParams(String $param){
    $conf_arr = array_map(function($item){
        return trim($item);
    },explode("|", $param));
    return $conf_arr;
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

function GetEthnets(){
    $list = scandir("/sys/class/net");
    $all_eth = [];
    foreach($list as $dir){
        $file_path = "/sys/class/net/".$dir."/address";
        if(file_exists($file_path) && preg_match("/^eth\d+_0$/", $dir) == 1){     
            $all_eth[] = $dir;          
        }
    }
    return $all_eth;
}

/**
 * 读取共享内存内容
 * @param string $file 文件
 * @param int $id id
 * @param int $size 共享内存大小
 * @param int $read_size 读取内容字节数
 * @return string $shm_datas
 */
function ReadShm($file, $id, $size, $read_size){
    $shm_key = myftok($file, $id);
    $shm_id = shmop_open($shm_key, 'c', 0644, $size);
    if (!$shm_id) 
        return NULL;

    $shm_datas = shmop_read($shm_id, 0, $read_size);
    if (!$shm_datas) 
        return NULL;

    shmop_close($shm_id);
    return $shm_datas;
}

/**
 * 将配置信息写入共享内存
 * @param String $config
 * @return Boolean
 */
function WriteInShm($config){
    exec("uroot fpcmd fp_shm_clear -config");

    $shm_key = myftok(PROTECT_CONF_SHM_FILE, "1");
    $shm_id = shmop_open($shm_key, 'w', 0644, PROTECT_CONF_SHM_SIZE);
    if (!$shm_id) 
        return false;

    $shm_bytes_written = shmop_write($shm_id, $config, 0);
    if (!$shm_bytes_written) 
        return false;

    shmop_close($shm_id);
    return true;
}

// 适配ftok
function myftok($pathname, $proj_id)   
{   
   $st = @stat($pathname);   
   if (!$st) {   
       return -1;   
   }   
   $key = sprintf("%u", (($st['ino'] & 0xffff) | (($st['dev'] & 0xff) << 16) | (($proj_id & 0xff) << 24)));  
   return $key;   
}  

/**
 * 将sql语句写入文件并导入数据表
 * @param String $sql_str
 * @return Boolean 
 */
function ImportSqlStr($sql_str){
    $sql_file_name = "sqlfile".strtotime("now");
    $sqlfile = fopen("/var/tmp/$sql_file_name", "w");
    if(!$sqlfile){
        return false;
    }
    $result = fwrite($sqlfile, $sql_str);
    if(!$result){
        return false;
    }
    fclose($sqlfile);
    exec("uroot mysql -u".MYSQL_USERNAME." -p".MYSQL_PASSWORD." --default-character-set=UTF8 ".DATABASE_NAME." < /var/tmp/$sql_file_name");
    exec("uroot rm /var/tmp/$sql_file_name");
    return true;
}

// 数组按键排序
function ArrKeySort($arr, $rules = []) {
    if (empty($rules)) {
        array_multisort($arr);
        return $arr;
    }
    if (empty($arr)) {
        return [];
    }
    foreach ($arr as $k => $a) {
        foreach ($rules as $rk => $rule) {
            $orderby[$rk][$k] = $a[$rule['orderby']];
            $order[$rk] = $rule['order'] == 'desc' ? SORT_DESC : SORT_ASC;
            $order_flag[$rk] = isset($rule['order_flag']) ? $rule['order_flag'] : SORT_REGULAR;
        }
    }
    foreach ($rules as $rk => $rule) {
        $params[] = $orderby[$rk];
        $params[] = $order[$rk];
        $params[] = $order_flag[$rk];
    }
    $params[] = &$arr;
    array_multisort(...$params);
    return $arr;
}

//查询流量日志
function selectFlowLogs($date_arr, $ip,$table_name = ""){
    $times_arr = array_map(function($timestamp){
        return date("Y-m-d H:i:s",$timestamp);
    },$date_arr);

    if (empty($ip)) {
        $dates = Db::table($table_name)->where("time", "IN", $times_arr)->select();
    } else {
        $dates = Db::table($table_name)->where(["ip" => $ip, "time" => ["IN", $times_arr]])->select();
    }
    return $dates;
}

//【方法】根据周期计算时间节点
function parseCounts($recent){
    $now_time = strtotime("now");
    $date_arr = [];

    if(in_array($recent,["2","3","4"])){
        $end_time = floor($now_time/30) * 30;
    }elseif(in_array($recent,["5","6"])){
        $end_time = floor($now_time/300) * 300;
    }elseif($recent == 7) {
        $end_time = floor($now_time/3600) * 3600;
    }elseif($recent == 8) {
        $end_time = strtotime(date('Y-m-d', strtotime('+1 Day', $now_time)));
    }elseif($recent == 9) {
        $now_month_time = strtotime(date('Y-m', $now_time));
        $end_time = strtotime(date('Y-m', strtotime('+1 Month', $now_month_time)));
    }else{
        $end_time = date("Y-m-d H",$now_time).":00:00" ;
    }
    $time = $end_time;
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
            $counts = 120;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-30 Second', $time);
            }
        break;
        case "5":
            $counts = 12*60/5;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-5 Minute', $time);
            }
        break;
        case "6":
            $counts = 24*60/5;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-5 Minute', $time);
            }
        break;
        case "7":
            $counts = 7*24;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-1 Hour', $time);
            }
        break;
        case "8":
            $counts = 24*60/5;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-5 Minute', $time);
            }
        break;
        case "9":
            $days = date("t",$now_time);
            $counts = $days*24;
            for($i = 0;$i <= $counts; $i++){
                $date_arr[] = $time;
                $time = strtotime('-1 Hour', $time);
            }
        break;
    }
    return $date_arr;
}

//获取系统状态日志(cpu,disk,memory)
function selectSystemLogs($date_arr, $table_name){
    $times_arr = array_map(function($timestamp){
        return date("Y-m-d H:i:s",$timestamp);
    },$date_arr);
    $dates = Db::table($table_name)->where("time", "IN", $times_arr)->select();

    return $dates;
}

//获取日志时间戳为空时填充 空数据
function fill_timestamp($date_arr,&$value_arr,$fill_arr,$each_item_add){
    $res_data = array_map(function($log) {
        return $log["timestamp"];
    }, $value_arr);
    foreach ($date_arr as $key => $value) {
        if (!in_array($value, $res_data)) {

            foreach ($each_item_add as $key => $value1) {
                $fill_arr[$value1] = ["0",$value];
            }
            $fill_arr["timestamp"] = $value;
            array_push($value_arr,$fill_arr);
        }
    }
}