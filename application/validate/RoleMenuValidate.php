<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/18
 * Time: 10:50
 */

namespace app\validate;


use think\Validate;

class RoleMenuValidate extends Validate
{
    public $field = [
        'id'            => 'ID',
        'role_id'       => '角色ID',
        'permission_id' => '权限ID',
    ];

    protected $rule = [
        'id'      => 'require|number',
        'role_id' => 'require|number',
        'menu_id' => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_save' => ['role_id'],
    ];
}