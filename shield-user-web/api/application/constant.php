<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/4/27
 * Time: 11:04
 */

//------------------------- RESPONSE CODE START -------------------------------

defined('REP_CODE_SUCCESS') or define('REP_CODE_SUCCESS', 0);   // 正常
defined('REP_CODE_PARAMS_INVALID') or define('REP_CODE_PARAMS_INVALID', 11000); // 请求参数不合法
defined('REP_CODE_NEED_LOGIN') or define('REP_CODE_NEED_LOGIN', 12001);   // 需要登陆
defined('REP_CODE_SOURCE_NOT_FOUND') or define('REP_CODE_SOURCE_NOT_FOUND', 13001);   // 请求数据不存在
defined('REP_CODE_ABNORMAL_OPERATION') or define('REP_CODE_ABNORMAL_OPERATION', 14000);   // 操作异常
defined('REP_CODE_ILLEGAL_OPERATION') or define('REP_CODE_ILLEGAL_OPERATION', 14001);   // 非法操作
defined('REP_CODE_FAILED_OPERATION') or define('REP_CODE_FAILED_OPERATION', 14200);     // 操作失败
defined('REP_CODE_PERMISSION_DENY') or define('REP_CODE_PERMISSION_DENY', 14003);     // 权限异常

defined('REP_CODE_DB_ERROR') or define('REP_CODE_DB_ERROR', 20000);   // 数据库操作失败
defined('REP_CODE_ES_ERROR') or define('REP_CODE_ES_ERROR', 21000);   // ES操作失败
defined('REP_CODE_ZK_ERROR') or define('REP_CODE_ZK_ERROR', 22000);   // ZK操作失败

//------------------------- RESPONSE CODE END -------------------------------