<?php

Route::group('mini_v1', function () {
    //登录
    Route::post('session/wx', 'session/wxlogin');
    //退出登录
    Route::delete('session','session/logout');
})
    ->prefix('mini_program/')
    ->pattern(['id' => '\d+'])
    ->allowCrossDomain();;

return [];