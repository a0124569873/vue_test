<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/9
 * Time: 15:54
 */

namespace app\index\model;

/**
 * 用户实例
 *
 * Class UserInstance
 * @package app\index\model
 */
class UserInstanceModel extends BaseModel
{

    protected $esIndex = 'user_instance';

    protected $esType = 'type';

    //------------------------- User instance status -------------------------------

    const STATUS_CREATED = 0;   // 未激活

    const STATUS_ACTIVATED = 1; // 已激活

    //------------------------- User instance status -------------------------------


    //------------------------- 用户实例类型 -------------------------------

        const INSTANCE_TYPE_SHARED = 1; // 共享型

        const INSTANCE_TYPE_SINGLE = 2; // 独享型

        const INSTANCE_TYPE_PORT = 3;   // 应用型

    //------------------------- 用户实例类型 -------------------------------



    //------------------------- 用户实例可选线路 -------------------------------

    // 海外线路
    const INSTANCE_LINE_OVERSEA = 0;
    // 电信线路
    const INSTANCE_LINE_DX = 1;
    // 联通线路
    const INSTANCE_LINE_LT = 2;
    // 电信/联通 双线
    const INSTANCE_LINE_DX_LT = 3;
    // 移动线路
    const INSTANCE_LINE_YD = 4;
    // 电信/移动 双线
    const INSTANCE_LINE_DX_YD = 5;
    // 移动/联通 双线
    const INSTANCE_LINE_YD_LT = 6;
    // 移动/联通/电信 三线
    const INSTANCE_LINE_YD_LT_DX = 7;
    // BGP 线路
    const INSTANCE_LINE_BGP = 8;
    // 电信/联通/BGP 三线
    const INSTANCE_LINE_DX_LT_BGP = 11;

    public static $instanceLines = [
        self::INSTANCE_LINE_OVERSEA => '海外',
        self::INSTANCE_LINE_DX => '电信',
        self::INSTANCE_LINE_LT => '联通',
        self::INSTANCE_LINE_DX_LT => '电信、联通',
        self::INSTANCE_LINE_YD => '移动',
        self::INSTANCE_LINE_DX_YD => '电信、移动',
        self::INSTANCE_LINE_YD_LT => '移动、联通',
        self::INSTANCE_LINE_YD_LT_DX => '移动、联通和电信',
        self::INSTANCE_LINE_BGP => 'BGP',
        self::INSTANCE_LINE_DX_LT_BGP => '电信、联通和BGP',
    ];

    //------------------------- 用户实例可选线路 -------------------------------

    //------------------------- 用户实例可选地域 -------------------------------

    const INSTANCE_AREA_BEIJING = 11; // 北京
    const INSTANCE_AREA_TIANJIN = 12; //天津
    const INSTANCE_AREA_HEBEI = 13; //河北

    public static $instanceAreas = [
        '11' => '北京',
        '12' => '天津',
        '13' => '河北',
        '14' => '山西',
        '15' => '内蒙古',
        '21' => '辽宁',
        '22' => '吉林',
        '23' => '黑龙江',
        '31' => '上海',
        '32' => '江苏',
        '33' => '浙江',
        '34' => '安徽',
        '35' => '福建',
        '36' => '江西',
        '37' => '山东',
        '41' => '河南',
        '42' => '湖北',
        '43' => '湖南',
        '44' => '广东',
        '45' => '广西',
        '46' => '海南',
        '50' => '重庆',
        '51' => '四川',
        '52' => '贵州',
        '53' => '云南',
        '54' => '西藏',
        '61' => '陕西',
        '62' => '甘肃',
        '63' => '青海',
        '64' => '宁夏',
        '65' => '新疆',
        '71' => '台湾',
        '81' => '香港',
        '91' => '澳门'
    ];

    //------------------------- 用户实例可选地域 -------------------------------
}