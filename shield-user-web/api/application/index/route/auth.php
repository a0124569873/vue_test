<?php

use think\Route;

Route::get([
    'loginfo'       =>  'index/Sessions/index',
]);
Route::post([
    'login'         =>  'index/Sessions/create',
]);
Route::delete([
    'logout'        =>  'index/Sessions/destroy'
]);
