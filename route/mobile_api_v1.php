<?php

Route::group('m_v1', function () {
    //考试题目路由
    Route::resource('examquestions','examquestions');
    //登录
    Route::post('examquestions/all', 'examquestions/index');
})
    ->prefix('mobile_api/')
    ->pattern(['id' => '\d+'])
    ->header('Access-Control-Allow-Origin','http://47.107.50.5:8082')
    ->header('Access-Control-Allow-Credentials', 'true')
    ->allowCrossDomain();;

return [];