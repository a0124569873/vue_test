<?php

define("API_SERVER_URL", "http://api.vedasec.net");


use think\Queue;
use think\Env;
use app\index\job\BaseJob;

if (!function_exists('env')) {
    /**
     * 获取环境变量的值
     * @param string $key
     * @param null $default
     * @return mixed
     */
    function env($key = '', $default = null)
    {
        return Env::get($key, $default);
    }
}

function IsLogin()
{
    $user = session('user_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session('user_auth_sign') == AuthSign($user) ? $user['id'] : 0;
    }
}

function AuthSign($data)
{
    if (!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data);//对数组进行降序排列
    $code = http_build_query($data);//将数组处理为url-encoded 请求字符串
    $sign = sha1($code);//进行散列算法

    return $sign;
}

//获取get请求
function Sendget($url)
{
    $data = file_get_contents($url);
    if (is_null($data) || empty($data))
        return Finalfail("20002", "the api services error");

    $data_arr = json_decode($data, true);

    return $data_arr;
}

/**
 * post方法发送表单请求
 * @param  string $url 请求url
 * @param  string $post_data 数据
 * @param  string $method 提交方式 form or json
 * @return array     返回结果数组
 */
function Sendpost($url, $post_data, $method = "from")
{ // 发送post请求
    if ($method == "json") {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/json\r\n" //json
                    . "Content-Length: " . strlen($post_data) . "\r\n",
                'content' => $post_data,
                'timeout' => 15 * 60 // 超时时间（单位:s）  
            ]
        ];
    } else {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => http_build_query($post_data),
                'timeout' => 15 * 60 // 超时时间（单位:s）  
            ]
        ];
    }

    try {
        $result = file_get_contents($url, false, stream_context_create($options));
    } catch (Exception $e) {
        return false;
    }

    if (is_null($result) || empty($result))
        return Finalfail("20002", "the api services error");

    $result_arr = json_decode($result, true);

    return $result_arr;
}

/**
 * 接口返回成功方法
 * @param  string|array $data 返回数据
 * @param  string $code 状态码默认0为请求成功
 * @return string    json
 */
function Finalsuccess($data = NULL, $code = "0")
{
    $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok'];
    if (is_null($data)) {
        return json($jsonData);
    } else if (is_array($data) && empty($data)) {
        $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok', 'data' => []];
    } else if (is_array($data)) {
        foreach ($data as $name => $value) {
            $jsonData[$name] = $value;
        }
    } else {
        $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok', 'data' => $data];
    }

    return json($jsonData);
}

/**
 * 接口返回失败方法
 * @param  string $code 状态码
 * @param  string $errorMessage 错误信息
 * @param  array $additionResults 追加data数据
 * @return string    json
 */
function Finalfail($code, $errorMessage = '', array $additionResults = [])
{
    $jsonData = ['errcode' => intval($code), 'errmsg' => $errorMessage];
    if (!empty($additionResults)) {
        foreach ($additionResults as $name => $value) {
            $jsonData[$name] = $value;
        }
    }

    return json($jsonData);
}

/**
 * 接口失败
 * @param  String $code 状态码 （若为10001|miss，则解析为状态码+错误信息）
 * @param  String $errorMessage 错误信息
 * @param  Array $additionResults 追加data数据
 * @return String    json字符串
 */
function Error($code, $errorMessage = '', array $additionResults = [])
{
    $jsonData = ['errcode' => intval($code), 'errmsg' => $errorMessage];
    if (!empty($additionResults)) {
        foreach ($additionResults as $name => $value) {
            $jsonData[$name] = $value;
        }
    }

    header("Content-Type: application/json");
    die(json_encode($jsonData));
}

/**
 * 接口失败
 * @param  String $code 状态码 （若为10001|miss，则解析为状态码+错误信息）
 * @param  String $errorMessage 错误信息
 * @param  Array $additionResults 追加data数据
 * @return String    json字符串
 */
