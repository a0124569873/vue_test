<?php

use think\Route;

Route::rest([
    'create' => ['GET', '/new', 'new'],         // new
    'save'   => ['POST', '', 'create'],         // create
    'read'   => ['GET', '/:id', 'show'],        // show
    'edit'   => ['PUT', '/:id', 'update'],      // update
    'delete' => ['DELETE', '/:id', 'destroy'],  // destroy
]);

Route::resource('test', 'index/TestJob');

Route::group('v1', function () {

    // 引入Login 模块路由
    require APP_PATH . '/index/route/auth.php';
    // 引入User 模块路由
    require APP_PATH . '/index/route/user.php';
    // 引入Site模块路由
    require APP_PATH . '/index/route/site.php';
    // 引入Order模块路由
    require APP_PATH . '/index/route/order.php';
    // 引入Server模块路由
    require APP_PATH . '/index/route/server.php';

    // 引入Port模块路由
    require APP_PATH . '/index/route/port.php';

    // 引入DDoS模块路由
    require APP_PATH . '/index/route/ddos.php';

    // 引入Pay模块路由
    require APP_PATH . '/index/route/pay.php';
});


return [
    "login"=>'index/index/login',
    "register"=>'index/index/register',
    "password_find"=>'index/index/password_find',
    "password_reset/:token"=>'index/index/password_reset',
    '__miss__' => 'index/Index/index'
];

