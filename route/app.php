<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

//登录
Route::get('admin/login', 'Login/index');
//登录操作
Route::post('admin/login', 'Login/index');

//统一使用路由中间件
Route::group(function(){

    //管理员主页
    Route::get('admin/index', 'Index/index');
    //管理员退出
    Route::get('admin/loginout', 'Index/loginout');

    //管理员管理
    Route::get('admin/list', 'Admin/index');
    Route::rule('admin/add', 'Admin/add','get|post');
    Route::rule('admin/edit/:id', 'Admin/edit',"get|post");
    Route::get('admin/del/:id', 'Admin/del');
    Route::post('admin/status/:id', 'Admin/status');

    //角色管理
    Route::get('admin/role/index', 'Role/index');
    Route::rule('admin/role/add', 'Role/add','get|post');
    Route::rule('admin/role/edit/:id', 'Role/edit',"get|post");
    Route::get('admin/role/del/:id', 'Role/del');
    //权限管理
    Route::get('admin/auth/index', 'Auth/index');
    Route::rule('admin/auth/add', 'Auth/add',"get|post");
    Route::rule('admin/auth/edit/:id', 'Auth/edit',"get|post");
    Route::get('admin/auth/del/:id', 'Auth/del');

})->middleware(\app\middleware\AdminCheck::class);


Route::miss(function(){
    return "无匹配的访问路由";
});