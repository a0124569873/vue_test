<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/5/3
 * Time: 10:27
 */

namespace app\index\model;


class AttackLog extends BaseModel
{
    protected $esIndex = 'attack_log';

    protected $esType = 'type';

    //------------------------- 攻击状态 START -------------------------------

    const ATTACK_STATUS_ENDED = 0;      // 攻击已经结束
    const ATTACK_STATUS_ONGOING = 1;    // 攻击正在进行
    const ATTACK_STATUS_PAUSING = 3;    // 攻击暂停中

    //------------------------- 攻击状态 END -------------------------------
}