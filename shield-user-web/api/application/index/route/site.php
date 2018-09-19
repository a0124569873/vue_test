<?php

use think\Route;

// 获取TextCode
Route::get('site/:id/txtcode/', 'index/Site/txtCode');

// 域名接入
Route::post('site/:id/linkup', 'index/Site/linkup');
// 更新域名接入信息
Route::post('site/:id/linkup-update', 'index/Site/updateLinkUp');

// 域名审核
Route::post('site/:id/txtcode-verify', 'index/Site/txtcodeVerify');
// DNS 解析校验
Route::post('site/:id/cname-verify', 'index/Site/cNameVerify');

// 站点https证书上传
Route::post('site/:id/https-cert/upload', 'index/Site/httpsCertUpload');

// 获取站点的证书
Route::post('site/:site/https-cert', 'index/Site/GetHttpsCert');

//------------------------- 缓存加速 START -------------------------------

// 设置站点缓存有效期配置
Route::post('site/:id/cache-expire', 'index/Site/setSiteCacheExpire');

// 获取站点缓存有效期配置
Route::get('site/:id/cache-expire', 'index/Site/getSiteCacheExpire');

// 添加站点缓存白名单
Route::post('site/:id/cache-whitelist', 'index/Site/addSiteCacheWhiteList');

// 获取站点缓存白名单配置
Route::get('site/:id/cache-whitelist', 'index/Site/getSiteCacheWhiteList');

// 移除缓存白名单关键字
Route::delete('site/:id/cache-whitelist', 'index/Site/delSiteCacheWhiteList');

// 获取缓存黑名单
Route::get('site/:id/cache-blacklist', 'index/Site/getSiteCacheBlackList');

// 添加缓存黑名单
Route::post('site/:id/cache-blacklist', 'index/Site/addSiteCacheBlackList');

// 移除站点缓存黑名单关键字
Route::delete('site/:id/cache-blacklist', 'index/Site/delSiteCacheBlackList');

//------------------------- 缓存加速 END -------------------------------

//------------------------- 黑白名单配置 START -------------------------------

Route::get('site/:id/blackwhitelist', "index/sites.Bwlist/getBlackWhiteList");

Route::get('site/:id/url-blacklist', 'index/Site/getUrlBlackList');
Route::post('site/:id/url-blacklist', 'index/Site/setUrlBlackList');

Route::get('site/:id/url-whitelist', 'index/Site/getUrlWhiteList');
Route::post('site/:id/url-whitelist', "index/Site/setUrlWhiteList");

Route::get('site/:id/ip-blacklist', 'index/Site/getIpBlacklist');
Route::post('site/:id/ip-blacklist', 'index/Site/setIpBlacklist');

Route::get('site/:id/ip-whitelist', 'index/Site/getIpWhitelist');
Route::post('site/:id/ip-whitelist', 'index/Site/setIpWhitelist');

//------------------------- 黑白名单配置 END -------------------------------

//------------------------- 报表 START -------------------------------

Route::get('site/:id/report/attacks', 'index/site.Report/attacks');

//------------------------- 报表 END -------------------------------

Route::get('site/:id/proxy-ips', 'index/Site/proxyIps');

// 站点批量删除
Route::delete('site/bundle/delete', 'index/Site/bundleDelete');

// 资源路由
Route::resource('site', 'index/Site');

