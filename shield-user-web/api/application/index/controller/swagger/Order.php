<?php

namespace app\index\controller\swagger;

/**
 * @SWG\Definition()
 */
class Order
{
    /**
     * @SWG\Definition(
     *    definition="OrderList",
     *    type="object",
     *    @SWG\Property(
     *         property="list",
     *         type="object",
     *         example={"errcode":0,"errmsg":"ok","data":{{"type":"2","fee":"10000","detail":{"instance_line":"0",
     *     "base_bandwidth":"10","bandwidth":"10","ord_time":"2","sp_num":"1","normal_bandwidth":"10","product_id":"1",
     *     "site_count":"50","start_date":1524444929,"end_date":1529715329,"instance_id":{}},"create_time":1524444929,
     *     "status":0,"uid":"test@veda.com","oid":"11804230855160","pay_time":null},{"type":"2","fee":"15000",
     *     "detail":{"instance_line":"8","base_bandwidth":"10","bandwidth":"10","ord_time":"3","sp_num":"2","normal_bandwidth":"10",
     *     "product_id":"3","port_count":"50","start_date":1524034570,"end_date":1531896970,"instance_id":{"ddos-g7stzro",
     *     "ddos-er5xsjs"}},"create_time":1524034570,"status":1,"uid":"test@veda.com","last_update":"2018-04-18T06:56:11.000Z",
     *     "pay_time":1524625176,"oid":"11804181456660"}}}
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
     *      definition="Site Type",
     *      type="object",
     *      @SWG\Property(property=" 0 ", type="未设置"),
     *      @SWG\Property(property="1", type="独享型网站类"),
     *      @SWG\Property(property="2", type="共享型网站类"),
     *      @SWG\Property(property="3", type="非网站类"),
     *
     *  )
     */

    /**
     * @SWG\Definition(
     *      definition="Area Type",
     *      type="object",
     *      @SWG\Property(property="11", type="北京"),
     *      @SWG\Property(property="12", type="天津"),
     *      @SWG\Property(property="13", type="河北"),
     *      @SWG\Property(property="14", type="山西"),
     *      @SWG\Property(property="15", type="内蒙古"),
     *      @SWG\Property(property="21", type="辽宁"),
     *      @SWG\Property(property="22", type="吉林"),
     *      @SWG\Property(property="23", type="黑龙江"),
     *      @SWG\Property(property="31", type="上海"),
     *      @SWG\Property(property="32", type="江苏"),
     *      @SWG\Property(property="33", type="浙江"),
     *      @SWG\Property(property="34", type="安徽"),
     *      @SWG\Property(property="35", type="福建"),
     *      @SWG\Property(property="36", type="江西"),
     *      @SWG\Property(property="37", type="山东"),
     *      @SWG\Property(property="41", type="河南"),
     *      @SWG\Property(property="42", type="湖北"),
     *      @SWG\Property(property="43", type="湖南"),
     *      @SWG\Property(property="44", type="广东"),
     *      @SWG\Property(property="45", type="广西"),
     *      @SWG\Property(property="46", type="海南"),
     *      @SWG\Property(property="50", type="重庆"),
     *      @SWG\Property(property="51", type="四川"),
     *      @SWG\Property(property="52", type="贵州"),
     *      @SWG\Property(property="53", type="云南"),
     *      @SWG\Property(property="54", type="西藏"),
     *      @SWG\Property(property="61", type="陕西"),
     *      @SWG\Property(property="62", type="甘肃"),
     *      @SWG\Property(property="63", type="青海"),
     *      @SWG\Property(property="64", type="宁夏"),
     *      @SWG\Property(property="65", type="新疆"),
     *      @SWG\Property(property="71", type="台湾"),
     *      @SWG\Property(property="81", type="香港"),
     *      @SWG\Property(property="91", type="澳门")
     *  )
     */

    /**
     * @SWG\Definition(
     *    definition="Orderdetail",
     *    type="object",
     *    @SWG\Property(property="type", type="integer", example=2, description="类型（1/2，充值/消费）。充值最低10元，必须为整数"),
     *    @SWG\Property(property="fee", type="float", example=2, description="金额，保留两位小数"),
     *    @SWG\Property(
     *        property="detail",
     *        type="object",
     *        @SWG\Property(property="product_id", type="integer", example=1, description="产品ID，0-充值，1-共享WEB实例，2-独享WEB实例，3-应用实例"),
     *        @SWG\Property(property="instance_line", type="string", example="8", description="实例线路类型，0-海外，1-电信，2-联通，
     * 3-电信/联通双线，4-移动，5-电信/移动双线，6-移动/联通双线，7-移动/联通/电信三线，8-BGP"),
     *        @SWG\Property(property="base_bandwidth", type="integer", example=20, description="保底带宽"),
     *        @SWG\Property(property="normal_bandwidth", type="integer", example=20, description="业务带宽"),
     *        @SWG\Property(property="bandwidth", type="integer", example=20, description="弹性带宽"),
     *        @SWG\Property(property="ord_time", type="string", example="1", description="时长(月)"),
     *        @SWG\Property(property="sp_num", type="string", example="1", description="购买数量"),
     *        @SWG\Property(property="site_count", type="integer", example=50, description="[网站类使用此参数]防护域名数"),
     *        @SWG\Property(property="port_count", type="integer", example=50, description="[应用型使用此参数]端口数"),
     *        @SWG\Property(property="start_date", type="string", example=1522048837000, description="开始时间"),
     *        @SWG\Property(property="end_date", type="string", example=1553616000000, description="截止时间"),
     *        @SWG\Property(property="pay_time", type="string", example=1553616000000, description="支付时间"),
     *        @SWG\Property(property="instance_id", type="object", example={"ddos-as123asdu3"}, description="实例ID数组"),
     *    ),
     * )
     *
     */

    /**
     * @SWG\Definition(
     *      definition="Order Status",
     *      type="object",
     *      @SWG\Property(property=" 0 ", type="已创建，待支付"),
     *      @SWG\Property(property="1", type="已支付"),
     *      @SWG\Property(property="2", type="已删除"),
     *
     *  )
     */
}