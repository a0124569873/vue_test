<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 16:28
 */

namespace app\index\controller\swagger;


class DDoS
{
    /**
     * @SWG\Definition(
     *    definition="DDoSList",
     *    type="object",
     *    @SWG\Property(
     *         property="list",
     *         type="object",
     *         example={"errcode":0,"errmsg":"ok","list":{{"instance_id":"ddos-kzxrdnp","uid":"test@veda.com","type":"2",
     *     "status":1,"instance_line":"8","normal_bandwidth":"10","bandwidth":"10","base_bandwidth":"10","start_date":1524445389,
     *     "end_date":1527037389,"area":11,"hd_ip":{{"line":"8","ip":"192.9.2.1","site_count":6}},"last_update":1524445391,
     *     "site_count":"50","node_id":"proxy-xm6jdzmykh","id":"ddos-kzxrdnp"}},"total":4}
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
     *    definition="DDoS字段",
     *    type="object",
     *    @SWG\Property(property="instance_id", type="string", example="ddos-kzxrdnp", description="实例ID"),
     *    @SWG\Property(property="uid", type="string", example="test@veda.com", description="用户ID"),
     *    @SWG\Property(property="type", type="string", example="1", description="实例类型，1-共享WEB实例，2-独享WEB实例，3-应用实例"),
     *    @SWG\Property(property="status", type="string", example="0", description="实例状态，0-未启用，1-已启用（启用过程分配地域与IP地址）"),
     *    @SWG\Property(property="instance_line", type="string", example="0", description="实例线路类型，0-海外，1-电信，
     * 2-联通，3-电信/联通双线，4-移动，5-电信/移动双线，6-移动/联通双线，7-移动/联通/电信三线，8-BGP"),
     *    @SWG\Property(property="normal_bandwidth", type="integer", example="20", description="业务带宽"),
     *    @SWG\Property(property="bandwidth", type="integer", example="20", description="弹性防护带宽"),
     *    @SWG\Property(property="base_bandwidth", type="integer", example="20", description="保底防护带宽"),
     *    @SWG\Property(property="site_count", type="integer", example="50", description="防护域名数"),
     *    @SWG\Property(property="port_count", type="integer", example="50", description="端口数"),
     *    @SWG\Property(property="start_date", type="integer", example="50", description="开始时间"),
     *    @SWG\Property(property="end_date", type="integer", example="50", description="结束时间"),
     *    @SWG\Property(property="area", type="integer", example="50", description="地域"),
     *    @SWG\Property(property="node_id", type="integer", example="50", description="高防节点ID"),
     *    @SWG\Property(property="hd_ip", type="object", example={{"line":"1","ip":"192.168.9.20","port_count":2},{"line":"2",
     *     "ip":"192.168.9.22","port_count":2},{"line":"8","ip":"192.168.9.55","port_count":1}}, description="高防IP列表"),
     *    @SWG\Property(property="last_update", type="integer", example="50", description="最后更新时间"),
     * )
     *
     */

    /**
     * @SWG\Definition(
     *    definition="AreaList",
     *    type="object",
     *    @SWG\Property(
     *         property="list",
     *         type="object",
     *         example={{"value":1,"label":"华北","children":{{"value":11,"label":"北京"},
     *     {"value":12,"label":"天津"},{"value":13,"label":"河北"},{"value":14,"label":"山西"},{"value":15,"label":"内蒙古"}}},
     *     {"value":2,"label":"东北","children":{{"value":21,"label":"辽宁"},{"value":22,"label":"吉林"},{"value":23,
     *     "label":"黑龙江"}}},{"value":3,"label":"华东","children":{{"value":31,"label":"上海"},{"value":32,"label":"江苏"},
     *     {"value":33,"label":"浙江"},{"value":34,"label":"安徽"},{"value":35,"label":"福建"},{"value":36,"label":"江西"},
     *     {"value":37,"label":"山东"}}},{"value":4,"label":"华中","children":{{"value":41,"label":"河南"},{"value":42,
     *     "label":"湖北"},{"value":43,"label":"湖南"},{"value":44,"label":"广东"},{"value":45,"label":"广西"},{"value":46,
     *     "label":"海南"}}},{"value":5,"label":"西南","children":{{"value":51,"label":"重庆"},{"value":52,"label":"四川"},
     *     {"value":53,"label":"贵州"},{"value":54,"label":"云南"},{"value":55,"label":"西藏"}}},{"value":6,"label":"西北",
     *     "children":{{"value":61,"label":"山西"},{"value":62,"label":"甘肃"},{"value":63,"label":"青海"},{"value":64,
     *     "label":"宁夏"},{"value":65,"label":"新疆"}}},{"value":71,"label":"台湾"},{"value":81,"label":"香港"},{"value":91,
     *     "label":"澳门"}}
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
}