<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 17:41
 */

namespace app\index\controller\swagger;


class Server
{
    /**
     * @SWG\Definition(
     *    definition="ServerList",
     *    type="object",
     *    @SWG\Property(
     *         property="list",
     *         type="object",
     *         example={{"type":3,"hd_ip":"122.2.22.1","line":"11_1"}}
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
     *      definition="Server Field",
     *      type="object",
     *      @SWG\Property(property="type", type="实例类型"),
     *      @SWG\Property(property="hd_ip", type="高防IP"),
     *      @SWG\Property(property="line", type="高防线路"),
     *  )
     */

    /**
     * @SWG\Definition(
     *      definition="Server Type",
     *      type="object",
     *      @SWG\Property(property="1", type="网站防护独享型"),
     *      @SWG\Property(property="2", type="网站防护共享型"),
     *      @SWG\Property(property="3", type="非网站防护性型"),
     *  )
     */
}