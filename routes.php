<?php

use Illuminate\Routing\Router;

Route::group([
    // 公会页面URL前缀
    'prefix' => 'publish-video',
    // 控制器命名空间
    'namespace' => 'Qihucms\PublishVideo\Controllers',
    'middleware' => ['web']
], function (Router $router) {
    // 发布
    $router->post('store', 'CreateController@store')->middleware('auth')
        ->name('publish-video.store');
    // 定位
    $router->get('location', 'CreateController@location')->middleware('auth')
        ->name('publish-video.location');
    // 发布页
    $router->get('create', 'CreateController@create')->middleware('auth')
        ->name('publish-video.create');
});

// 后台管理
Route::group([
    // 后台使用laravel-admin的前缀加上扩展的URL前缀
    'prefix' => config('admin.route.prefix') . '/publish-video',
    // 后台管理的命名空间
    'namespace' => 'Qihucms\PublishVideo\Controllers\Admin',
    // 后台的中间件，限制管理权限才能访问
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {
    // 配置
    $router->get('config', 'ConfigController@index')->name('admin.publish-video.config');
});