<?php

use think\Route;

// 所有路由绑定到index控制器
Route::bind('index');

return [
    //控制器路由别名
    '__alias__' =>  [
        'setting'   =>  'index/Defense',
        'license'   =>  'index/License',
        'logs'      =>  'index/Logs',
        'stats'     =>  'index/Stats',
        'system'    =>  'index/Sysinfo',
        'user'      =>  'index/User',
        'protect'   =>  'index/Protect',
        'sysconf'   =>  'index/Sysconf'
    ],
    '__miss__' => 'index/Index/index'
];
