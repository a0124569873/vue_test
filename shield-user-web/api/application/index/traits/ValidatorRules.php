<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/26
 * Time: 14:33
 */

namespace app\index\traits;


use think\Validate;

trait ValidatorRules
{

    protected $checkingFieldName = null;

    protected $checkingFieldTitle = null;

    protected function ips($ips)
    {
        foreach ($ips as $ip) {
            $_ips = explode('-', $ip);
            foreach ($_ips as $_ip) {
                if (!Validate::ip($_ip, 'ipv4')) {
                    return $this->checkingFieldTitle . '规则错误！';
                }
            }
        }

        return true;
    }

    protected function domains($domains)
    {
        foreach ($domains as $domain) {
            if ($this->domain($domain) !== true) {
                return $this->checkingFieldTitle . '规则错误！';
            }
        }

        return true;
    }

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

}