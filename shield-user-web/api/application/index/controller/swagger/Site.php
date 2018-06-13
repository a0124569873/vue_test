<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/13
 * Time: 17:52
 */

namespace app\index\controller\swagger;

/**
 * @SWG\Definition()
 */
class Site
{
    /**
     * @SWG\Definition(
     *     definition="SiteList",
     *     type="object",
     *     @SWG\Property(
     *          property="list",
     *          type="object",
     *          example={{"id":"test1.com","uid":"test@veda.com","name":"test1.com","type":0,"http":0,"https":1,
     *     "upstream":"127.0.0.1,127.0.0.2","status":3,"text_code":"251d3984ea7f0b","ip":{},"last_update":1522289309,
     *     "hd_ip":{{"line":"11_1","ip":"192.16.1.1"},{"line":"12_1","ip":"152.141.10.1"}},"cname":"1a27f5213ab62b3d.hd.vedasec.net"}}
     *      ),
     *      @SWG\Property(
     *          property="total",
     *          type="string",
     *          example="5"
     *      ),
     *     @SWG\Property(
     *          property="errcode",
     *          type="integer",
     *          example=0
     *      ),
     *     @SWG\Property(
     *          property="errmsg",
     *          type="string",
     *          example="ok"
     *      ),
     * )
     */

    /**
     * @SWG\Definition(
     *    definition="SiteBwList",
     *    type="object",
     *    @SWG\Property(property="url_white_list", type="object", example={"test","host"}, description="网址白名单"),
     *    @SWG\Property(property="url_black_list", type="object", example={"veda","haha"}, description="网址黑名单"),
     *    @SWG\Property(property="ip_white_list", type="object", example={"1.1.1.1","2.2.22.2"}, description="IP白名单"),
     *    @SWG\Property(property="ip_black_list", type="object", example={"3.32.3.3","44.4.4.4"}, description="IP黑名单")
     * )
     *
     */

    /**
     * @SWG\Definition(
     *     definition="Site Status",
     *      type="object",
     *      @SWG\Property(property=" 0 ", type="用户已提交，待审核"),
     *      @SWG\Property(property="1", type="已审核，待接入"),
     *      @SWG\Property(property="2", type="正在接入"),
     *      @SWG\Property(property="3", type="已接入，未修改DNS解析"),
     *      @SWG\Property(property="4", type="正常"),
     *      @SWG\Property(property="5", type="接入错误"),
     *      @SWG\Property(property="6", type="正在删除"),
     *      @SWG\Property(property="7", type="删除异常"),
     *
     *  )
     */

    /**
     * @SWG\Definition(
     *     definition="Site Cache",
     *      type="object",
     *      @SWG\Property(property="static_expire", type="静态资源缓存有效期"),
     *      @SWG\Property(property="html_expire", type="静态页面缓存有效期"),
     *      @SWG\Property(property="index_expire", type="首页缓存有效期"),
     *      @SWG\Property(property="directory_expire", type="目录缓存有效期")
     *  )
     */

}