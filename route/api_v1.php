<?php

Route::group('v1', function () {

    //错误信息
    Route::rule('error','error/index');

    //登录
    Route::post('session', 'session/login');
    //退出登录
    Route::delete('session','session/logout');

    //用户信息
    Route::resource('user','user');
    //修改用户状态
    Route::put('user/:id/state', 'user/updState');
    //修改用户密码
    Route::put('user/:id/pwd', 'user/updPwd');
    //上传头像
    Route::post('user/avatar','user/uploadAvatar');
    //获取用户信息
    Route::get('user/userinfo','user/getUserinfo');
    //修改当前登录用户的密码
    Route::put('user/pwd','user/selfUpdPwd');


    //角色信息
    Route::resource('role','role');
    //修改角色状态
    Route::put('role/:id/state','role/setState');


    //权限路由
    Route::resource('permission','permission');
    //获取角色权限
    Route::get('role/:id/permission','role/getRolePermission');
    //修改角色权限
    Route::put('role/:id/permission','role/saveRolePermission');

    //获取权限节点树
    Route::get('permission/treeNode','permission/getTreeNode');
    //获取当前用户的权限
    Route::get('permission/userPermission','permission/getUserPermission');

    //资源路由
    Route::resource('resource','resource');

    //修改资源状态
    Route::put('resource/:id/state','resource/updState');

    //菜单路由
    Route::resource('menu','menu');
    //获取角色菜单
    Route::get('role/:id/menu','role/getRoleMenu');
    //修改角色菜单
    Route::put('role/:id/menu','role/saveRoleMenu');
    //获取用户菜单
    Route::get('menu/userMenu','menu/getUserMenu');

    //考试题目路由
    Route::resource('examquestions','examquestions');
    //修改考试题目状态
    Route::put('examquestions/:id/state','examquestions/updState');
    //上传单选题文件
    Route::post('examquestions/uploadSingle','examquestions/uploadSingle');
    //上传判断题文件
    Route::post('examquestions/uploadJudge','examquestions/uploadJudge');
    //上传多选题题文件
    Route::post('examquestions/uploadMulti','examquestions/uploadMulti');

})
    ->prefix('api/')
    ->pattern(['id' => '\d+'])
   /* ->header('Access-Control-Allow-Origin','http://47.107.50.5')*/
    ->header('Access-Control-Allow-Origin','*')
    ->header('Access-Control-Allow-Credentials', 'true')
    ->allowCrossDomain();;

return [];