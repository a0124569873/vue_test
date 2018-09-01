<?php

namespace app\index\validate;

use app\index\traits\ValidatorRules;
use think\Validate;

class Site extends Validate
{
    use ValidatorRules;

    protected $checkingFieldName = null;

    protected $checkingFieldTitle = null;

    // 缓存失效期的单位
    const CACHE_TIME_UNITS = ['minute', 'hour', 'day'];

    protected $message = [
        'port.require' => '22001',
        'id.require' => '22001',
        'rip.ip' => '11006',
        'http.required' => 'http或https二选一'
    ];

    protected $rule = [
        'port' => 'require|port',
        'domain' => 'require|domain',
        'site' => 'require|domain',
        'id' => 'require',
        'rip' => 'require|ip',
        'ip' => 'require|ip',
        'linked_ips' => 'require|array|min:1|linkedIp',
        'udomain' => 'require|domain',
        'upstream' => 'require|upstream',
        'http' => 'require|boolean',
        'https' => 'require|boolean',
        'name' => 'require|domain',
        'type' => 'require|in:1,2',
        'certificate' => 'require|min:1|base64Str',
        'certificate_key' => 'require|min:1|base64Str',
        'request_time' => 'require|integer|min:1',
        'ids' => 'require|array|min:1',

        'static_expire' => 'require|cacheTime',    // 静态资源缓存有效期
        'html_expire' => 'require|cacheTime',   // 静态页面缓存有效期
        'index_expire' => 'require|cacheTime',  // 首页缓存有效期
        'directory_expire' => 'require|cacheTime',  // 目录缓存有效期

        'keyword' => 'require|min:1',  //缓存关键字
        'keywords' => 'require|array|min:1|keywords', // 缓存关键字数组
        'expire' => 'require|cacheTime',  //
        'blacklist_expire' => 'require|cacheTime',

        'urlWhitelist' => 'require|array', // Url 白名单
        'urlBlacklist' => 'require|array', // Url 黑名单
        'ipBlacklist' => 'require|array|ips', // IP 黑名单
        'ipWhitelist' => 'require|array|ips', // IP 黑名单
    ];

    protected $field = [
        'port' => '端口',
        'domain' => '防护网站',
        'name' => '防护网站',
        'http' => 'http',
        'https' => 'https',
        'upstream' => '源站IP',
        'linked_ips' => '接入实例IP',
        'type' => '接入类型',
        'certificate' => '证书',
        'certificate_key' => '证书私钥',
        'ids' => '站点ID',

        'static_expire' => '静态资源缓存有效期',
        'html_expire' => '静态页面缓存有效期',
        'index_expire' => '首页缓存有效期',
        'directory_expire' => '目录缓存有效期',

        'keyword' => '缓存关键字',
        'keywords' => '缓存关键字',
        'expire' => '缓存过期时间',

        'urlWhitelist' => 'URL白名单',
        'urlBlacklist' => 'URL黑名单',
        'ipWhitelist' => 'IP白名单',
        'ipBlacklist' => 'IP黑名单',
    ];

    protected $scene = [
        'get_udomain' => [],    // 获取用户所有站点
        'del_site' => ['id'],   // 删除站点
        'update_site' => ['upstream', 'http', 'https'],   // 更新站点
        'get_textcode' => ['domain'],   // 获取站点的TextCode值
        'update_site_conf' => ['udomain', 'rip', 'port'],   // 更新站点设置
        'site_add' => ['name', 'http', 'https', 'upstream'],    // 添加站点
        'linkup' => ['linked_ips', 'type'],  // 站点接入
        'update_linkup' => ['linked_ips'],  // 更新域名接入信息

        'update_config' => ['name', 'http', 'https', 'linked_ips'],
        'https_cert_upload' => ['certificate', 'certificate_key'],  // 站点HTTPS证书上传
        'get_https_cert' => ['site'],   // 获取站点的HTTPS证书
        'bundle_delete' => ['ids'], // 批量删除
        'set_site_cache_expire' => ['static_expire', 'html_expire', 'index_expire', 'directory_expire'], // 设置站点缓存有效期

        'set_site_cache_keywords' => ['keyword', 'expire'], // 设置站点缓存有效期
        'del_site_cache_keywords' => ['keywords'], // 移除站点缓存关键字

        'set_site_cache_blackList' => ['keyword'], // 设置缓存黑名单
        'del_site_cache_blacklist' => ['keywords'],  // 删除缓存黑名单

        'set_url_whitelist' => ['urlWhitelist'], // 站点URL白名单
        'set_url_blacklist' => ['urlBlacklist'], // 站点URL黑名单
        'set_ip_blacklist' => ['ipBlacklist'], //  站点IP黑名单
        'set_ip_whitelist' => ['ipWhitelist'], //  站点IP白名单
    ];

    protected function linkedIp($value)
    {
        if (count($value) == 0) {
            return '至少有提供一个接入IP';
        }

        foreach ($value as $ip) {
            if (!isset($ip['line']) || empty($ip['ip']) || empty($ip['ddos_id'])) {
                return '接入IP格式不正确';
            }
        }

        return true;
    }

    // 验证端口
    protected function port($value)
    {
        return is_numeric($value) && floor($value) == $value && $value >= 0 && $value <= 65535 ? true : '端口不符合规范！';
    }

    // 验证域名
    protected function domain($value)
    {
        if (!Validate::regex($value, '\b([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}\b')) {
            return "域名不符合规范！";
        }

        if (count(explode('.', $value)) < 2) {
            return "域名不符合规范！";
        }

        return true;
    }

    // 验证整数组
    protected function multiInteger($value, $rule, $data)
    {
        $integers = explode(',', $value);
        $legals = array_filter($integers, function ($item) {
            return floor($item) == $item && is_numeric($item);
        });

        return count($integers) == count($legals) ? true : '11010';
    }

    protected function upstream($upstream)
    {
        if (empty($upstream)) {
            return false;
        }

        $ips = explode(',', $upstream);
        foreach ($ips as $ip) {
            if (!Validate::is($ip, 'ip')) {
                return false;
            }
        }

        return true;
    }

    protected function base64Str($value)
    {
        if (!base64_decode($value)) {
            return '不是有效的Base64数据';
        }

        return true;
    }

    /**
     * 检查缓存失效期的格式
     *
     * @param $value
     * @return string
     */
    protected function cacheTime($value)
    {
        try {
            list($num, $unit) = explode(':', $value);
            if (!is_numeric($num) || !in_array($unit, self::CACHE_TIME_UNITS)) {
                return $this->checkingFieldTitle . '格式错误！';
            }

            return true;
        } catch (\Exception $e) {
            return $this->checkingFieldTitle . '格式错误！';
        }
    }

    // 检查缓存关键字数组
    protected function keywords($value)
    {
        foreach ($value as $item) {
            if (!is_string($item)) {
                return '缓存关键字格式不正确！';
            }
        }

        return true;
    }

    // 重写TP Validate,获得当前正在校验的字段和别名
    protected function checkItem($field, $value, $rules, $data, $title = '', $msg = [])
    {
        // 记录当前正在校验的字段
        $this->checkingFieldName = $field;
        $this->checkingFieldTitle = $title;

        return parent::checkItem($field, $value, $rules, $data, $title, $msg); // TODO: Change the autogenerated stub
    }

}

