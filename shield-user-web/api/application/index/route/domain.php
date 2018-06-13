<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/14
 * Time: 10:18
 */

use think\Route;

// 获取TextCode
Route::get('domain/text-code/:domain', 'index/Domain/textCode');
// 域名审核
Route::post('domain/verify', 'index/Domain/verify');
// 域名接入
Route::post('domain/linkup', 'index/Domain/linkup');
// 修改配置
Route::post('domain/update-config', 'index/Domain/updateConfig');
// 资源路由
Route::resource('domain', 'index/Domain');