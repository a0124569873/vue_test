<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/28
 * Time: 14:13
 */

namespace app\index\service;


class Curl
{
    protected $options = [
        'RETURNTRANSFER' => true,                   // 将获取的信息返回
        'FAILONERROR'    => false,                  // 请求异常的处理方式
        'CONNECTTIMEOUT' => '',                      // 链接超时时间
        'TIMEOUT'        => 30,                      // 请求超时时间
        'USERAGENT'      => '',                      // 浏览器信息
        'URL'            => '',                      // 请求地址
        'POST'           => false,                  // 请求方式
        'HTTPHEADER'     => array(),                // 请求头
        'SSL_VERIFYPEER' => false,                  // 不进行证书验证
        'HEADER'         => false,                  // 启用时会将头文件的信息作为数据流输出
    ];

    protected $data = [];

    public function to($url)
    {
        return $this->withOption('URL', $url);
    }

    public function withTimeout($timeout = 30)
    {
        return $this->withOption('CONNECTTIMEOUT', $timeout);
    }

    public function withHeader($header)
    {
        $this->options['HTTPHEADER'][] = $header;

        return $this;
    }

    public function withHeaders(array $headers = [])
    {
        $this->options['HTTPHEADER'] = array_merge(
            $this->options['HTTPHEADER'], $headers
        );

        return $this;
    }

    public function withData($data = [])
    {
        $this->data = $data;

        return $this;
    }

    public function get()
    {
        return $this->send();
    }

    public function post(string $url = '', $data = [])
    {
        $url && $this->to($url);
        !empty($data) && $this->withData($data);
        $this->setPostParams();

        return $this->send();
    }

    protected function send()
    {
        $ch = curl_init();
        $options = $this->forgeOptions();
        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    protected function withOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    protected function setData($data = [])
    {
        $this->data = $data;
    }

    protected function setPostParams()
    {
        $this->options['POST'] = true;

        $this->options['POSTFIELDS'] = json_encode($this->data);
    }

    private function forgeOptions()
    {
        $options = [];
        foreach ($this->options as $key => $value) {
            $options[constant('CURLOPT_' . $key)] = $value;
        }

        return $options;
    }
}