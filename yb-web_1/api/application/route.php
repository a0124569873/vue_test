<?php

use think\Route;

//所有路由绑定到index控制器
Route::bind('index');

//前端页面路由
Route::rule([
    'login'         =>  'index/Index/index',
    "errorpage"     =>  'index/Index/index',
    "user_info"       =>  'index/Index/index',
    "vpn_maped"       =>  'index/Index/index',
    "netaddr"       =>  'index/Index/index',
    "connect_back"      =>  'index/Index/index',
    "camouflage"      =>  'index/Index/index',
    "sys_status"     =>  'index/Index/index',
    "linkinfo"      =>  'index/Index/index',
    "account_manage"      =>  'index/Index/index',
    "vpnserver"      =>  'index/Index/index',
    "g_status"      =>  'index/Index/index',
    "grule"      =>  'index/Index/index',
    "control"      =>  'index/Index/index',
    
]);

return [
    //路由别名
    '__alias__' =>  [
        'admin'     =>  'index/Admin',
        'uconnect'  =>  'index/Uconnect',
        'vpnmap'    =>  'index/Vpnmap',
        'stats'     =>  'index/Stats',
        'maskpools' =>  'index/Maskpool',
        'system'    =>  'index/System',
        'relink'    =>  'index/Relink',
        'settings'    =>  'index/Setting',
        
    ],
];