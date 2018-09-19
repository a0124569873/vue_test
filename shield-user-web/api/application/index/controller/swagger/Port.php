<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/28
 * Time: 19:06
 */

namespace app\index\controller\swagger;


class Port
{
    /**
     * @SWG\Definition(
     *    definition="PortList",
     *    type="object",
     *    @SWG\Property(
     *         property="list",
     *         type="object",
     *         example={"errcode":0,"errmsg":"ok","list":{{"status":1,"uid":"test@veda.com","app_id":"app-ddos-Ezhs8iB",
     *     "type":3,"proxy_ip":{{"instance_id":"ddos-iary7q9","instance_line":"8","area":13,"line":"8","ip":"192.17.54.1"}},
     *     "protocol":"TCP","proxy_port":"81","server_port":"801","server_ip":{"127.0.0.1","127.0.0.2"},"filter":null,
     *     "last_update":1524883314,"name":"1.com","cname":"isdoz4huhj.hd.vedasec.net","id":"app-ddos-Ezhs8iB"},{"status":1,
     *     "uid":"test@veda.com","app_id":"app-ddos-kC0c5EP","type":3,"proxy_ip":{{"instance_id":"ddos-iary7q9","instance_line":"8",
     *     "area":13,"line":"8","ip":"192.17.54.1"}},"protocol":"TCP","proxy_port":"10392","server_port":"10391","server_ip":
     *     {"127.10.39.1","127.10.39.2"},"name":null,"cname":null,"filter":null,"last_update":1524883205,"id":"app-ddos-kC0c5EP"}},
     *     "total":2}
     *    ),
     *    @SWG\Property(
     *         property="errcode",
     *         type="integer",
     *         example=0
     *    ),
     *    @SWG\Property(
     *         property="errmsg",
     *         type="string",
     *         example="ok"
     *    )
     *)
     */

    /**
     * @SWG\Definition(
     *      definition="Port",
     *      type="object",
     *      @SWG\Property(property="server_ip", type="用户IP"),
     *      @SWG\Property(property="server_port", type="用户端口"),
     *      @SWG\Property(property="proxy_ip", type="高仿节点IP"),
     *      @SWG\Property(property="proxy_port", type="高仿节点端口"),
     *      @SWG\Property(property="line", type="高防节点的接入线路"),
     *
     *  )
     */

    /**
     * @SWG\Definition(
     *      definition="Port status",
     *      type="object",
     *      @SWG\Property(property="1", type="接入中"),
     *      @SWG\Property(property="2", type="已接入"),
     *      @SWG\Property(property="3", type="接入失败"),
     *      @SWG\Property(property="4", type="删除失败"),
     *
     *  )
     */
}