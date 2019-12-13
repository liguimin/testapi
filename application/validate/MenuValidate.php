<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/3
 * Time: 17:09
 */

namespace app\validate;


use think\Validate;

class MenuValidate extends Validate
{
    public $field = [
        'id'            => 'ID',
        'pid'           => '上级ID',
        'menu_name'     => '菜单名称',
        'permission_id' => '权限ID',
        'is_public'     => '是否公有',
        'sort_num'      => '排序',
        'is_node'       => '是否节点',
        'state'         => '状态',
        'resource_id'   => '资源ID',
        'create_time'   => '创建时间',
        'update_time'   => '修改时间'
    ];

    protected $rule = [
        'id'            => 'require|number',
        'pid'           => 'require|number',
        'menu_name'     => 'require',
        'permission_id' => 'require|number',
        'is_public'     => 'require|number',
        'sort_num'      => 'require|number',
        'is_node'       => 'require|number',
        'state'         => 'require|number',
        'resource_id'   => 'require|number',
    ];

    protected $message = [

    ];

    protected $scene = [
        's_save'     => ['pid', 'menu_name','is_public','sort_num','is_node','state'],
        's_updState'   => ['id', 'state'],
        's_delete'     => ['id'],
        's_edit'       => ['id'],
        's_update'     => ['id'],
        's_updPwd'     => ['id', 'pwd', 'repwd'],
        's_selfUpdPwd' => ['pwd', 'repwd']
    ];
}