function Fail($code, $errorMessage = '', array $additionResults = [])
{
    header("Content-Type: application/json");
    $tmp = explode("|", $code);
    if (count($tmp) == 2) {
        $code = $tmp[0];
        $errorMessage = $tmp[1];
    }

    $jsonData = ['errcode' => intval($code), 'errmsg' => $errorMessage];
    if (!empty($additionResults)) {
        foreach ($additionResults as $name => $value) {
            $jsonData[$name] = $value;
        }
    }

    die(json_encode($jsonData));
}

/**
 * 接口返回成功方法
 * @param  string|array $data 返回数据
 * @param  string $code 状态码默认0为请求成功
 * @return string    json
 */
function Success($data = null, $code = "0")
{
    header("Content-Type: application/json");
    $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok'];
    if (is_null($data)) {
        die(json_encode($jsonData));
    }

    if (is_array($data) && !empty($data)) {
        foreach ($data as $name => $value) {
            $jsonData[$name] = $value;
        }
    } else {
        $jsonData = ['errcode' => intval($code), 'errmsg' => 'ok', 'data' => $data];
    }
    die(json_encode($jsonData));
}


function Time2string($min)
{
    if ($min == 0) {
        $time_str = '';
    }
    if ($min > 0 && $min < 60) {
        $time_str = $min . 'm';
    }
    if ($min >= 60 && $min < 1440) {
        $time_str = floor($min / 60) . "h";
    }
    if ($min >= 1440) {
        $time_str = floor($min / 1440) . "d";
    }

    return $time_str;
}

function String2time($str)
{
    if ($str == "") {
        $time = 0;
    }
    $unit = substr($str, -1);
    $time = (int)substr($str, 0, strlen($str) - 1);
    if ($unit == 'h') {
        $time = $time * 60;
    }
    if ($unit == 'd') {
        $time = $time * 24 * 60;
    }

    return $time;
}

function ShortMd5($str)
{
    return substr(md5($str), 8, 16);
}

function NowTime()
{
    return date("Y-m-d H:i:s");
}

function EncryptPwd($pwd)
{
    $crypt_pwd = md5(crypt($pwd, substr($pwd, 0, 2)));

    return $crypt_pwd;
}

function CheckIpOk($ip)
{
    return !empty(ip2long($ip));
}

function CheckPort($port)
{
    $a = @intval($port);

    return $a > 0 && $a < (2 << 16);
}

function Randchar($length = "")
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $randchar = "";
    for ($i = 0; $i < $length; $i++) {
        $randchar .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $randchar;
}

function Randnum($length = "")
{
    $chars = "0123456789";
    $randchar = "";
    for ($i = 0; $i < $length; $i++) {
        $randchar .= $chars[mt_rand(0, strlen($chars) - 1)];
    }

    return $randchar;
}

if (!function_exists('dd')) {
    /**
     * 打印内容并停止程序运行
     * @param array ...$args
     */
    function dd(...$args)
    {
        dump($args);
        die;
    }
}

if (!function_exists('str_rand')) {
    /**
     * 生成指定长度的随机字符串
     * @param int $length
     * @return string
     */
    function str_rand($length = 0)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}

if (!function_exists('micro_timestamp')) {
    /**
     * 获取毫秒级时间戳
     * @param null $timestamp
     * @return float
     */
    function micro_timestamp($timestamp = null)
    {
        $_timestamp = (int)(microtime(true) * 1000);
        $_timestamp = $timestamp ? $timestamp + 1 : $_timestamp;

        return $_timestamp;
    }
}

if (!function_exists('gmt_withTZ')) {
    /**
     * 生成附带时区标志的 GMT 时间
     * example "2018-03-31T04:04:10.000Z"
     * @param null $timestamp
     * @return false|string
     */
    function gmt_withTZ($timestamp = null)
    {
        $timestamp = $timestamp ?: time();

        return gmdate('Y-m-d\TH:i:s.v\Z', $timestamp);
    }
}

if (!function_exists('dispatch')) {
    /**
     * 添加一个队列任务到队列中
     * @param $job
     * @param null $data
     */
    function dispatch($job, $data = null)
    {

        if (is_a($job, BaseJob::class)) {
            $job = get_class($job);
        }

        return Queue::push($job, $data);
    }
}