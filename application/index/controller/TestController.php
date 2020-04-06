<?php
namespace app\index\controller;

use think\Controller;

class TestController extends Controller
{
    public function index()
    {
        phpinfo();
    }

    public function hello()
    {
        //return  password_hash("123", PASSWORD_DEFAULT);
    }
}
