<?php

use think\Route;

Route::resource('users','index/user.Users',['except'=>['read']]);

Route::get([
    'users/profile'        =>  'index/user.Users/profile',
    'manage'               =>  'index/Manage/get',
]);
Route::post([
    'password/send'        =>  'index/user.Password/send',
    'safemail/send'        =>  'index/user.Safemail/send',
    'safephone/send'       =>  'index/user.Safephone/send',
]);
Route::put([
    'password'        =>  'index/user.Password/update',
    'safemail'        =>  'index/user.Safemail/update',
    'safephone'       =>  'index/user.Safephone/update',
    'realname'        =>  'index/user.Realname/update',
]);
