<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/18
 * Time: 10:50
 */

namespace app\validate;


use think\Validate;

class PermissionValidate extends Validate
{
    public $field = [
        'id'      => 'ID',
        'pid'     => '上级ID',
        'name'    => '权限名称',
        'state'   => '状态',
        'is_node' => '是否节点',
    ];

    protected $rule = [
        'id'      => 'require|number',
        'pid'     => 'require|number',
        'name'    => 'require',
        'state'   => 'require|number',
        'is_node' => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_save'   => ['pid', 'name', 'state', 'is_node'],
        's_edit'   => ['id'],
        's_update' => ['id', 'pid', 'name', 'state', 'is_node'],
        's_del'    => ['id']
    ];
}