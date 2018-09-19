<?php
/**
 * Created by PhpStorm.
 * User: WangSF
 * Date: 2018/3/29
 * Time: 15:54
 */

use think\Route;

Route::post('ddos/:id/active', 'index/DDoS/active');

Route::get('ddos/ips', 'index/DDoS/ips');

Route::get('ddos/areas', 'index/DDoS/areas');

Route::get('ddos/:id/available-areas', 'index/DDoS/availableAreas');

//------------------------- 报表 START -------------------------------

Route::get('ddos/:id/report/attacks', 'index/ddos.Report/attacks');

//------------------------- 报表 END -------------------------------

Route::resource('ddos', 'index/DDoS');