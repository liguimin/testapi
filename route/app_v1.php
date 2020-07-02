<?php

Route::group('app_v1', function () {

    //错误信息
    Route::rule('error','error/index');

    //发送验证码
    Route::post('sms','sms/send');

    //登录
    Route::post('session', 'session/login');
    //退出登录
    Route::delete('session','session/logout');

})
    ->prefix('app_api/')
    ->pattern(['id' => '\d+'])
   /* ->header('Access-Control-Allow-Origin','http://47.107.50.5')*/
    ->header('Access-Control-Allow-Origin','*')
    ->header('Access-Control-Allow-Credentials', 'true')
    ->allowCrossDomain();;

return [];