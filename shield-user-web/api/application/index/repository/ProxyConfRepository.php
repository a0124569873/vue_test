<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/30
 * Time: 18:35
 */

namespace app\index\repository;

use app\index\model\ProxyConfModel;

class ProxyConfRepository
{
    public function __construct()
    {
        $this->model = new ProxyConfModel();
    }

    public function getProxyByFilter($site)
    {
        $filter['query']['bool']['must'][] = ['term' => ['web.name.keyword' => $site]];
        $result = $this->model->esSearch($filter);
        return empty($result) ? null : $result;
    }

    public function getBlackWhiteList($site)
    {
        $list = [
            "url_white_list" => [],
            "url_black_list" => [],
            "ip_white_list"  => [],
            "ip_black_list"  => [],
        ];
        $filter['query']['bool']['must'][] = ['term' => ['web.name.keyword' => $site]];
        $result = $this->model->esSearch($filter);
        if (empty($result)) {
            return $list;
        }

        $data = [];
        foreach ($result[0]['web'] as $tmp) {
            if ($tmp['name'] === $site) {
                $data = $tmp['filter'];
            }
        }
        if (empty($data)) {
            return $list;
        }

        $list["url_white_list"] = $data['url_whitelist'];
        $list["url_black_list"] = $data['url_blacklist'];
        $list["ip_white_list"] = $data['ip_whitelist'];
        $list["ip_black_list"] = $data['ip_blacklist'];
        return $list;
    }

   /**
    * 操作网址IP黑白名单
    *
    * @param [type] $oper
    * @param [type] $type
    * @param [type] $_data
    * @param [type] $site
    * @return boolean
    */
    public function updateBlackWhiteList($oper, $type, $_data, $site): bool
    {
        try {
            $proxy_conf = $this->getProxyByFilter($site);
            if(is_null($proxy_conf)){
                return false;
            }

            if($oper === "add"){
                foreach ($proxy_conf[0]['web'] as &$tmp) {
                    if ($tmp['name'] === $site) {
                        $tmp['filter'][$type][] = $_data['keyword'];
                    }
                }
            }elseif($oper === "del"){
                foreach ($proxy_conf[0]['web'] as &$tmp) {
                    if ($tmp['name'] === $site) {
                        $del_arr = explode(",", $_data['keyword']);
                        $tmp['filter'][$type] = array_diff($tmp['filter'][$type], $del_arr);
                    }
                }
            }

            return (bool)$this->model->esUpdateById(["web" => $proxy_conf[0]['web']], $proxy_conf[0]['ip']);
        } catch (\Exception $e) {
            return false;
        }
    }

}
