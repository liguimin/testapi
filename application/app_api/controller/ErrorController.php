<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/24
 * Time: 17:24
 */

namespace app\app_api\controller;


class ErrorController extends Base
{
    protected $middleware = [];
    /**
     * 返回错误
     */
    public function index(){
        $this->jsonReturn([],urldecode(input('msg')),input('code'));
    }
}