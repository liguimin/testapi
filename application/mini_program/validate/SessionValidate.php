<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\mini_program\validate;


use think\Validate;

class SessionValidate extends Validate
{
    public $field = [
        'code'            => '登录凭证code',
    ];

    protected $rule = [
        'code'            => 'require',
    ];

    protected $message = [
    ];

    protected $scene = [
        's_login'     => ['code'],
    ];
}