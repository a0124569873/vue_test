<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/11
 * Time: 11:01
 */

namespace app\index\controller\swagger;

/**
 * @SWG\Definition()
 */
class UserInstance
{

    /**
     * @SWG\Definition(
     *      definition="Instance Type",
     *      type="object",
     *      @SWG\Property(property=" 0 ", type="未设置"),
     *      @SWG\Property(property="1", type="共享型网站类"),
     *      @SWG\Property(property="2", type="独享型网站类"),
     *      @SWG\Property(property="3", type="应用型"),
     *
     *  )
     */


    /**
     * @SWG\Definition(
     *      definition="Line Type",
     *      type="object",
     *      @SWG\Property(property=" 0 ", type="海外"),
     *      @SWG\Property(property="1", type="电信"),
     *      @SWG\Property(property="2", type="联通"),
     *      @SWG\Property(property="3", type="电信、联通"),
     *      @SWG\Property(property="4", type="移动"),
     *      @SWG\Property(property="5", type="电信、移动"),
     *      @SWG\Property(property="6", type="联通、移动"),
     *      @SWG\Property(property="7", type="电信、联通、移动"),
     *      @SWG\Property(property="8", type="BGP"),
     *      @SWG\Property(property="11", type="电信、联通、BGP"),
     *
     *  )
     */
}