<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 18:52
 */

namespace app\index\repository;

use app\index\model\DomainModel;
use app\index\service\Auth;

class DomainRepository extends BaseRepository
{

    public function __construct()
    {
        $this->model = new DomainModel();
    }

    /**
     * 生成幻盾安全域名
     * @param $userDomain
     * @return string
     */
    public static function generateHDDomain($userDomain)
    {
        return $userDomain ? DomainModel::DOMAIN_PREFIX_HD . '.' . $userDomain : '';
    }

    /**
     * 生成域名的CName地址
     * @param $domain
     * @param $IPLinked
     * @return string
     */
    public static function generateDomainCName($domain, $IPLinked)
    {
        $hash = ShortMd5($domain . $IPLinked);
        $cname = $hash . DomainModel::DOMAIN_CNAME_TAIL;

        return $cname;
    }

    /**
     * 验证域名是否已经配置Txt解析
     * 1.校验失败返回错误代码；2.校验成功返回获取到的TextCode
     *
     * @param $vDomain
     * @return int|string 错误代码|textCode
     */
    public static function domainIsValid($vDomain)
    {
        $command = "nslookup -q=txt $vDomain";
        $retval = array();
        exec($command, $retval, $status);
        if ($status != 0 || count($retval) < 5) {
            return 10017;
        }

        $arr = explode("\t", $retval[4]);
        if (count($arr) < 2) {
            return 10017;
        }

        $textCode = trim(explode(" ", $arr[1])[2], '"');

        return $textCode;
    }

    /**
     * 检查 Domain 是否存在
     * @param $domain
     * @return bool
     */
    public function domainIsExist(string $domain): bool
    {
        $domain = $this->model->esGetById($domain);

        return $domain ? true : false;
    }

    /**
     * 添加新的Domain
     *
     * @param $attributes
     * @return array|mixed
     */
    public function addDomain($attributes)
    {
        $data = [
            'uid' => Auth::id() ?: 0,
            'name' => $attributes['name'],
            'type' => DomainModel::DOMAIN_TYPE_NONE,
            'http' => $attributes['http'],
            'https' => $attributes['https'],
            'upstream' => explode(',', $attributes['upstream']),
            'status' => DomainModel::DOMAIN_STATUS_CREATED,
            'text_code' => ShortMd5($attributes['name']),
        ];

        return $this->model->esAdd($data, $attributes['name']);
    }

    /**
     * 获取域名的TextCode值
     * @param $domain
     * @return mixed|null
     */
    public function getDomainTextCode($domain)
    {
        $domain = $this->model->esGetById($domain);
        if (!empty($domain)) {
            return $domain['text_code'];
        }

        return null;
    }

    /**
     * 更新域名状态
     * @param $domain
     * @param $status
     * @return bool
     */
    public function updateSiteStatus($domain, $status)
    {
        $domainDetail = $this->model->esGetById($domain);
        $domainDetail['status'] = $status;

        $result = $this->model->esUpdateById($domainDetail, $domain);

        return $result ? true : false;
    }

    /**
     * @param $domain
     * @return array|false|\PDOStatement|string|\think\Model
     */
    public function getDomainByDomain($domain)
    {
        $domain = $this->model->esGetById($domain);

        return $domain;
    }

    /**
     * 更新域名的信息
     * @param $domain
     * @param $cname
     * @param $IPLinked
     * @param $status
     * @return DomainModel
     */
    public function updateDomainConf($domain, $cname, $IPLinked, $status)
    {
        $result = $this->model->where('domain', $domain)
            ->update(['domain' => $domain, 'ip_linked' => $IPLinked, 'veda_domain' => $cname, 'status' => $status]);

        return $result;
    }

    public function domainList($filter = [], $from = 0, $size = null)
    {
        $_domains = $this->model->esSearch($filter, $from, $size);
        $domains = array_map(function ($domain) {
            $domain['upstream'] = !empty($domain['upstream']) ? implode(',', $domain['upstream']) : '';
            $domain['id'] = !empty($domain['upstream']) ? $domain['name'] : '';

            return $domain;
        }, $_domains);

        return $domains;
    }

    public function removeDomain($id)
    {
        $result = $this->model->esDeleteById($id);

        return $result;
    }
}