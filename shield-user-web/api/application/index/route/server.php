<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/27
 * Time: 16:07
 */

use think\Route;

// Server 实例价格计算
Route::post('server/price', 'index/Servers/serverPrice');

// Server 的基础信息
Route::get('server/config', 'index/Servers/serverConfig');

// 获取实例可接入的地域
Route::get('server/:id/areas', 'index/Servers/serverAreas');

// 获取实例可接入线路
Route::get('server/lines', 'index/Servers/serverLines');

Route::resource('server', 'index/Servers');