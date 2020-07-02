<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class ExUserValidate extends Validate
{
    public $field = [
        'id'      => 'ID',
        'mobile'  => '手机号',
        'captcha' => '验证码',
    ];

    protected $rule = [
        'id'      => 'require|number',
        'mobile'  => 'require',
        'captcha' => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_save' => ['mobile','captcha'],
    ];
}