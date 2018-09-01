<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/28
 * Time: 17:59
 */

use think\Route;

// 生成CName自动调度的CNAME值
Route::post('port/:id/cname-generate', 'index/Port/cnameGenerate');
// 启用CNAME自动调度
Route::post('port/:id/cname-active', 'index/Port/cnameActive');

// 修改应用接入信息
Route::post('port/:id/linkup-update', 'index/Port/updateLinkUp');
// 修改应用信息
Route::post('port/:id/conf-update', 'index/Port/updateConf');

// 获取应用黑白名单
Route::get('port/:id/ip-white-black-list', 'index/Port/getIpWhiteBlackList');

// 设置应用黑白名单
Route::post('port/:id/ip-black-list', 'index/Port/setIpBlacklist');
Route::post('port/:id/ip-white-list', 'index/Port/setIpWhitelist');

// 站点批量删除
Route::delete('port/bundle/delete', 'index/Port/bundleDelete');

//------------------------- 报表 START -------------------------------

Route::get('port/:id/report/attacks', 'index/port.Report/attacks');

//------------------------- 报表 END -------------------------------

// 资源路由
Route::resource('port', 'index/Port');