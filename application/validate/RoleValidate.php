<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class RoleValidate extends Validate
{
    public $field = [
        'id'          => 'ID',
        'name'        => '角色名称',
        'state'       => '状态',
        'create_time' => '创建时间',
        'update_time' => '修改时间'
    ];

    protected $rule = [
        'id'    => 'require|number',
        'name'  => 'require',
        'state' => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_create'     => ['name', 'state'],
        's_updState'   => ['id', 'state'],
        's_delete'     => ['id'],
        's_edit'       => ['id'],
        's_update'     => ['id'],
        's_updPwd'     => ['id', 'pwd', 'repwd'],
        's_selfUpdPwd' => ['pwd', 'repwd']
    ];
}