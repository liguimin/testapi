<?php
namespace app\index\controller;

use think\Controller;

class IndexController extends Controller
{
    public function index()
    {
        return 'test';
    }

    public function hello()
    {
        $arr1=[
            "a"=>"red","b"=>"blue","c"=>"tete","d"=>"green"
        ];
        $arr2=[
            "e"=>"red","f"=>"green","g"=>"blue",'h'=>'232424'
        ];
         var_dump(array_diff($arr1,$arr2));
        //return  password_hash("123", PASSWORD_DEFAULT);
    }
}
