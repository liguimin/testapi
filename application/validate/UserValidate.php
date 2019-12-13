<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class UserValidate extends Validate
{
    public $field = [
        'id'       => 'ID',
        'username' => '用户名',
        'pwd'      => '密码',
        'repwd'    => '密码确认',
        'name'     => '姓名',
        'mobile'   => '手机号',
        'remarks'  => '备注',
        'birthday' => '生日',
        'avatar'   => '头像',
        'state'    => '状态'
    ];

    protected $rule = [
        'id'       => 'require|number',
        'username' => 'require',
        'pwd'      => 'require',
        'repwd'    => 'require|confirm:pwd',
        'state'    => 'require|number',
        'avatar'   => 'require'
    ];

    protected $message = [

    ];

    protected $scene = [
        's_create'     => ['username', 'pwd', 'repwd', 'state'],
        's_updState'   => ['id', 'state'],
        's_delete'     => ['id'],
        's_edit'       => ['id'],
        's_update'     => ['id'],
        's_updPwd'     => ['id', 'pwd', 'repwd'],
        's_selfUpdPwd' => ['pwd', 'repwd']
    ];
}